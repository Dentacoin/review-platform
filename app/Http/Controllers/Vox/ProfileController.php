<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use App\Models\User;
use App\Models\VoxIdea;
use App\Models\VoxCashout;
use App\Models\Dcn;


class ProfileController extends FrontController
{

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->menu = [
        	'home' => trans('vox.page.profile.dentist.home'),
			'info' => trans('vox.page.profile.dentist.info'),
			'password' => trans('vox.page.profile.dentist.password'),
			'wallet' => trans('front.page.profile.dentist.wallet'),
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
    }


    public function home($locale=null) {
        if(!($this->user->is_verified || $this->user->fb_id)) {
            return redirect(getLangUrl('/'));
        }

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
			'menu' => $this->menu,
            'js' => [
                'profile.js',
            ]
		]);
    }

    public function info($locale=null) {
        if(!($this->user->is_verified || $this->user->fb_id)) {
            return redirect(getLangUrl('/'));
        }

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

    public function password($locale=null) {
        if(!($this->user->is_verified || $this->user->fb_id)) {
            return redirect(getLangUrl('/'));
        }

		return $this->ShowVoxView('profile-password', [
            'menu' => $this->menu,
		]);
    }

    public function change_password($locale=null) {
        if(!($this->user->is_verified || $this->user->fb_id)) {
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
        
        if(!($this->user->is_verified || $this->user->fb_id)) {
            return redirect(getLangUrl('/'));
        }

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
            ]
		]);
    }

    public function balance($locale=null) {
        return Response::json( User::getBalance( Request::input('vox-address') ) );
    }


    public function withdraw($locale=null) {
        $amount = intval(Request::input('wallet-amount'));
        if($amount>$this->user->getVoxBalance()) {
            $ret = [
                'success' => false,
                'message' => trans('vox.page.profile.amount-too-high')
            ];
        } else {
            $ret = Dcn::send($this->user, $this->user->vox_address, $amount);
            if($ret['success']) {
                $cashout = new VoxCashout;
                $cashout->user_id = $this->user->id;
                $cashout->reward = $amount;
                $cashout->address = $this->user->vox_address;
                $cashout->tx_hash = $ret['message'];
                $cashout->save();
                $ret['balance'] = $this->user->getVoxBalance();
            }
        }

        return Response::json( $ret );
    }

}