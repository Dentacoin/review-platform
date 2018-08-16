<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\Dcn;
use App\Models\UserInvite;
use App\Models\Blacklist;
use App\Models\BlacklistBlock;
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

    public function twitter_login($locale=null) {

    	config(['services.twitter.redirect' => getLangUrl('login/callback/twitter')]);
        return Socialite::driver('twitter')->redirect();
    }

    public function gplus_login($locale=null) {

    	config(['services.google.redirect' => getLangUrl('login/callback/gplus')]);
        return Socialite::driver('google')->redirect();
    }


    public function facebook_callback() {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('login'));
        }
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return $this->try_social_login(Socialite::driver('facebook')->user());
    }

    public function twitter_callback() {
    	config(['services.twitter.redirect' => getLangUrl('login/callback/twitter')]);
        return $this->try_social_login(Socialite::driver('twitter')->user());
    }

    public function gplus_callback() {
    	config(['services.google.redirect' => getLangUrl('login/callback/gplus')]);
        return $this->try_social_login(Socialite::driver('google')->user());
    }


    private function try_social_login($s_user) {

        if($s_user->getId()) {
            $user = User::where( 'fb_id','LIKE', $s_user->getId() )->first();
        }
        if(empty($user) && $s_user->getEmail()) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->where('id', '<', 5200)->first();            
        }

        if ($user) {
            
            if( $user->isBanned('vox') ) {
                return redirect( getLangUrl('login') )
                ->withInput()
                ->with('error-message', trans('front.page.login.vox-ban'));
            }

            Auth::login($user, true);

            Request::session()->flash('success-message', trans('front.page.login.success'));
            return redirect('/');
        } else {
            Request::session()->flash('error-message', trans('front.page.login.error'));
            return redirect( getLangUrl('login'));
        }
    }




    public function facebook_register($locale=null, $type) {
        if($type=='patient') {
            session([
                'is_dentist' => false,
                'is_clinic' => false, 
            ]);            
        } else if($type=='dentist') {
            session([
                'is_dentist' => true,
                'is_clinic' => false, 
            ]);            
        } else if($type=='clinic') {
            session([
                'is_dentist' => true,
                'is_clinic' => true, 
            ]);            
        } else {
            return redirect( getLangUrl('register'));
        }
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        return Socialite::driver('facebook')
        ->scopes(['user_friends'])
        ->redirect();
    }
/*
    public function twitter_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.twitter.redirect' => getLangUrl('register/callback/twitter') ]);
        return Socialite::driver('twitter')->redirect();
    }

    public function gplus_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.google.redirect' => getLangUrl('register/callback/gplus') ]);
        return Socialite::driver('google')->redirect();
    }
*/

    public function facebook_callback_register() {
        
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }
        return $this->try_social_register(Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'verified', 'friends'])->user(), 'fb');
    }
