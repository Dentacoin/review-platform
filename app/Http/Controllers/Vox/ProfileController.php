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
use App\Models\User;
use App\Models\VoxIdea;
use App\Models\UserInvite;
use App\Models\VoxCashout;
use App\Models\Dcn;
use App\Models\Civic;


class ProfileController extends FrontController
{

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->menu = [
        	'home' => trans('vox.page.profile.dentist.home'),
			'info' => trans('vox.page.profile.dentist.info'),
			'password' => trans('vox.page.profile.dentist.password'),
            'wallet' => trans('front.page.profile.dentist.wallet'),
			'history' => trans('front.page.profile.dentist.history'),
            'invite' => trans('front.page.profile.patient.invite'),
            'privacy' => trans('front.page.profile.patient.privacy'),
		];

		$this->profile_fields = [
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
    		'country_id' => [
    			'type' => 'country',
                'required' => true,
    		],
    		'city_id' => [
    			'type' => 'city',
                'required' => true,
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

    private function handleMenu() {
        if($this->user->bans->isNotEmpty()) {
            $this->menu['bans'] = trans('vox.page.profile.dentist.bans');
        }
    }


    public function home($locale=null) {
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();

        // if($this->user->id==4232) {
        //     $this->user->banUser('vox', 'mistakes');
        //     return redirect(getLangUrl('profile/bans'));
        // }

        $histories = $this->user->vox_rewards->where('vox_id', '!=', 34);

        if(Request::isMethod('post')) {
            $ideatext = trim( Request::input('idea') );

            if(!empty($ideatext)) {
                $idea = new VoxIdea;
                $idea->user_id = $this->user->id;
                $idea->idea = $ideatext;
                $idea->save();

                $mtext = 'New idea submitted:
                
                '.$ideatext.'

                More info at:
                https://reviews.dentacoin.com/cms/vox/ideas';

                Mail::raw($mtext, function ($message) {

                    $sender = config('mail.from.address-vox');
                    $sender_name = config('mail.from.name');

                    $message->from($sender, $sender_name);
                    $message->to( $sender );
                    //$message->to( 'dokinator@gmail.com' );
                    $message->replyTo($sender, $sender_name);
                    $message->subject('New Questionnaire Idea Submitted');
                });

                $this->user->sendTemplate( 14 );


                return Response::json( [
                    'success' => true
                ] );
            }
            return Response::json( [
                'success' => false
            ] );
        }

		return $this->ShowVoxView('profile', [
            'histories' => $histories,
			'menu' => $this->menu,
            'js' => [
                'profile.js',
            ]
		]);
    }

    public function info($locale=null) {
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
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

                $send_validate_email = $this->user->email != Request::input('email');

                foreach ($this->profile_fields as $key => $value) {
        			$this->user->$key = Request::input($key);
        		}
        		$this->user->save();

        		// if ($send_validate_email) {
        		// 	$this->user->sendTemplate( $this->user->is_dentist ? 1 : 2 );
        		// 	$this->user->is_verified = null;
        		// 	$this->user->verified_on = null;
        		// 	$this->user->save();
        		// }
                Request::session()->flash('success-message', trans('vox.page.profile.info-updated'));
                return redirect( getLangUrl('profile/info') );
	        }
        }

		return $this->ShowVoxView('profile-info', [
            'menu' => $this->menu,
			'fields' => $this->profile_fields,
		]);
    }


    public function privacy($locale=null) {
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();


        if(Request::isMethod('post')) {
            if( Request::input('action') ) {
                if( Request::input('action')=='delete' ) {
                    $this->user->sendTemplate( 30 );
                    $this->user->self_deleted = 1;
                    $this->user->save();
                    User::destroy( $this->user->id );
                    Auth::guard('web')->logout();
                    return redirect( getLangUrl('/') );
                }
            }
        }

        return $this->ShowVoxView('profile-privacy', [
            'menu' => $this->menu,
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


    public function password($locale=null) {
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();

		return $this->ShowVoxView('profile-password', [
            'menu' => $this->menu,
		]);
    }

    public function change_password($locale=null) {
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
        }

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
        		Request::session()->flash('error-message', trans('vox.page.profile.wrong-password'));
	    		return redirect( getLangUrl('profile/password') );
        	}
            
            $this->user->password = bcrypt(Request::input('new-password'));
            $this->user->save();
			
			Request::session()->flash('success-message', trans('vox.page.profile.password-updated'));
    		return redirect( getLangUrl('profile/password'));
	    }
	}


