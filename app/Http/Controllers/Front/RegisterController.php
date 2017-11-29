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

        if (!empty($user) && $user->is_dentist && !$user->is_verified) {

            if ($hash == $user->get_invite_token()) {

                session([
                    'claim_id' => $user->id,
                ]);


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