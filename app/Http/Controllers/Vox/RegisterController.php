<?php

namespace App\Http\Controllers\Vox;
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
use Cookie;

class RegisterController extends FrontController
{
    public function register($locale=null) {

        $messages = [
            'email.unique' => trans('vox.popup.register.email-taken',[
                'recoverlink' => '<a href="'.getLangUrl('forgot-password').'">',
                'endrecoverlink' => '</a>',
            ]),
        ];

    	$validator = Validator::make(Request::all(), [
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'gender' => array('required', 'in:m,f'),
            'birthyear' => array('required', 'numeric'),
            'country_id' => array('required', 'numeric'),
            'city_id' => array('required', 'numeric'),
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
            'agree' => array('accepted'),
        ], $messages);

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
                'secret' => env('CAPTCHA_SECRET'),
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

            if( !$captcha ) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'captcha' => trans('vox.popup.register.captcha')
                    )
                );

                return Response::json( $ret );
            }  

            $newuser = new User;
            $newuser->name = Request::input('name');
            $newuser->email = Request::input('email');
            $newuser->is_dentist = 0;
            $newuser->password = bcrypt(Request::input('password'));
            $newuser->country_id = Request::input('country_id');
            $newuser->city_id = Request::input('city_id');
            $newuser->gender = Request::input('gender');
            $newuser->birthyear = Request::input('birthyear');
            
            $newuser->save();

            $newuser->sendTemplate( 11 );

            Auth::login($newuser, Request::input('remember'));
            Request::session()->flash('success-message', trans('vox.page.registration.success'));
            return Response::json( [
                'success' => true,
                'url' => getLangUrl('/'),
            ] );
        }
    }


    public function invite_accept($locale=null, $id, $hash, $inv_id=null) {

        $user = User::find($id);

        if (!empty($user) && $user->canInvite('vox')) {

            if ($hash == $user->get_invite_token()) {

                if($this->user) {
                    if($this->user->id==$user->id) {
                        Request::session()->flash('success-message', trans('vox.page.registration.invite-yourself'));
                    } else {
                        if(!$this->user->wasInvitedBy($user->id)) {
                            $inv = UserInvite::find($inv_id);
                            if(empty($inv)) {
                                $inv = UserInvite::where('user_id', $user->id)->where('invited_email', 'LIKE', $this->user->email)->first();
                            }
                            if(empty($inv)) {
                                $inv = new UserInvite;
                                $inv->user_id = $user->id;
                            }
                            $inv->invited_name = $this->user->name;
                            $inv->invited_email = $this->user->email;
                            $inv->invited_id = $this->user->id;
                            $inv->save();
                        }
                        Request::session()->flash('success-message', trans('vox.page.registration.invitation-registered', [ 'name' => $user->name ]));
                    }
                    return redirect( getLangUrl('/') );
                } else {
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

                    Request::session()->flash('success-message', trans('vox.page.registration.invitation', [ 'name' => $user->name ]));
                    return redirect( getLangUrl('/').'#register' ); 
                }

            }
        } else {
            return redirect('/');
        }
    }

    public function register_verify($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user) && !$user->is_verified) {

            if ($hash == $user->get_token()) {

                $user->verified_on = Carbon::now();
                $user->is_verified = true;

                $user->save();

                $user->sendTemplate( 12 );

                Auth::login($user, true);

                Request::session()->flash('success-message', trans('vox.page.registration.profile-confirmed'));
                return redirect( getLangUrl('/'));
            }
        }
        else {
            return redirect('/');
        }
    }

    public function forgot($locale=null) {

        return $this->ShowVoxView('forgot-password');
    }

    public function forgot_form($locale=null) {

        $user = User::where([
            ['email','LIKE', Request::input('email') ]
        ])->first();

        if(empty($user->id)) {
            Request::session()->flash('error-message', trans('vox.page.registration.email-error'));
            return redirect( getLangUrl('forgot-password') );
        }

        $user->sendTemplate(13);

        Request::session()->flash('success-message', trans('vox.page.registration.email-success'));
        return redirect( getLangUrl('forgot-password') );
    }

    public function recover($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                return $this->ShowVoxView('recover-password', array(
                    'id' => $id,
                    'hash' => $hash,
                ));
            }
        }
        else {
            return redirect('/');
        }
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

                    Request::session()->flash('success-message', trans('vox.page.recover.success', [
                        'link' => '<a data-toggle="modal" data-target="#loginPopup" href="javascript:;">',
                        'endlink' => '</a>',
                    ]));
                    return redirect( getLangUrl('recover/'.$id.'/'.$hash) );
                }
            }
        }
    }
}