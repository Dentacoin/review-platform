<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\User;
use App\Models\UserInvite;
use App\Models\Country;
use App\Models\City;
use Carbon\Carbon;

use Validator;
use Auth;
use Request;
use Response;
use Mail;

class RegisterController extends FrontController
{
    public function register($locale=null) {

        if(!empty($this->user)) {
            return redirect(getLangUrl('profile'));
        }

        
		return $this->ShowView('register', [
			'js' => [
				'register.js'
			],
            'invitation_email' => session('invitation_email'),
            'invitation_name' => session('invitation_name'),
		]);
    }

    public function register_form($locale=null) {

    	$validator = Validator::make(Request::all(), [
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'is_dentist' => array('required', 'boolean'),
            'country_id' => array('required', 'numeric'),
            'city_id' => array('required', 'numeric'),
            'phone' => array('required'),
            'password' => array('required', 'min:6'),
    		'password-repeat' => 'required|same:password',
        ]);

        if ($validator->fails()) {


            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $captcha = false;
            $cpost = [
                'secret' => '6LdmpjQUAAAAAF_3NBc2XtM_VdKp0g0BNsaeWFD3',
                'response' => Request::input('g-recaptcha-response'),
                'remoteip' => Request::ip()
            ];
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            if($response) {
                $api_response = json_decode($response, true);
                if(!empty($api_response['success'])) {
                    $captcha = true;
                }
            }

            if( Request::input('action')=='verify' && !$captcha ) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'captcha' => trans('front.page.registration.captcha')
                    )
                );

