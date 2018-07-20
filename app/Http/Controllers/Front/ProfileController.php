<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Auth;
use Mail;
use Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\UserTeam;
use App\Models\UserCategory;
use App\Models\UserPhoto;
use App\Models\UserInvite;
use App\Models\UserAsk;
use App\Models\Dcn;
use App\Models\Reward;
use App\Models\TrpReward;
use App\Models\TrpCashout;
use App\Models\Civic;


class ProfileController extends FrontController
{

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->menu_dentist = [
        	'home' => trans('front.page.profile.dentist.home'),
			'info' => trans('front.page.profile.dentist.info'),
			'gallery' => trans('front.page.profile.dentist.gallery'),
			'password' => trans('front.page.profile.dentist.password'),
            'wallet' => trans('front.page.profile.dentist.wallet'),
            'invite' => trans('front.page.profile.dentist.invite'),
            //'reward' => trans('front.page.profile.dentist.reward'),
            'widget' => trans('front.page.profile.dentist.widget'),
			'history' => trans('front.page.profile.dentist.history'),
            'privacy' => trans('front.page.profile.patient.privacy'),
		];

		$this->menu_patient = [
        	'home' => trans('front.page.profile.patient.home'),
			'info' => trans('front.page.profile.patient.info'),
			'password' => trans('front.page.profile.patient.password'),
			'reviews' => trans('front.page.profile.patient.reviews'),
			'wallet' => trans('front.page.profile.patient.wallet'),
            'invite' => trans('front.page.profile.patient.invite'),
            //'reward' => trans('front.page.profile.dentist.reward'),
            'history' => trans('front.page.profile.dentist.history'),
            'privacy' => trans('front.page.profile.patient.privacy'),
		];

		$this->dentist_fields = [
            'title' => [
                'type' => 'select',
                'values' => [
                    '' => '-',
                    'dr' => 'Dr.',
                    'prof' => 'Prof. Dr.'
                ]
            ],
            'name' => [
                'type' => 'text',
                'required' => true,
                'min' => 3,
            ],
            'description' => [
                'type' => 'textarea',
            ],
            // 'is_clinic' => [
            //     'type' => 'select',
            //     'values' => [
            //         '0' => trans('front.clinic.no'),
            //         '1' => trans('front.clinic.yes'),
            //     ]
            // ],
    		'email' => [
    			'type' => 'text',
    			'required' => true,
    			'is_email' => true,
    		],
    		'country_id' => [
    			'type' => 'country',
    		],
    		'city_id' => [
    			'type' => 'city',
    		],
    		'zip' => [
    			'type' => 'text',
    		],
    		'address' => [
    			'type' => 'text',
    		],
            'phone' => [
                'type' => 'text',
                'subtype' => 'phone'
            ],
            'website' => [
                'type' => 'text',
            ],
            'categories' => [
                'type' => 'checkboxes',
                'values' => $this->categories
            ],
    		'work_hours' => [
    			'type' => 'work_hours'
    		],
    	];

    	$this->patient_fields = [
    		'name' => [
    			'type' => 'text',
    			'required' => true,
    			'min' => 3,
    		],
    		'email' => [
    			'type' => 'text',
    			'required' => true,
    			'is_email' => true,
    		],
    		'phone' => [
    			'type' => 'text',
    		],
    		'country_id' => [
    			'type' => 'country',
    		],
    		'city_id' => [
    			'type' => 'city',
    		],
    	];