/*
    public function twitter_callback_register() {
        config(['services.twitter.redirect' => getLangUrl('register/callback/twitter') ]);

        // if (!Request::has('code') || Request::has('denied')) {
        //     dd('bla');
        //     return redirect('register');
        // }
        return $this->try_social_register(Socialite::driver('twitter')->user(), 'tw');
    }

    public function gplus_callback_register() {
        config(['services.google.redirect' => getLangUrl('register/callback/gplus') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }
        return $this->try_social_register(Socialite::driver('google')->user(), 'gp');
    }
*/

    private function try_social_register($s_user, $network) {
        //dd($s_user);
        return redirect( getLangUrl('register') )
        ->withInput()
        ->with('error-message', 'Due to the overwhelming surge in popularity, new registrations on DentaVox are currently disabled to allow for infrastructure & security upgrades. Thank you for your understanding!');

        //isset($s_user->user['verified']) && 
        $allset = isset($s_user->user['friends']);
        if(!$allset) {
            $url = 'https://graph.facebook.com/v2.5/me/permissions?access_token='. $s_user->token;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            
            Request::session()->flash('error-message', trans('front.page.registration.incomplete-facebook') );
            return redirect(getLangUrl('register'));
        }

        //!empty($s_user->user['verified']) && 
        $verified = !empty($s_user->user['friends']['summary']['total_count']) && $s_user->user['friends']['summary']['total_count']>50;
        if(!$verified) {
            Request::session()->flash('error-message', trans('front.page.registration.fake-facebook'));
            return redirect(getLangUrl('register'));
        }

        $is_dentist = session('is_dentist');
        $is_clinic = session('is_clinic');
        if($s_user->getId()) {
            $user = User::where( 'fb_id','LIKE', $s_user->getId() )->first();
        }
        if(empty($user) && $s_user->getEmail()) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->where('id', '<', 5200)->first();            
        }

        if ($user) {
            if( $user->isBanned('vox') ) {
                Request::session()->flash('error-message', trans('front.page.login.vox-ban'));
                return redirect( getLangUrl('register') )
                ->withInput()
                ->with('error-message', trans('front.page.login.vox-ban'));
            }
            Auth::login($user, true);
            if(empty($user->fb_id)) {
                $user->fb_id = $s_user->getId();
                $user->save();      
            }

            Request::session()->flash('success-message', trans('front.page.registration.have-account'));
            return redirect(getLangUrl('profile'));
        } else {

            $claiming = session('claim_id');
            if($claiming) {
                $newuser = User::find($claiming);
                $newuser->is_verified = true;
                $newuser->verified_on = Carbon::now();
                $newuser->fb_id = $s_user->getId();
                $newuser->save();

                $newuser->sendTemplate( 3 );

                Auth::login($newuser, true);
                Request::session()->flash('success-message', trans('front.page.registration.completed-by-claim'));
                return redirect( getLangUrl('profile') );
            }

            $name = $s_user->getName() ? $s_user->getName() : ( !empty($s_user->user['first_name']) && !empty($s_user->user['last_name']) ? $s_user->user['first_name'].' '.$s_user->user['last_name'] : ( !empty($s_user->getEmail()) ? explode('@', $s_user->getEmail() )[0] : 'User' ) );

            foreach (Blacklist::get() as $b) {
                if ($b['field'] == 'name') {
                    if (fnmatch(mb_strtolower($b['pattern']), mb_strtolower($name)) == true) {

                        $new_blacklist_block = new BlacklistBlock;
                        $new_blacklist_block->blacklist_id = $b['id'];
                        $new_blacklist_block->name = $name;
                        $new_blacklist_block->email = $s_user->getEmail();
                        $new_blacklist_block->save();

                        Request::session()->flash('error-message', trans('front.page.login.blocked-name'));
                        return redirect( getLangUrl('register') )
                        ->withInput()
                        ->with('error-message', trans('front.page.login.blocked-name'));
                    }
                } else {
                    if (fnmatch(mb_strtolower($b['pattern']), mb_strtolower($s_user->getEmail())) == true) {

                        $new_blacklist_block = new BlacklistBlock;
                        $new_blacklist_block->blacklist_id = $b['id'];
                        $new_blacklist_block->name = $name;
                        $new_blacklist_block->email = $s_user->getEmail();
                        $new_blacklist_block->save();
                        
                        Request::session()->flash('error-message', trans('front.page.login.blocked-email'));
                        return redirect( getLangUrl('register') )
                        ->withInput()
                        ->with('error-message', trans('front.page.login.blocked-email'));
                    }
                }
            }

            $password = $name.date('WY');
            $newuser = new User;
            $newuser->name = $name;
            $newuser->email = $s_user->getEmail() ? $s_user->getEmail() : '';
            $newuser->is_verified = $s_user->getEmail() ? true : false;
            $newuser->password = bcrypt($password);
            $newuser->is_dentist = $is_dentist;
            $newuser->is_clinic = $is_clinic;
            $newuser->verified_on = Carbon::now();
            $newuser->country_id = $this->country_id;
            $newuser->fb_id = $s_user->getId();
            $newuser->gdpr_privacy = true;
            $newuser->platform = 'trp';
            
            if(!empty(session('invited_by'))) {
                $newuser->invited_by = session('invited_by');
            }
            if(!empty(session('invite_secret'))) {
                $newuser->invite_secret = session('invite_secret');
            }
            
            $newuser->save();

            if(!$newuser->is_dentist) {                
                $avatarurl = $s_user->getAvatar();
                if($network=='fb') {
                    $avatarurl .= '&width=600&height=600';                
                } else if($network=='gp') {
                    $avatarurl = str_replace('sz=50', 'sz=600', $avatarurl);
                } else if($network=='tw') {
                    $avatarurl = str_replace('_normal', '', $avatarurl);
                }
                if(!empty($avatarurl)) {
                    $img = Image::make($avatarurl);
                    $newuser->addImage($img);
                }
            }


            if($newuser->invited_by && $newuser->invitor->canInvite('trp')) {
                $inv_id = session('invitation_id');
                if($inv_id) {
                    $inv = UserInvite::find($inv_id);
                } else {
                    $inv = new UserInvite;
                    $inv->user_id = $newuser->invited_by;
                    $inv->invited_email = $newuser->email;
                    $inv->invited_name = $newuser->name;
                    $inv->save();
                }

                $inv->invited_id = $newuser->id;
                $inv->save();

                $newuser->invitor->sendTemplate( $newuser->invitor->is_dentist ? 18 : 19, [
                    'who_joined_name' => $newuser->getName()
                ] );
            }

            $sess = [
                'invited_by' => null,
                'invitation_name' => null,
                'invitation_email' => null,
                'invitation_id' => null,
                'just_registered' => true,
            ];
            session($sess);

            if( $newuser->email ) {
                $newuser->sendTemplate( $newuser->is_dentist ? 3 : 4 );                
            }

            Auth::login($newuser, true);

            if($newuser->invited_by && $newuser->invitor->is_dentist) {
                Request::session()->flash('success-message', trans('front.page.registration.completed-by-invite', ['name' => $newuser->invitor->getName()]));
            } else {
                Request::session()->flash('success-message', trans('front.page.registration.completed'));
            }
            return redirect( $newuser->invited_by && $newuser->invitor->is_dentist ? $newuser->invitor->getLink() : getLangUrl('profile') );
        }
    }
}