    public function wallet($locale=null) {
        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/bans'));
        }
        
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
        }
        $this->handleMenu();

        if(Request::isMethod('post')) {
            $va = trim(Request::input('vox-address'));
            if(empty($va) || mb_strlen($va)!=42) {
                Request::session()->flash('error-message', trans('vox.page.profile.address-invalid'));
            } else if(!$this->user->canIuseAddress($va)) {
                Request::session()->flash('error-message', trans('vox.page.profile.address-used'));
            } else {
                $this->user->vox_address = Request::input('vox-address');
                $this->user->save();
                Request::session()->flash('success-message', trans('vox.page.profile.address-saved'));
            }
            
            return redirect( getLangUrl('profile/wallet'));
        }

        return $this->ShowVoxView('profile-wallet', [
            'menu' => $this->menu,
            'js' => [
                'wallet.js',
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


    public function address($locale=null) {

        $va = trim(Request::input('provide-address'));
        $ret = [
            'success' => false
        ];
        if(empty($va) || mb_strlen($va)!=42) {
            $ret['message'] = trans('vox.page.profile.address-invalid');
        } else if(!$this->user->canIuseAddress($va)) {
            $ret['message'] = trans('vox.page.profile.address-used');
        } else {
            $this->user->vox_address = Request::input('provide-address');
            $this->user->save();
            $ret['success'] = true;
            $ret['message'] = trans('vox.common.provide-address-done');
        }
        
        return Response::json( $ret );
    }

    public function balance($locale=null) {
        return Response::json( User::getBalance( Request::input('vox-address') ) );
    }


    public function withdraw($locale=null) {
        if($this->user->isBanned('vox') || !$this->user->canWithdraw('vox') ) {
            return ;
        }

        $amount = intval(Request::input('wallet-amount'));
        if($amount>$this->user->getVoxBalance()) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.amount-too-high')
            ];
        } else if($amount<env('VOX_MIN_WITHDRAW')) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.amount-too-low', [
                    'minimum' => '<b>'.env('VOX_MIN_WITHDRAW').'</b>'
                ])
            ];
        } else {
            $cashout = new VoxCashout;
            $cashout->user_id = $this->user->id;
            $cashout->reward = $amount;
            $cashout->address = $this->user->vox_address;
            $cashout->save();

            $ret = Dcn::send($this->user, $this->user->vox_address, $amount, 'vox-cashout', $cashout->id);
            $ret['balance'] = $this->user->getVoxBalance();
            
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

    public function history($locale=null) {
        if(!$this->user->is_verified || !$this->user->email) {
            return redirect(getLangUrl('/'));
        }

        $this->handleMenu();

        return $this->ShowVoxView('profile-history', [
            'menu' => $this->menu,
            'history' => $this->user->history->where('type', '=', 'vox-cashout')
        ]);
    }

    public function bans($locale=null) {
        $this->handleMenu();

        return $this->ShowVoxView('profile-bans', [
            'menu' => $this->menu,
            'ban_info' => $this->user->isBanned('vox')
        ]);
    }

    
    public function invite($locale=null) {
        if(!$this->user->canInvite('vox')) {
            return redirect(getLangUrl('/'));
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

                        $this->user->sendTemplate(25, [
                            'friend_name' => $dentist_name,
                            'invitation_id' => $invitation->id
                        ]);

                        //Back to original
                        $this->user->name = $dentist_name;
                        $this->user->email = $dentist_email;
                        $this->user->save();
                    }

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
                    $already = UserInvite::where([
                        ['user_id', $this->user->id],
                        ['invited_email', 'LIKE', Request::Input('email')],
                    ])->first();

                    if($already) {
                        return Response::json(['success' => false, 'message' => trans('vox.page.profile.'.$this->current_subpage.'.already-invited') ] );                    
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

                    $this->user->sendTemplate(25, [
                        'friend_name' => $dentist_name,
                        'invitation_id' => $invitation->id
                    ]);

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
                $this->user->sendTemplate( 11 );
                $ret['success'] = true;
            }

        }


        return Response::json($ret);
    }

    public function gdpr($locale=null) {
        $this->user->gdpr_privacy = true;
        $this->user->save();
        Request::session()->flash('success-message', trans('vox.page.profile.gdpr-done'));
        return redirect( getLangUrl('profile'));
    }


}