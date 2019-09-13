<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use App\Models\Civic;
use App\Models\UserInvite;
use Carbon\Carbon;

use Session;
use Socialite;
use Auth;
use Response;
use Request;
use Image;

class LoginController extends FrontController
{
    public function facebook_login($locale=null, $path = null) {
        Session::flush();

        //dd('https://dev.dentavox.dentacoin.com/en/login/callback/facebook'.($path ? '/'.$path : ''));
    	//config(['services.facebook.redirect' => getLangUrl('login/callback/facebook') ]);
        config(['services.facebook.redirect' => 'https://dev.dentavox.dentacoin.com/en/login/callback/facebook'.($path ? '/'.$path : '') ]);
        return Socialite::driver('facebook')->redirect();
    }

    public function facebook_callback($locale=null, $path = null) {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getVoxUrl('/'));
        }
    	//config(['services.facebook.redirect' =>  getLangUrl('login/callback/facebook') ]);
        config(['services.facebook.redirect' =>  'https://dev.dentavox.dentacoin.com/en/login/callback/facebook'.($path ? '/'.$path : '') ]);
        return $this->try_social_login(Socialite::driver('facebook')->user(), $path);
    }

    private function try_social_login($s_user, $path = null) {

        if( session('new_auth') && !empty($this->user) && empty($this->user->fb_id) && empty($this->user->civic_id) ) {
            $user = $this->user;

            $duplicate = User::where('fb_id', $s_user->getId() )->first();

            if( $duplicate ) {
                //Request::session()->flash('error-message', 'There\'s another profile registered with this Facebook Account');
                return redirect( getVoxUrl('/').'?error-message='.urlencode('There\'s another profile registered with this Facebook Account'));

            } else {
                $user->fb_id = $s_user->getId();
                $user->save();
                session(['new_auth' => null]);

                Request::session()->flash('success-message');
                return redirect('/');
            }

        } else {
            if($s_user->getId()) {
                $user = User::where( 'fb_id','LIKE', $s_user->getId() )->first();            
            }
            if(empty($user) && $s_user->getEmail()) {
                $user = User::where( 'email','LIKE', $s_user->getEmail() )->first(); //->where('id', '<', 5200)
                if( !empty($user) && $user->fb_id != $s_user->getId() ) {
                    $user->fb_id = $s_user->getId();
                    $user->save();
                }      
            }

            if ($user) {
                if($user->loggedFromBadIp()) {
                    //dd('Bad IP', $s_user, $s_user->user);
                    return redirect( getVoxUrl('/').'?suspended-popup' );
                }

                $sess = [
                    'login_patient' => true,
                ];
                session($sess);

                Auth::login($user, true);
                
                if($user->isBanned('vox')) {
                    return redirect( getVoxUrl('profile-redirect'));
                }

                $intended = session()->pull('our-intended');

                if( $path  ){
                    return redirect( getVoxUrl($path) );
                }
                return redirect( $intended ? $intended : ( Request::input('intended') ? Request::input('intended') : getVoxUrl('/')) );
            } else {
                //dd('Other error', $s_user, $s_user->user);
                // Request::session()->flash('error-message', trans('vox.page.login.error-fb', [
                //     'link' => '<a href="'.getVoxUrl('/').'">',
                //     'endlink' => '</a>',
                // ]));
                return redirect( getVoxUrl('login').'?error-message='.urlencode(trans('vox.page.login.error-fb', [
                    'link' => '<a href="'.getVoxUrl('/').'">',
                    'endlink' => '</a>',
                ])));
            }
        }
    }


    public function facebook_register($locale=null) {
        Session::flush();
        
        //config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        config(['services.facebook.redirect' => 'https://dev.dentavox.dentacoin.com/en/register/callback/facebook' ]);
        return Socialite::driver('facebook')
        ->setScopes(['public_profile', 'email', 'user_location', 'user_birthday'])
        ->redirect();
    }


    public function facebook_callback_register() {
        
        //config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        config(['services.facebook.redirect' => 'https://dev.dentavox.dentacoin.com/en/register/callback/facebook' ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getVoxUrl('/') );
        }
        return $this->try_social_register(Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'gender', 'birthday', 'location'])->user(), 'fb');
    }

    private function try_social_register($s_user, $network) {

        if($s_user->getId()) {
            $user = User::where( 'fb_id','LIKE', $s_user->getId() )->withTrashed()->first();
        }
        if(empty($user) && $s_user->getEmail()) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->withTrashed()->first();            
        }

        $city_id = null;
        $country_id = null;
        if ($user) {
            if($user->deleted_at) {
                //Request::session()->flash('error-message', 'You have been permanently banned and cannot return to DentaVox anymore.');
                return redirect(getLangUrl('registration', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode('You have been permanently banned and cannot return to DentaVox anymore.'));
            } else {

                Auth::login($user, true);

                if($user->isBanned('vox')) {
                    return redirect( getVoxUrl('profile-redirect'));
                }
                
                $intended = session()->pull('our-intended');
                //Request::session()->flash('success-message', trans('vox.popup.register.have-account'));
                return redirect($intended ? $intended : ( Request::input('intended') ? Request::input('intended') : getVoxUrl('/').'?success-message='.urlencode(trans('vox.popup.register.have-account'))) );
            }
        } else {
            if (!empty($s_user->getEmail())) {
                
                $name = (!empty($s_user->user['first_name'] && !empty($s_user->user['last_name']))) ? ($s_user->user['first_name'].' '.$s_user->user['last_name']) : (!empty($s_user->getEmail()) ? explode('@', $s_user->getEmail() )[0] : 'User' );

                $is_blocked = User::checkBlocks($name, $s_user->getEmail());
                if( $is_blocked ) {
                    //Request::session()->flash('error-message', $is_blocked );
                    return redirect(getLangUrl('registration', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode($is_blocked));                
                }            

                if($s_user->getEmail() && (User::validateEmail($s_user->getEmail()) == true)) {
                    //Request::session()->flash('error-message', nl2br(trans('front.page.login.existing_email')) );
                    return redirect(getLangUrl('registration', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode(trans('vox.page.login.existing_email')));
                }

                $gender = !empty($s_user->user['gender']) ? ($s_user->user['gender']=='male' ? 'm' : 'f') : null;
                $birthyear = !empty($s_user->user['birthday']) ? explode('/', $s_user->user['birthday'])[2] : 0;

                if($birthyear && (intval(date('Y')) - $birthyear) < 18 ) {
                    //Request::session()->flash('error-message', nl2br(trans('front.page.login.over18')) );
                    return redirect(getLangUrl('registration', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode(trans('front.page.login.over18')));
                }
                
                $has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;

                $state_name = null;
                $state_slug = null;
                $city_name = null;
                $lat = null;
                $lon = null;
                $f_name = null;
                $l_name = null;
                if(!empty($s_user->user['location']['name'])) {

                    // $loc_info = explode(',', $s_user->user['location']['name']);
                    // $fb_country = trim($loc_info[(count($loc_info)-1)]);
                    // $fb_city = trim($loc_info[0]);

                    // $country = Country::whereHas('translations', function ($query) use ($fb_country) {
                    //     $query->where('name', 'LIKE', $fb_country);
                    // })->first();

                    // if(!empty($country)) {
                    //     $country_id = $country->id;
                    //     $city = City::where('country_id', $country_id)->whereHas('translations', function ($query) use ($fb_city) {
                    //         $query->where('name', 'LIKE', $fb_city);
                    //     })->first();
                    //     if(!empty($city)) {
                    //         $city_id = $city->id;
                    //     }
                            
                    // }

                    $info = User::validateAddress( '', $s_user->user['location']['name'] );
                    if (!empty($info['country_name'])) {
                        $fb_country = $info['country_name'];
                        $country = Country::whereHas('translations', function ($query) use ($fb_country) {
                            $query->where('name', 'LIKE', $fb_country);
                        })->first();

                        if (!empty($country)) {
                            $country_id = $country->id;
                        }
                    }
                    if (!empty($info['state_name'])) {
                        $state_name = $info['state_name'];
                    }
                    if (!empty($info['state_slug'])) {
                        $state_slug = $info['state_slug'];
                    }
                    if (!empty($info['city_name'])) {
                        $city_name = $info['city_name'];
                    }
                    if (!empty($info['lat'])) {
                        $lat = $info['lat'];
                    }
                    if (!empty($info['lon'])) {
                        $lon = $info['lon'];
                    }
                } else if($has_test) {
                    $country_id = !empty($has_test['location']) ? $has_test['location'] : '' ;
                }

                $password = $name.date('WY');
                $newuser = new User;
                $newuser->name = $name;
                $newuser->email = $s_user->getEmail() ? $s_user->getEmail() : '';
                $newuser->password = bcrypt($password);
                $newuser->country_id = !empty($country_id) ? $country_id : $this->country_id;
                $newuser->state_name = $state_name;
                $newuser->state_slug = $state_slug;
                $newuser->city_name = $city_name;
                $newuser->lat = $lat;
                $newuser->lon = $lon;
                // $newuser->city_id = $city_id;
                $newuser->gender = $gender;
                $newuser->birthyear = $birthyear;
                $newuser->fb_id = $s_user->getId();
                $newuser->gdpr_privacy = true;
                $newuser->platform = 'vox';
                $newuser->status = 'approved';

                if(!empty(session('invited_by'))) {
                    $newuser->invited_by = session('invited_by');
                }
                if(!empty(session('invite_secret'))) {
                    $newuser->invite_secret = session('invite_secret');
                }
                
                $newuser->save();


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


                if($newuser->invited_by && $newuser->invitor->canInvite('vox')) {
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

                    // $newuser->invitor->sendTemplate( 26, [
                    //     'who_joined_name' => $newuser->getName()
                    // ] );
                }

                $sess = [
                    'invited_by' => null,
                    'invitation_name' => null,
                    'invitation_email' => null,
                    'invitation_id' => null,
                    'just_registered' => true,
                    'just_registered_patient_vox' => true,
                ];
                session($sess);

                if( $newuser->email ) {
                    $newuser->sendTemplate( 12 );
                }

                if($newuser->loggedFromBadIp()) {
                    return redirect( getVoxUrl('/').'?suspended-popup' );
                }

                Auth::login($newuser, true);
                //Request::session()->flash('success-message', trans('vox.page.registration.success'));
                return redirect(getVoxUrl('/').'?success-message='.urlencode(trans('vox.page.registration.success')));
            } else {
                return redirect( getVoxUrl('/').'?error-message='.urlencode(trans('vox.page.registration.no-fb-email')));
            }
        }
    }

    public function civic() {

        $jwt = Request::input('jwtToken');
        $civic = Civic::where('jwtToken', 'LIKE', $jwt)->first();
        if(!empty($civic)) {
            $data = json_decode($civic->response, true);
            if(!empty($data['userId'])) {

                //dd($data);
                $email = null;
                $phone = null;

                if(!empty($data['data'])) {
                    foreach ($data['data'] as $dd) {
                        if($dd['label'] == 'contact.personal.email' && $dd['isOwner'] && $dd['isValid']) {
                            $email = $dd['value'];
                        }
                        if($dd['label'] == 'contact.personal.phoneNumber' && $dd['isOwner'] && $dd['isValid']) {
                            $phone = $dd['value'];
                        }
                    }
                }

                if(empty($email)) {
                    return redirect(getLangUrl('login', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode(trans('front.common.civic.weak')));
                } else {

                    if( session('new_auth') ) {
                        $user = $this->user;

                        $duplicate = User::where('civic_id', $data['userId'] )->first();

                        if( $duplicate ) {
                            return redirect(getLangUrl('login', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode('There\'s another profile registered with this Civic Account'));
                        } else {
                            $user->civic_id = $data['userId'];
                            $user->save();
                            session(['new_auth' => null]);

                            return redirect(getVoxUrl('/'));
                        }

                    } else {
                        $user = User::where( 'civic_id','LIKE', $data['userId'] )->first();
                        if(empty($user) && $email) {
                            $user = User::where( 'email','LIKE', $email )->first();            
                        }


                        if ($user) {
                            if($user->loggedFromBadIp()) {
                                
                                return redirect( getVoxUrl('/').'?suspended-popup' );

                            } else {

                                Auth::login($user, true);
                                if(empty($user->civic_id)) {
                                    $user->civic_id = $data['userId'];
                                    $user->save();      
                                }

                                $intended = session()->pull('our-intended');
                                
                                return redirect($user->isBanned('vox') ? getVoxUrl('profile-redirect') : ($intended ? $intended : ( Request::input('intended') ? Request::input('intended') : getVoxUrl('/')  )));
                                
                                $sess = [
                                    'login_patient' => true,
                                ];
                                session($sess);
                            }

                        } else {
                            return redirect(getLangUrl('login', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode(trans('front.common.civic.not-found')));
                        }
                    }
                }

            } else {
                return redirect(getLangUrl('login', null, 'https://vox.dentacoin.com/').'?noredirect=1&error-message='.urlencode(trans('front.common.civic.weak')));
            }
        }
    }

    public function new_facebook_login($locale=null) {

        $user = Socialite::driver('facebook')->userFromToken(Request::input('access-token'));

        return $this->try_social_login($user, 'fb');
    }

    public function new_facebook_register($locale=null) {

        $user = Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'gender', 'birthday', 'location'])->userFromToken(Request::input('access-token'));

        return $this->try_social_register($user, 'fb');
    }

    public function status() {
        return !empty($this->user) ? $this->user->convertForResponse() : null;
    }

}