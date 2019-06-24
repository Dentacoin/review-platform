<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use Auth;
use Image;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\Vox;
use App\Models\DcnReward;
use App\Models\DcnCashout;
use App\Models\Dcn;
use App\Models\Country;
use App\Models\Civic;
use Carbon\Carbon;



class ProfileController extends FrontController
{

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->menu = [
        	'home' => trans('vox.page.profile.menu.wallet'),
			'info' => trans('vox.page.profile.menu.info'),
            'privacy' => trans('vox.page.profile.menu.privacy'),
            'invite' => trans('vox.page.profile.menu.invite-patient'),
            'vox' => trans('vox.page.profile.menu.vox'),
		];

		$this->profile_fields = [
            'name' => [
                'type' => 'text',
                'required' => true,
                'min' => 3,
                'hint' => true,
            ],
            'name_alternative' => [
                'type' => 'text',
                'required' => false,
                'hint' => true,
            ],
            'email' => [
                'type' => 'text',
                'required' => true,
                'is_email' => true,
            ],
            'country_id' => [
                'type' => 'country',
                'required' => true,
            ],
            'address' => [
                'type' => 'text',
                'required' => true,
            ],
            'website' => [
                'type' => 'text',
                'required' => true,
                'hint' => true,
                'regex' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
            ],
            'birthyear' => [
                'type' => 'select',
                'required' => true,
                'values' => array_combine( range( date('Y'), date('Y')-90 ), range( date('Y'), date('Y')-90 ) )
            ],
            'gender' => [
                'type' => 'select',
                'required' => true,
                'values' => [
                    'm' => trans('vox.common.gender.m'),
                    'f' => trans('vox.common.gender.f'),
                ]
            ],
    	];