                return Response::json( $ret );
            }  

            $phone = null;
            $c = Country::find( Request::Input('country_id') );
            if(!empty($c)) {
                $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');
                $pn = $c->phone_code.' '.$phone;

                $validator = Validator::make(['phone' => $pn], [
                    'phone' => ['required','phone:'.$c->code],
                ]);


                if ($validator->fails()) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'phone' => trans('front.page.registration.phone')
                        ]
                    ] );
                }
            } else {
                return Response::json( [
                    'success' => false, 
                    'messages' => [
                        'phone' => trans('front.page.registration.phone')
                    ]
                ] );
            }

            $other = User::where([
                ['country_id', $c->id],
                ['phone', 'LIKE', $phone],
            ])->first();
            if(!empty($other)) {
                return Response::json( [
                    'success' => false,
                    'messages' => array(
                        'phone' => trans('front.page.registration.phone-taken')
                    )
                ] );
            }

            
            if( Request::input('action')=='verify' ) {
                //Send SMS
                $vc = rand(100000, 999999);
                session([
                    'sms_code' => $vc,
                    'real_number' => $phone,
                ]);

                $sms_text = trans( 'front.common.sms', ['code' => $vc] );
                //$this->user->sendSMS($sms_text);
                $formatted_phone = $c->phone_code.$phone;
                file_get_contents('https://bulksrv.allterco.net/sendsms/sms.php?nmb_from=1909&user=SWISSDENTAPRIME&pass=m9rr95er9em&nmb_to='.$formatted_phone.'&text='.urlencode($sms_text).'&dlrr=1');

                return Response::json( [
                    'success' => true,
                    'goon' => true
                ] );
            } else if( Request::input('action')=='confirm' ) {

                $real_number = session('real_number');
                $real = session('sms_code');
                $entered = Request::input('sms-code');
                if(empty($real_number) || empty($real) || $real!=$entered) {
                    return Response::json( [
                        'success' => false,
                        'messages' => array(
                            'phone' => trans('front.page.registration.phone-code')
                        )
                    ] );
                }

                $newuser = new User;
                $newuser->name = Request::input('name');
                $newuser->email = Request::input('email');
                $newuser->is_dentist = Request::input('is_dentist');
                $newuser->password = bcrypt(Request::input('password'));
                $newuser->country_id = $this->country_id;
                $newuser->city_id = $this->city_id;
                $newuser->phone = $real_number;
                $newuser->phone_verified = true;
                $newuser->phone_verified_on = Carbon::now();

                if(!empty(session('invited_by'))) {
                    $newuser->invited_by = session('invited_by');

                }

                $newuser->save();

                $inv_id = session('invitation_id');
                if($inv_id) {
                    $inv = UserInvite::find($inv_id);
                    if($inv && $inv->user_id == session('invited_by')) {
                        $inv->invited_id = $newuser->id;
                        $inv->save();
                    }
                }

                $newuser->sendTemplate( $newuser->is_dentist ? 1 : 2 );

                Auth::login($newuser, Request::input('remember'));

                if($newuser->invited_by) {
                    Request::session()->flash('success-message', trans('front.page.registration.success-by-invite', ['name' => $newuser->invitor->getName()]  ));
                } else {
                    Request::session()->flash('success-message', trans('front.page.registration.success'));
                }
                return Response::json( [
                    'success' => true,
                    'url' => $newuser->invited_by ? $newuser->invitor->getLink() : getLangUrl('profile'),
                ] );
            }
        }
    }

    public function invite_accept($locale=null, $id, $hash, $inv_id) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_invite_token()) {

                $sess = [
                    'invited_by' => $user->id,
                ];
                $inv = UserInvite::find($inv_id);
                if(!empty($inv)) {
                    $sess['invitation_name'] = $inv->invited_name;
                    $sess['invitation_email'] = $inv->invited_email;
                    $sess['invitation_id'] = $inv->id;
                }
                session($sess);

                Request::session()->flash('success-message', trans('front.page.registration.invitation', [ 'name' => $user->name ]));
                return redirect( getLangUrl('register'));
            }
        }
        else {
            return redirect('/');
        }
    }

    public function register_verify($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                $user->verified_on = Carbon::now();
                $user->is_verified = true;

                $user->save();

                $user->sendTemplate( $user->is_dentist ? 3 : 4 );

                Auth::login($user, true);

                Request::session()->flash('success-message', trans('front.page.registration.profile-confirmed'));
                return redirect( getLangUrl('profile'));
            }
        }
        else {
            return redirect('/');
        }
    }

    public function forgot($locale=null) {

		return $this->ShowView('forgot-password');
    }

    public function forgot_form($locale=null) {

		$user = User::where([
            ['email','LIKE', Request::input('email') ]
        ])->first();

    	if(empty($user->id)) {
            Request::session()->flash('error-message', trans('front.page.registration.email-error'));
            return redirect( getLangUrl('forgot-password') );
        }

        $user->sendTemplate(5);

        Request::session()->flash('success-message', trans('front.page.registration.email-success'));
        return redirect( getLangUrl('forgot-password') );
    }

    public function recover($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                return $this->ShowView('recover-password', array(
                    'id' => $id,
                    'hash' => $hash,
                ));
            }
        }
        else {
            return redirect('');
        }
    }
    
    public function claim($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_invite_token()) {

                if(Request::isMethod('post')) {
                    $validator = Validator::make(Request::all(), [
                        'password' => array('required', 'min:6'),
                        'password-repeat' => 'required|same:password',
                    ]);

                    if ($validator->fails()) {
                        return redirect( getLangUrl('claim/'.$id.'/'.$hash))
                        ->withInput()
                        ->withErrors($validator);
                    } else {
                        
                        $user->is_verified = true;
                        $user->password = bcrypt(Request::input('password'));
                        $user->save();

                        Auth::login($user, true);

                        Request::session()->flash('success-message', trans('front.page.claim.success'));
                        return redirect( getLangUrl('profile') );
                    }

                }

                return $this->ShowView('claim', array(
                    'id' => $id,
                    'hash' => $hash,
                    'future_profile' => $user
                ));
            }
        }

        return redirect('/');
    }

    public function recover_form($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {
                $validator = Validator::make(Request::all(), [
                    'password' => array('required', 'min:6'),
                    'password-repeat' => 'required|same:password',
                ]);

                if ($validator->fails()) {
                    return redirect( getLangUrl('recover/'.$id.'/'.$hash))
                    ->withInput()
                    ->withErrors($validator);
                } else {
                    
                    $user->password = bcrypt(Request::input('password'));
                    $user->save();

                    Request::session()->flash('success-message', trans('front.page.recover.success'));
                    return redirect( getLangUrl('login') );
                }
            }
        }
    }
}