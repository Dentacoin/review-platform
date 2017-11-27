<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Carbon\Carbon;

use Socialite;
use Auth;
use Request;
use Image;

class LoginController extends FrontController
{
    public function facebook_login($locale=null) {

    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return Socialite::driver('facebook')->redirect();
    }

    public function facebook_callback() {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('login'));
        }
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return $this->try_social_login(Socialite::driver('facebook')->user());
    }

    private function try_social_login($s_user) {

        $user = User::where( 'fb_id','LIKE', $s_user->getId() )->first();
        if(empty($user)) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->where('id', '<', 5200)->first();            
        }

        if ($user) {
            if($user->isBanned('vox')) {
                return redirect( getLangUrl('banned'));
            }

            Auth::login($user, true);
            return redirect( getLangUrl('/'));
        } else {
            Request::session()->flash('error-message', trans('front.page.login.error'));
            return redirect( getLangUrl('/'));
        }
    }




    public function facebook_register($locale=null) {
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        return Socialite::driver('facebook')
        ->setScopes(['user_friends', 'public_profile', 'email', 'user_location', 'user_birthday'])
        ->redirect();
    }


    public function facebook_callback_register() {
        
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('/') );
        }
        return $this->try_social_register(Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'verified', 'friends', 'gender', 'birthday', 'location'])->user(), 'fb');
    }

    private function try_social_register($s_user, $network) {

        $allset = isset($s_user->user['verified']) && isset($s_user->user['friends']);
        if(!$allset) {
            $url = 'https://graph.facebook.com/v2.5/me/permissions?access_token='. $s_user->token;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            
            Request::session()->flash('error-message', trans('vox.popup.register.incomplete-facebook') );
            return redirect(getLangUrl('/').'#register');
        }

        $verified = !empty($s_user->user['verified']) && !empty($s_user->user['friends']['summary']['total_count']) && $s_user->user['friends']['summary']['total_count']>50;
        if(!$verified) {
            Request::session()->flash('error-message', trans('vox.popup.register.fake-facebook') );
            return redirect(getLangUrl('/').'#register');
        }

        $user = User::where( 'fb_id','LIKE', $s_user->getId() )->first();
        if(empty($user)) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->where('id', '<', 5200)->first();            
        }

        if ($user) {
            if($user->isBanned('vox')) {
                return redirect( getLangUrl('banned'));
            }
            Auth::login($user, true);

            Request::session()->flash('success-message', trans('vox.popup.register.have-account'));
            return redirect(getLangUrl('/'));
        } else {
            $name = $s_user->getName() ? $s_user->getName() : explode('@', $s_user->getEmail() )[0];

            if(!empty($s_user->user['location']['name'])) {
                $loc_info = explode(',', $s_user->user['location']['name']);
                $fb_country = trim($loc_info[(count($loc_info)-1)]);
                $fb_city = trim($loc_info[0]);
            } else {
                $city_id = null;
                $country_id = null;
            }

            $gender = $s_user->user['gender']=='male' ? 'm' : 'f';
            $birthyear = !empty($s_user->user['birthday']) ? explode('/', $s_user->user['birthday'])[2] : 0;

            if(!empty($s_user->user['location']['name'])) {
                $loc_info = explode(',', $s_user->user['location']['name']);
                $fb_country = trim($loc_info[(count($loc_info)-1)]);
                $fb_city = trim($loc_info[0]);

                $country = Country::whereHas('translations', function ($query) use ($fb_country) {
                    $query->where('name', 'LIKE', $fb_country);
                })->first();
                if(!empty($country)) {
                    $country_id = $country->id;
                    $city = City::where('country_id', $country_id)->whereHas('translations', function ($query) use ($fb_city) {
                        $query->where('name', 'LIKE', $fb_city);
                    })->first();
                    if(!empty($city)) {
                        $city_id = $city->id;
                    }
                        
                }

            } else {
                $city_id = null;
                $country_id = null;
            }


            $password = $name.date('WY');
            $newuser = new User;
            $newuser->name = $name;
            $newuser->email = $s_user->getEmail();
            $newuser->password = bcrypt($password);
            $newuser->country_id = $country_id;
            $newuser->city_id = $city_id;
            $newuser->gender = $gender;
            $newuser->birthyear = $birthyear;
            $newuser->fb_id = $s_user->getId();
            
            $newuser->save();

            $newuser->sendTemplate( 11 );

            Auth::login($newuser, true);
            Request::session()->flash('success-message', trans('vox.page.registration.success'));
            return redirect(getLangUrl('/'));
        }
    }
}