        $this->genders = [
            '' => null,
            'm' => trans('admin.common.gender.m'),
            'f' => trans('admin.common.gender.f'),
        ];
    }

    public function handleMenu() {
        if($this->user->is_dentist) {
            $this->menu['invite'] = trans('vox.page.profile.menu.invite-dentist');
        } else {
            unset($this->profile_fields['address']);            
            unset($this->profile_fields['website']);            
            unset($this->profile_fields['name_alternative']);            
        }
    }


    public function setGrace($locale=null) {
        if(empty($this->user->grace_end)) {
            $this->user->grace_end = Carbon::now();
            $this->user->save();
        }
        session(['new_auth' => null]);
    }

    //
    //Home
    //

    public function home($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('welcome-to-dentavox'));
        }
        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/vox'));
        }
        $this->handleMenu();

        if(Request::isMethod('post')) {
            $va = trim(Request::input('vox-address'));
            if(empty($va) || mb_strlen($va)!=42) {
                Request::session()->flash('error-message', trans('vox.page.profile.home.address-invalid'));
            } else if(!$this->user->canIuseAddress($va)) {
                Request::session()->flash('error-message', trans('vox.page.profile.home.address-used'));
            } else {
                $this->user->dcn_address = Request::input('vox-address');
                $this->user->save();
                Request::session()->flash('success-message', trans('vox.page.profile.home.address-saved'));
            }
            
            return redirect( getLangUrl('profile'));
        }

        return $this->ShowVoxView('profile', [
            'menu' => $this->menu,
            'currencies' => file_get_contents('/tmp/dcn_currncies'),
            'history' => $this->user->history->where('type', '=', 'vox-cashout'),
            'js' => [
                'wallet.js',
                'profile.js',
            ],
            'jscdn' => [
                'https://hosted-sip.civic.com/js/civic.sip.min.js',
            ],
            'csscdn' => [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ],
        ]);
    }


    public function withdraw($locale=null) {
        if($this->user->isBanned('vox') || !$this->user->canWithdraw('vox') ) {
            return;
        }

        $va = trim(Request::input('vox-address'));
        if(empty($va) || mb_strlen($va)!=42) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.home.address-invalid')
            ];
            return Response::json( $ret );
        } else if(!$this->user->canIuseAddress($va)) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.home.address-used')
            ];
            return Response::json( $ret );
        } else {
            $this->user->dcn_address = Request::input('vox-address');
            $this->user->save();
        }
        

        $amount = intval(Request::input('wallet-amount'));
        if($amount > $this->user->getTotalBalance('vox')) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.home.amount-too-high')
            ];
        } else if($amount<env('VOX_MIN_WITHDRAW')) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.home.amount-too-low', [
                    'minimum' => '<b>'.env('VOX_MIN_WITHDRAW').'</b>'
                ])
            ];
        } else {

            $cashout = new DcnCashout;
            $cashout->user_id = $this->user->id;
            $cashout->reward = $amount;
            $cashout->platform = 'vox';
            $cashout->address = $this->user->dcn_address;
            $cashout->save();


            $ret = Dcn::send($this->user, $this->user->dcn_address, $amount, 'vox-cashout', $cashout->id);
            $ret['balance'] = $this->user->getTotalBalance('vox');
            
            if($ret['success']) {
                $cashout->tx_hash = $ret['message'];
                $cashout->save();
            } else if (!empty($ret['valid_input'])) {
                $ret['success'] = true;
            }

            if( empty($ret['success']) ) {
                $cashout->delete();
            }
        }

        return Response::json( $ret );
    }


    //
    //Vox
    //

    public function vox($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('welcome-to-dentavox'));
        }


        $path = explode('/', request()->path())[2];
        $markLogin = true;
        $markLogout = true;

        if ($path == 'vox-iframe') {
            $markLogin = false;
            $markLogout = false;
        }

        $this->handleMenu();
        $current_ban = $this->user->isBanned('vox');
        $prev_bans = null; 
        $time_left = '';

        $ban_reason = '';
        $ban_alternatives = '';
        $ban_alternatives_buttons = '';
        if( $current_ban ) {
            $prev_bans = $this->user->getPrevBansCount('vox', $current_ban->type);
            if($current_ban->type=='mistakes') {
                $ban_reason = trans('vox.page.bans.banned-mistakes-title-'.$prev_bans);
            } else {
                $ban_reason = trans('vox.page.bans.banned-too-fast-title-'.$prev_bans);
            }

            if($prev_bans==1) {
                $ban_alternatives = trans('vox.page.bans.banned-alternative-1');
                $ban_alternatives_buttons = '
                <a href="https://dentacare.dentacoin.com/" target="_blank">
                    <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                </a>';
            } else if($prev_bans==2) {
                $ban_alternatives = trans('vox.page.bans.banned-alternative-2');
                $ban_alternatives_buttons = '
                <a href="https://reviews.dentacoin.com/" target="_blank">
                    <img src="'.url('new-vox-img/bans-trp.png').'" />
                </a>';
            } else if($prev_bans==3) {
                $ban_alternatives = trans('vox.page.bans.banned-alternative-3');
                $ban_alternatives_buttons = '
                <a href="https://dentacare.dentacoin.com/" target="_blank">
                    <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                </a>';
            } else {
                $ban_alternatives = trans('vox.page.bans.banned-alternative-4');
                $ban_alternatives_buttons = '
                <a href="https://dentacare.dentacoin.com/" target="_blank">
                    <img src="'.url('new-vox-img/bans-dentacare.png').'" />
                </a>
                <a href="https://reviews.dentacoin.com/" target="_blank">
                    <img src="'.url('new-vox-img/bans-trp.png').'" />
                </a>';
            }

            if( $current_ban->expires ) {
                $now = Carbon::now();
                $time_left = $current_ban->expires->diffInHours($now).':'.
                str_pad($current_ban->expires->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
                str_pad($current_ban->expires->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);
            } else {
                $time_left = null;
            }
        }

        $more_surveys = false;
        $rewards = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('reference_id', '!=', 34)->get();
        if ($rewards->count() == 1 && $rewards->first()->vox_id == 11) {
            $more_surveys = true;
        }

        return $this->ShowVoxView('profile-vox', [
            'markLogout' => $markLogout,
            'markLogin' => $markLogin,
            'latest_voxes' => Vox::where('type', 'normal')->orderBy('created_at', 'desc')->take(3)->get(),
            'more_surveys' => $more_surveys,
            'menu' => $this->menu,
            'prev_bans' => $prev_bans,
            'current_ban' => $current_ban,
            'ban_reason' => $ban_reason,
            'ban_alternatives' => $ban_alternatives,
            'ban_alternatives_buttons' => $ban_alternatives_buttons,
            'time_left' => $time_left,
            'histories' => $this->user->vox_rewards->where('reference_id', '!=', 34),
            'payouts' => $this->user->history->where('type', '=', 'vox-cashout'),
            'js' => [
                'profile.js',
            ]
        ]);
    }

    //
    //Privacy
    //


    public function privacy($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('welcome-to-dentavox'));
        }
        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/vox'));
        }
        $this->handleMenu();
        


        if(Request::isMethod('post')) {
            if( Request::input('action') ) {
                if( Request::input('action')=='delete' ) {
                    $this->user->sendTemplate( 30 );
                    $this->user->self_deleted = 1;
                    $this->user->save();
                    $this->user->deleteActions();
                    User::destroy( $this->user->id );
                    Auth::guard('web')->logout();
                    return redirect( getLangUrl('/') );
                }
            }
        }

        return $this->ShowVoxView('profile-privacy', [
            'menu' => $this->menu,
            'js' => [
                'profile.js',
            ],
        ]);
    }

    public function privacy_download($locale=null) {

        $html = $this->showView('users-data', array(
            'genders' => $this->genders,
        ))->render();

        $tmp_path = '/tmp/'.$this->user->id;
        if(!is_dir($tmp_path)) {
            mkdir($tmp_path);
        }

        file_put_contents($tmp_path.'/my-private-info.html', $html);

        if($this->user->hasimage) {
            copy( $this->user->getImagePath(), $tmp_path.'/'.$this->user->id.'.jpg' );
        }

        if($this->user->photos->isNotEmpty()) {
            foreach ($this->user->photos as $photo) {
                copy( $photo->getImagePath(), $tmp_path.'/'.$photo->id.'.jpg' );
            }
        }

        exec('zip -rj0 '.$tmp_path.'.zip '.$tmp_path.'/*');
        exec('rm -rf '.$tmp_path);

        $mtext = $this->user->getName().' just requested his/hers personal information<br/>
User\'s email is: '.$this->user->email.'<br/>
Link to user\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$this->user->id;

        Mail::send([], [], function ($message) use ($mtext) {
            $sender = config('mail.from.address');
            $sender_name = config('mail.from.name');

            $message->from($sender, $sender_name);
            $message->to( 'privacy@dentacoin.com' ); //$sender
            //$message->to( 'dokinator@gmail.com' );
            $message->replyTo($sender, $sender_name);
            $message->subject('New Personal Data Download Request');
            $message->attach('/tmp/'.$this->user->id.'.zip');
            $message->setBody($mtext, 'text/html'); // for HTML rich messages
        });

        return Response::download($tmp_path.'.zip', 'your-private-info.zip');
    }

    //
    //Invites
    //



    public function invite($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('welcome-to-dentavox'));
        }
        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/vox'));
        }
        $this->handleMenu();
        

        if(Request::isMethod('post') && $this->user->canInvite('vox') ) {

            if(Request::Input('is_contacts')) {
                if(empty(Request::Input('contacts')) || !is_array( Request::Input('contacts') ) ) {
                    return Response::json(['success' => false, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.contacts-none-selected') ] );
                }

                foreach (Request::Input('contacts') as $inv_info) {
                    $inv_arr = explode('|', $inv_info);
                    $email = $name = '';
                    if(count($inv_arr)>1) {
                        $email = $inv_arr[ count($inv_arr)-1 ];
                        $name = $inv_arr[0];
                    } else {
                        $email = $inv_arr[0];
                    }
                    $invitation = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', $email],
                    ])->first();

                    if($invitation) {
                        if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                            return Response::json(['success' => false, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.already-invited') ] );
                        }
                        $invitation->invited_name = $name;
                        $invitation->created_at = Carbon::now();
                        $invitation->save();
                    } else {
                        $invitation = new UserInvite;
                        $invitation->user_id = $this->user->id;
                        $invitation->invited_email = $email;
                        $invitation->invited_name = $name;
                        $invitation->save();
                    }
                    //Mega hack
                    $dentist_name = $this->user->name;
                    $dentist_email = $this->user->email;
                    $this->user->name = '';
                    $this->user->email = $email;
                    $this->user->save();

                    $this->user->sendTemplate( $this->user->is_dentist ? 27 : 25, [
                        'friend_name' => $dentist_name,
                        'invitation_id' => $invitation->id
                    ]);

                    //Back to original
                    $this->user->name = $dentist_name;
                    $this->user->email = $dentist_email;
                    $this->user->save();

                }

                return Response::json(['success' => true, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.contacts-success') ] );
            } else {
                $validator = Validator::make(Request::all(), [
                    'email' => ['required', 'email'],
                    'name' => ['required', 'string'],
                ]);

                if ($validator->fails()) {
                    return Response::json(['success' => false, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.failure') ] );
                } else {
                    $invitation = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', Request::Input('email')],
                    ])->first();

                    if($invitation) {
                        if($invitation->created_at->timestamp > Carbon::now()->subMonths(1)->timestamp) {
                            return Response::json(['success' => false, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.already-invited') ] );
                        }
                        $invitation->invited_name = Request::Input('name');
                        $invitation->created_at = Carbon::now();
                        $invitation->save();
                    } else {
                        $invitation = new UserInvite;
                        $invitation->user_id = $this->user->id;
                        $invitation->invited_email = Request::Input('email');
                        $invitation->invited_name = Request::Input('name');
                        $invitation->save();
                    }

                    //Mega hack
                    $dentist_name = $this->user->name;
                    $dentist_email = $this->user->email;
                    $this->user->name = Request::Input('name');
                    $this->user->email = Request::Input('email');
                    $this->user->save();

                    // $this->user->sendTemplate( $this->user->is_dentist ? 27 : 25 , [
                    //     'friend_name' => $dentist_name,
                    //     'invitation_id' => $invitation->id
                    // ]);

                    $substitutions = [
                        'type' => $this->user->is_clinic ? 'dental clinic' : ($this->user->is_dentist ? 'your dentist' : ''),
                        'inviting_user_name' => ($this->user->is_dentist && !$this->user->is_clinic && $this->user->title) ? config('titles')[$this->user->title].' '.$dentist_name : $dentist_name,
                        'invited_user_name' => $this->user->name,
                        "invitation_link" => getVoxUrl('invite/'.$this->user->id.'/'.$this->user->get_invite_token().'/'.$invitation->id),
                    ];

                    $this->user->sendGridTemplate(58, $substitutions);

                    //Back to original
                    $this->user->name = $dentist_name;
                    $this->user->email = $dentist_email;
                    $this->user->save();

                    return Response::json(['success' => true, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.success') ] );
                }
            }

        }

        return $this->ShowVoxView('profile-invite', [
            'menu' => $this->menu,
            'js' => [
                'profile.js',
                'hello.all.js',
            ],
        ]);
    }


    
    //
    //Info
    //

    public function upload($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return Response::json(['success' => false ]);
        }
        if($this->user->isBanned('vox')) {
            return Response::json(['success' => false ]);
        }
        $this->handleMenu();

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $this->user->addImage($img);

            $this->user->hasimage_social = false;
            $this->user->save();

            foreach ($this->user->reviews_out as $review_out) {
                $review_out->hasimage_social = false;
                $review_out->save();
            }

            foreach ($this->user->reviews_in_dentist as $review_in_dentist) {
                $review_in_dentist->hasimage_social = false;
                $review_in_dentist->save();
            }

            foreach ($this->user->reviews_in_clinic as $review_in_clinic) {
                $review_in_clinic->hasimage_social = false;
                $review_in_clinic->save();
            }
            
            return Response::json(['success' => true, 'thumb' => $this->user->getImageUrl(true), 'name' => '' ]);
        }
    
        
    }
    

    public function info($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('welcome-to-dentavox'));
        }
        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/vox'));
        }
        $this->handleMenu();
        

        if(Request::isMethod('post')) {

        	$validator_arr = [];
        	foreach ($this->profile_fields as $key => $value) {
        		$arr = [];
        		if (!empty($value['required'])) {
        			$arr[] = 'required';
        		}
        		if (!empty($value['is_email'])) {
                    $arr[] = 'email';
        			$arr[] = 'unique:users,email,'.$this->user->id;
        		}
                if (!empty($value['min'])) {
                    $arr[] = 'min:'.$value['min'];
                }
                if (!empty($value['max'])) {
                    $arr[] = 'max:'.$value['max'];
                }
                if (!empty($value['number'])) {
                    $arr[] = 'numeric';
                }
        		if (!empty($value['values'])) {
        			$arr[] = 'in:'.implode(',', array_keys($value['values']) );
        		}
                if (!empty($value['regex'])) {
                    $arr[] = $value['regex'];
                }

        		if (!empty($arr)) {
        			$validator_arr[$key] = $arr;
        		}
        	}

            if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
                request()->merge([
                    'website' => 'http://'.request('website')
                ]);
            }

        	$validator = Validator::make(Request::all(), $validator_arr);

	        if ($validator->fails()) {
	            return redirect( getLangUrl('profile/info') )
	            ->withInput()
	            ->withErrors($validator);
	        } else {

                if( $this->user->is_dentist && !User::validateAddress( Country::find( request('country_id')->name ), request('address') ) ) {
                    return redirect( getLangUrl('profile/info') )
                    ->withInput()
                    ->withErrors([
                        'address' => trans('trp.common.invalid-address')
                    ]);
                }

                if($this->user->validateMyEmail() == true) {
                    return redirect( getLangUrl('profile/info') )
                    ->withInput()
                    ->withErrors([
                        'email' => trans('vox.common.invalid-email')
                    ]);
                }

                if(!empty(Request::input('name')) && (User::validateLatin(Request::input('name')) == false)) {
                    if( Request::input('json') ) {
                        $ret = [
                            'success' => false,
                            'messages' => [
                                'name' => trans('trp.common.invalid-name')
                            ]
                        ];
                        return Response::json($ret);
                    }

                    return redirect( getLangUrl('profile/info') )
                    ->withInput()
                    ->withErrors([
                        'name' => trans('trp.common.invalid-name')
                    ]);
                }

                foreach ($this->profile_fields as $key => $value) {
                    $this->user->$key = Request::input($key);
                }

                $this->user->hasimage_social = false;
                $this->user->save();

                foreach ($this->user->reviews_out as $review_out) {
                    $review_out->hasimage_social = false;
                    $review_out->save();
                }

                foreach ($this->user->reviews_in_dentist as $review_in_dentist) {
                    $review_in_dentist->hasimage_social = false;
                    $review_in_dentist->save();
                }

                foreach ($this->user->reviews_in_clinic as $review_in_clinic) {
                    $review_in_clinic->hasimage_social = false;
                    $review_in_clinic->save();
                }

                Request::session()->flash('success-message', trans('vox.page.profile.info.updated'));
                return redirect( getLangUrl('profile/info') );
	        }
        }

		return $this->ShowVoxView('profile-info', [
            'menu' => $this->menu,
			'fields' => $this->profile_fields,
            'js' => [
                'profile.js',
                'address.js',
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ],
		]);
    }


    public function change_password($locale=null) {
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            return redirect(getLangUrl('welcome-to-dentavox'));
        }
        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/vox'));
        }

        $validator = Validator::make(Request::all(), [
			'cur-password' => 'required',
    		'new-password' => 'required|min:6',
            'new-password-repeat' => 'required|same:new-password',
        ]);

        if ($validator->fails()) {
            return redirect( getLangUrl('profile/info') )
            ->withInput()
            ->withErrors($validator);
        } else {
        	if ( !Hash::check(Request::input('cur-password'), $this->user->password) ) {
        		Request::session()->flash('error-message', trans('vox.page.profile.wrong-password'));
	    		return redirect( getLangUrl('profile/info') );
        	}
            
            $this->user->password = bcrypt(Request::input('new-password'));
            $this->user->save();
			
			Request::session()->flash('success-message', trans('vox.page.profile.info.password-updated'));
    		return redirect( getLangUrl('profile/info'));
	    }
	}


    public function jwt($locale=null) {
        $ret = [
            'success' => false
        ];
        if($this->user->isBanned('vox')) {
            $ret['message'] = 'banned';
            return Response::json( $ret );
        }
        
        if($this->user->is_dentist && $this->user->status!='approved' && $this->user->status!='added_approved' && $this->user->status!='test') {
            $ret['message'] = 'not-verified';
            return Response::json( $ret );
        }

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $ret = $this->user->validateCivicKyc($civic);
            if($ret['success']) {
                Request::session()->flash('success-message', trans('vox.page.profile.wallet.civic-validated'));
            }
        }

        
        return Response::json( $ret );
    }

    public function balance($locale=null) {
        return Response::json( User::getBalance( Request::input('vox-address') ) );
    }

    public function gdpr($locale=null) {
        $this->user->gdpr_privacy = true;
        $this->user->save();
        Request::session()->flash('success-message', trans('vox.page.profile.gdpr-done'));
        return redirect( getLangUrl('profile'));
    }


}