        $this->genders = [
            '' => null,
            'm' => trans('admin.common.gender.m'),
            'f' => trans('admin.common.gender.f'),
        ];
    }

    private function handleMenu() {
        if($this->user->is_dentist && $this->user->asks->isNotEmpty()) {
            $this->menu_dentist['asks'] = trans('front.page.profile.dentist.asks');
        }

        if($this->user->is_clinic) {
            $this->menu_dentist['dentists'] = trans('front.page.profile.dentist.dentists'); //My dentists
        }

        if($this->user->is_dentist && !$this->user->is_clinic) {
            $this->menu_dentist['clinics'] = trans('front.page.profile.dentist.clinics'); //My clinic
        }
    }


    public function home($locale=null) {
        $this->handleMenu();

        $arr_dentist = [
            'photo-dentist' => 'javascript:$("#avatar-uplaoder").click();',
            'info' => getLangUrl("profile/info"),
            'gallery' => getLangUrl("profile/gallery"),
            'wallet' => getLangUrl("profile/wallet"),
            'invite-dentist' => getLangUrl("profile/invite"),
            'widget' => getLangUrl("profile/widget"),
        ];

        $arr_patient = [
            'photo-patient' => 'javascript:$("#avatar-uplaoder").click();',
            'wallet' => getLangUrl("profile/wallet"),
            'review' => getLangUrl("search"),
            'invite-patient' => getLangUrl("profile/invite"),
        ];

        // $d = User::find(3040);
        // $d->sendTemplate(31);
        // $d->sendTemplate(32);
        // $d = User::find(5352);
        // $d->sendTemplate(31);
        // $d->sendTemplate(32);
        

		return $this->ShowView('profile', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,

            'needs_avatar' => !$this->user->hasimage,
            'no_reviews' => !$this->user->is_dentist && $this->user->reviews_out->isEmpty(),
            'no_address' => $this->user->is_dentist && (!$this->user->city_id || !$this->user->address),
            'no_invites' => $this->user->invites->isEmpty(),
            'no_reward' => !$this->user->register_reward,

            'buttons_link' => $this->user->is_dentist ? $arr_dentist : $arr_patient,

            'my_reviews' => null,
            'my_upvotes' => null,
            'js' => [
                'profile.js',
                'dApp.js'
            ]
		]);
    }

    public function info($locale=null) {
        $this->handleMenu();

        $fields =  $this->user->is_dentist == 1 ? $this->dentist_fields : $this->patient_fields;

        if(Request::isMethod('post')) {

        	$validator_arr = [];
        	foreach ($fields as $key => $value) {
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

        		if (!empty($arr)) {
        			$validator_arr[$key] = $arr;
        		}
        	}

        	$validator = Validator::make(Request::all(), $validator_arr);

	        if ($validator->fails()) {
	            return redirect( getLangUrl('profile/info') )
	            ->withInput()
	            ->withErrors($validator);
	        } else {

                $phone = '';
                if(Request::Input('phone')) {
                    $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');
                    $other = User::where([
                        ['id', '!=', $this->user->id],
                        ['country_id', Request::input('country_id')],
                        ['phone', $phone],
                    ])->first();
                    if(!empty($other)) {
                        return redirect( getLangUrl('profile/info') )
                        ->withInput()
                        ->withErrors(['phone' => trans('front.common.phone-already-used')]);
                    }
                }

                $send_validate_email = $this->user->email != Request::input('email');

                foreach ($fields as $key => $value) {
                    if($key=='categories') {
                        UserCategory::where('user_id', $this->user->id)->delete();
                        if(!empty(Request::input($key))) {
                            foreach (Request::input($key) as $cat) {
                                $newc = new UserCategory;
                                $newc->user_id = $this->user->id;
                                $newc->category_id = $cat;
                                $newc->save();
                            }
                        }
                    } else if($key=='work_hours') {
                        $wh = Request::input($key);
                        if(!empty($wh) && is_array($wh)) {
                            foreach ($wh as $k => $v) {
                                if(is_array($v) && ( empty($v[0]) || empty($v[1]) ) ) {
                                    unset($wh[$k]);
                                }
                            }
                        }
                        $this->user->work_hours = json_encode($wh);
                    } else if($key=='phone') {
                        $this->user->phone = $phone;
                    } else {
        			    $this->user->$key = Request::input($key);
                    }
        		}
                $this->user->updateStrength();

                $this->user->save();


        		// if ($send_validate_email) {
        		// 	$this->user->sendTemplate( $this->user->is_dentist ? 1 : 2 );
        		// 	$this->user->is_verified = null;
        		// 	$this->user->verified_on = null;
        		// 	$this->user->save();
        		// }
                Request::session()->flash('success-message', trans('front.page.profile.info-updated'));
                return redirect( getLangUrl('profile/info') );
	        }
        }

		return $this->ShowView('profile-info', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
			'fields' => $fields,
            'js' => [
                'profile.js'
            ],
		]);
    }

    public function gallery($locale=null, $position=null) {
        $this->handleMenu();

        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        if( Request::file('image') && Request::file('image')->isValid() ) {
            if(!empty($this->user->photos[$position])) {
                $dapic = $this->user->photos[$position];
            } else {
                $dapic = new UserPhoto;
                $dapic->user_id = $this->user->id;
                $dapic->save();
            }

            $img = Image::make( Input::file('image') )->orientate();
            $dapic->addImage($img);
            return Response::json(['success' => true, 'url' => $dapic->getImageUrl(true), 'position' => $position]);
        }
        $this->user->updateStrength();

        return $this->ShowView('profile-gallery', [
            'menu' => $this->menu_dentist,
            'js' => [
                'profile.js'
            ],
        ]);
    }

    public function gallery_delete($locale=null, $position=null) {

        if(!empty($this->user->photos[$position])) {
            $this->user->photos[$position]->delete();
        }
        $this->user->updateStrength();

        return Response::json([ 'success' => true ] );
    }

    public function resend($locale=null) {
        $this->user->sendTemplate( $this->user->is_dentist ? 1 : 2 );

        return Response::json( ['success' => true] );
    }

    public function invite($locale=null) {
        $this->handleMenu();

        if(Request::isMethod('post') && $this->user->canInvite('trp') ) {

            if(Request::Input('is_contacts')) {
                if(empty(Request::Input('contacts')) || !is_array( Request::Input('contacts') ) ) {
                    return Response::json(['success' => false, 'message' => trans('front.page.profile.'.$this->current_subpage.'.contacts-none-selected') ] );
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
                    $already = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', $email],
                    ])->first();

                    if(!$already) {
                        $invitation = new UserInvite;
                        $invitation->user_id = $this->user->id;
                        $invitation->invited_email = $email;
                        $invitation->invited_name = $name;
                        $invitation->save();

                        //Mega hack
                        $dentist_name = $this->user->name;
                        $dentist_email = $this->user->email;
                        $this->user->name = '';
                        $this->user->email = $email;
                        $this->user->save();

                        $this->user->sendTemplate($this->user->is_dentist ? 7 : 17, [
                            'friend_name' => $dentist_name,
                            'invitation_id' => $invitation->id
                        ]);

                        //Back to original
                        $this->user->name = $dentist_name;
                        $this->user->email = $dentist_email;
                        $this->user->save();
                    }

                }
                $this->user->updateStrength();

                return Response::json(['success' => true, 'message' => trans('front.page.profile.'.$this->current_subpage.'.contacts-success') ] );
            } else {
                $validator = Validator::make(Request::all(), [
                    'email' => ['required', 'email'],
                    'name' => ['required', 'string'],
                ]);

                if ($validator->fails()) {
                    return Response::json(['success' => false, 'message' => trans('front.page.profile.'.$this->current_subpage.'.failure') ] );
                } else {
                    $already = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', Request::Input('email')],
                    ])->first();

                    if($already) {
                        return Response::json(['success' => false, 'message' => trans('front.page.profile.'.$this->current_subpage.'.already-invited') ] );                    
                    }

                    $invitation = new UserInvite;
                    $invitation->user_id = $this->user->id;
                    $invitation->invited_email = Request::Input('email');
                    $invitation->invited_name = Request::Input('name');
                    $invitation->save();

                    //Mega hack
                    $dentist_name = $this->user->name;
                    $dentist_email = $this->user->email;
                    $this->user->name = Request::Input('name');
                    $this->user->email = Request::Input('email');
                    $this->user->save();

                    $this->user->sendTemplate($this->user->is_dentist ? 7 : 17, [
                        'friend_name' => $dentist_name,
                        'invitation_id' => $invitation->id
                    ]);

                    //Back to original
                    $this->user->name = $dentist_name;
                    $this->user->email = $dentist_email;
                    $this->user->save();
                    $this->user->updateStrength();

                    return Response::json(['success' => true, 'message' => trans('front.page.profile.'.$this->current_subpage.'.success') ] );
                }
            }
        }

		return $this->ShowView('profile-invite', [
            'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'js' => [
                'profile.js',
                'hello.all.js',
            ],
		]);
    }


    public function asks($locale=null) {
        $this->handleMenu();
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        return $this->ShowView('profile-ask', [
            'menu' => $this->menu_dentist,
            'js' => [
                'profile.js',
            ],
        ]);
    }

    public function asks_accept($locale=null, $ask_id) {
        $this->handleMenu();
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'yes';
            $ask->save();

            $inv = new UserInvite;
            $inv->user_id = $this->user->id;
            $inv->invited_email = $ask->user->email;
            $inv->invited_name = $ask->user->name;
            $inv->invited_id = $ask->user->id;
            $inv->save();

            $ask->user->sendTemplate( 24 ,[
                'dentist_name' => $this->user->getName(),
                'dentist_link' => $this->user->getLink(),
            ]);
        }
        
        Request::session()->flash('success-message', trans('front.page.profile.password-updated'));
        return redirect( getLangUrl('profile/asks'));
    }

    public function asks_deny($locale=null, $ask_id) {
        $this->handleMenu();
        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        $ask = UserAsk::find($ask_id);
        if(!empty($ask) && $ask->dentist_id==$this->user->id && $ask->status=='waiting') {
            $ask->status = 'no';
            $ask->save();
        }
        
        Request::session()->flash('success-message', trans('front.page.profile.password-updated'));
        return redirect( getLangUrl('profile/asks'));
    }

    public function balance($locale=null) {
        return Response::json( User::getBalance( Request::input('balance-address') ) );
    }


    public function reward($locale=null) {

        $ret = [
            'success' => false,
            'message' => 'An error occured. Please try again later.'
        ];

        if(Request::isMethod('post') && !$this->user->register_reward) {

            if( !$this->user->canIuseAddress( Request::input('reward-address') ) ) {
                $ret['message'] = trans('front.common.address-used');
            } else {
                // $reward = new TrpReward();
                // $reward->user_id = $this->user->id;
                // $reward->reward = Reward::getReward('reward_register');
                // $reward->type = 'registration';
                // $reward->reference_id = null;
                // $reward->save();

                $this->user->register_reward = Request::input('reward-address');
                $this->user->save();

                $ret['success'] = true;
                Request::session()->flash('success-message', trans('front.page.profile.reward.done'));
            }

        }
        return Response::json($ret);
    }

    public function password($locale=null) {
        $this->handleMenu();

		return $this->ShowView('profile-password', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'js' => [
                'profile.js'
            ],
		]);
    }

    public function change_password($locale=null) {

        $validator = Validator::make(Request::all(), [
			'cur-password' => 'required',
    		'new-password' => 'required|min:6',
            'new-password-repeat' => 'required|same:new-password',
        ]);

        if ($validator->fails()) {
            return redirect( getLangUrl('profile/password') )
            ->withInput()
            ->withErrors($validator);
        } else {
        	if ( !Hash::check(Request::input('cur-password'), $this->user->password) ) {
        		Request::session()->flash('error-message', trans('front.page.profile.wrong-password'));
	    		return redirect( getLangUrl('profile/password') );
        	}
            
            $this->user->password = bcrypt(Request::input('new-password'));
            $this->user->save();
			
			Request::session()->flash('success-message', trans('front.page.profile.password-updated'));
    		return redirect( getLangUrl('profile/password'));
	    }
	}

    public function reviews($locale=null) {
        $this->handleMenu();

        return $this->ShowView('profile-reviews', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'my_reviews' => null,
            'my_upvotes' => null,
            'js' => [
                'profile.js',
                'dentist.js'
            ],
		]);
    }

    public function wallet($locale=null) {
        $this->handleMenu();

        return $this->ShowView('profile-wallet', [
            'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'js' => [
                'profile.js',
                'dApp.js'
            ],
            'jscdn' => [
                'https://hosted-sip.civic.com/js/civic.sip.min.js',
            ],
            'csscdn' => [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ],            
        ]);
    }

    public function jwt($locale=null) {
        $ret = [
            'success' => false
        ];
        if($this->user->isBanned('vox')) {
            $ret['message'] = 'banned';
            return Response::json( $ret );
        }
        
        if(!$this->user->is_verified || !$this->user->email) {
            $ret['message'] = 'not-verified';
            return Response::json( $ret );
        }

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $data = json_decode($civic->response, true);
            if(empty($data['data'])) {
                $ret['weak'] = true;
            } else if(!empty($data['userId'])) {
                $u = User::where('civic_id', 'LIKE', $data['userId'])->first();
                if(!empty($u)) {
                    $ret['duplicate'] = true;
                } else {
                    $this->user->civic_id = $data['userId'];
                    $this->user->save();
                    $ret['success'] = true;
                    Request::session()->flash('success-message', trans('vox.page.profile.wallet.civic-validated'));                    
                }
            }
        }

        
        return Response::json( $ret );
    }

    public function withdraw($locale=null) {

        if(!$this->user->canWithdraw('trp')) {
            return ;
        }

        $amount = intval(Request::input('withdraw-amount'));
        if($amount>$this->user->getTrpBalance()) {
            $ret = [
                'success' => false,
                'message' => trans('front.page.profile.amount-too-high')
            ];
        } else {
            $cashout = new TrpCashout;
            $cashout->user_id = $this->user->id;
            $cashout->reward = $amount;
            $cashout->address = $this->user->my_address();
            $cashout->save();

            $ret = Dcn::send($this->user, $this->user->my_address(), $amount, 'trp-cashout', $cashout->id);
            $ret['balance'] = $this->user->getTrpBalance();
            
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

    public function widget($locale=null) {
        $this->handleMenu();

        if (!$this->user->is_dentist) {
            return redirect( getLangUrl('profile') );
        }

        return $this->ShowView('profile-widget', [
            'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'js' => [
                'profile.js'
            ],
        ]);
    }

    public function history($locale=null) {
        $this->handleMenu();

		return $this->ShowView('profile-history', [
			'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
            'history' => $this->user->history->where('type', '!=', 'vox-cashout')
		]);
    }

    public function avatar($locale=null) {
        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $this->user->addImage($img);
            return Response::json(['success' => true, 'url' => $this->user->getImageUrl(true)]);
        }
        return Response::json(['success' => false]); 
    }

    public function remove_avatar($locale=null) {
        $this->user->hasimage = false;
        $this->user->updateStrength();
        $this->user->save();
        Request::session()->flash('success-message', trans('front.page.profile.avatar-removed'));
        return redirect( getLangUrl('profile'));
    }

    public function setEmail($locale=null) {
        $ret = [
            'success' => false
        ];
        if($this->user->is_verified) {
            $ret['success'] = true;
            $ret['verified'] = true;
        } else {
            $validator_arr = [
                'email' => ['required', 'email', 'unique:users,email,'.$this->user->id]
            ];
            $validator = Validator::make(Request::all(), $validator_arr);

            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->toArray();
                $ret['message'] = implode(', ', $msg['email']);
            } else {
                $this->user->email = Request::input('email');
                $this->user->save();
                $this->user->sendTemplate( $this->user->is_dentist ? 1 : 2 );
                $ret['success'] = true;
            }

        }


        return Response::json($ret);
    }

    public function privacy($locale=null) {
        $this->handleMenu();


        if(Request::isMethod('post')) {
            if( Request::input('action') ) {
                if( Request::input('action')=='delete' ) {
                    $this->user->sendTemplate( 29 );
                    $this->user->self_deleted = 1;
                    $this->user->save();
                    User::destroy( $this->user->id );
                    Auth::guard('web')->logout();
                    return redirect( getLangUrl('/') );
                }
            }
        }

        return $this->ShowView('profile-privacy', [
            'menu' => $this->user->is_dentist == 1 ? $this->menu_dentist : $this->menu_patient,
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

    public function gdpr($locale=null) {
        $this->user->gdpr_privacy = true;
        $this->user->save();
        Request::session()->flash('success-message', trans('front.page.profile.gdpr-done'));
        return redirect( getLangUrl('profile'));
    }

    public function dentists($locale=null) {
        $this->handleMenu();

        return $this->ShowView('profile-dentists', [
            'menu' => $this->menu_dentist,
            'js' => [
                'profile.js'
            ],
        ]);
    }

    public function dentists_delete( $locale=null, $id ) {
        $res = UserTeam::where('user_id', $this->user->id)->where('dentist_id', $id)->delete();

        if( $res ) {
            $dentist = User::find( $id );

            $dentist->sendTemplate(37, [
                'clinic-name' => $this->user->getName()
            ]);
        }
        
        //Success message
        Request::session()->flash('success-message', trans('front.page.profile.dentist-workpalce-deleted', ['name' => $dentist->getName() ]));

        return redirect( getLangUrl('profile/dentists'));
    }

    public function dentists_reject( $locale=null, $id ) {

        $res = UserTeam::where('user_id', $this->user->id)->where('dentist_id', $id)->delete();

        if( $res ) {
            $dentist = User::find( $id );

            $dentist->sendTemplate(36, [
                'clinic-name' => $this->user->getName()
            ]);
        }
        
        //Success message
        Request::session()->flash('success-message', trans('front.page.profile.dentist-workpalce-rejected'));

        return redirect( getLangUrl('profile/dentists'));
    }

    public function dentists_accept( $locale=null, $id ) {

        $item = UserTeam::where('dentist_id', $id)->where('user_id', $this->user->id)->first();

        if ($item) {
            
            $item->approved = 1;
            $item->save();

            $dentist = User::find( $id );

            $dentist->sendTemplate(35, [
                'clinic-name' => $this->user->getName()
            ]);
        }

        //Success message
        Request::session()->flash('success-message', trans('front.page.profile.dentist-workplace-accepted'));

        return redirect( getLangUrl('profile/dentists'));
    }

    public function clinics($locale=null) {
        $this->handleMenu();

        return $this->ShowView('profile-clinics', [
            'menu' => $this->menu_patient,
            'js' => [
                'profile.js'
            ],
        ]);
    }

    public function clinics_delete( $locale=null, $id ) {
        $res = UserTeam::where('dentist_id', $this->user->id)->where('user_id', $id)->delete();

        if( $res ) {
            $clinic = User::find( $id );

            $clinic->sendTemplate(38, [
                'dentist-name' => $this->user->getName()
            ]);
        }

        //Success message
        Request::session()->flash('success-message', trans('front.page.profile.dentist-workplace-left'));

        return redirect( getLangUrl('profile/clinics'));
    }

    public function inviteClinic() {

        if(!empty(Request::input('joinclinicid'))) {

            $clinic = User::find( Request::input('joinclinicid') );

            if(!empty($clinic)) {

                $newclinic = new UserTeam;
                $newclinic->dentist_id = $this->user->id;
                $newclinic->user_id = Request::input('joinclinicid');
                $newclinic->approved = 0;
                $newclinic->save();

                $clinic->sendTemplate(34, [
                    'dentist-name' =>$this->user->getName()
                ]);

                Request::session()->flash('success-message', trans('front.page.profile.clinic-invited'));
                return redirect( getLangUrl('profile/clinics'));
            }
        } else {
            return redirect( getLangUrl('profile/clinics'));
        }

    }

    public function inviteDentist() {

        if(!empty(Request::input('invitedentistid'))) {

            $dentist = User::find( Request::input('invitedentistid') );

            if(!empty($dentist)) {

                $newdentist = new UserTeam;
                $newdentist->dentist_id = Request::input('invitedentistid');
                $newdentist->user_id = $this->user->id;
                $newdentist->approved = 1;
                $newdentist->save();

                $dentist->sendTemplate(33, [
                    'clinic-name' => $this->user->getName()
                ]);

                Request::session()->flash('success-message', trans('front.page.profile.dentist-invited', ['name' => $dentist->getName() ]));
                return redirect( getLangUrl('profile/dentists'));
            }
        } else {
            return redirect( getLangUrl('profile/dentists'));
        }

    }

}