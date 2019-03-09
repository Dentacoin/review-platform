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
    public function facebook_login($locale=null) {

    	config(['services.facebook.redirect' => 'https://dev-dentavox.dentacoin.com/en/login/callback/facebook' ]);
        return Socialite::driver('facebook')->redirect();
    }

    public function facebook_callback() {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('/'));
        }
    	config(['services.facebook.redirect' =>  'https://dev-dentavox.dentacoin.com/en/login/callback/facebook' ]);
        return $this->try_social_login(Socialite::driver('facebook')->user());
    }

    private function try_social_login($s_user) {

        if( session('new_auth') && !empty($this->user) && empty($this->user->fb_id) && empty($this->user->civic_id) ) {
            $user = $this->user;

            $duplicate = User::where('fb_id', $s_user->getId() )->first();

            if( $duplicate ) {
                Request::session()->flash('error-message', 'There\'s another profile registered with this Facebook Account');
                return redirect( getLangUrl('/'));

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
                $user = User::where( 'email','LIKE', $s_user->getEmail() )->where('id', '<', 5200)->first();            
            }

            if ($user) {
                if($user->loggedFromBadIp()) {
                    return redirect( getLangUrl('login').'?suspended-popup' );
                }

                $sess = [
                    'login_patient' => true,
                ];
                session($sess);

                Auth::login($user, true);
                $intended = session()->pull('our-intended');
                return redirect( $intended ? $intended : getLangUrl('/') );
            } else {
                Request::session()->flash('error-message', trans('vox.page.login.error-fb', [
                    'link' => '<a href="'.getLangUrl('/').'">',
                    'endlink' => '</a>',
                ]));
                return redirect( getLangUrl('login'));
            }
        }
    }


    public function facebook_register($locale=null) {
        config(['services.facebook.redirect' => 'https://dev-dentavox.dentacoin.com/en/register/callback/facebook' ]);
        return Socialite::driver('facebook')
        ->setScopes(['user_friends', 'public_profile', 'email', 'user_location', 'user_birthday'])
        ->redirect();
    }


    public function facebook_callback_register() {
        
        config(['services.facebook.redirect' => 'https://dev-dentavox.dentacoin.com/en/register/callback/facebook' ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('/') );
        }
        return $this->try_social_register(Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'verified', 'friends', 'gender', 'birthday', 'location'])->user(), 'fb');
    }

    private function try_social_register($s_user, $network) {

        //dd($s_user);
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
            
            Request::session()->flash('error-message', trans('vox.popup.register.incomplete-facebook') );
            return redirect(getLangUrl('registration'));
        }

        //!empty($s_user->user['verified']) &&
        $verified = !empty($s_user->user['friends']['summary']['total_count']) && $s_user->user['friends']['summary']['total_count']>50;
        if(!$verified) {
            Request::session()->flash('error-message', trans('vox.popup.register.fake-facebook') );
            return redirect(getLangUrl('registration'));
        }


        if($s_user->getId()) {
            $user = User::where( 'fb_id','LIKE', $s_user->getId() )->withTrashed()->first();
        }
        if(empty($user) && $s_user->getEmail()) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->where('id', '<', 5200)->withTrashed()->first();            
        }

        $city_id = null;
        $country_id = null;
        if ($user) {
            if($user->deleted_at) {
                Request::session()->flash('error-message', 'You have been permanently banned and cannot return to DentaVox anymore.');
                return redirect(getLangUrl('registration'));
            } else {

                if($user->isBanned('vox')) {
                    return redirect( getLangUrl('profile'));
                }
                Auth::login($user, true);

                Request::session()->flash('success-message', trans('vox.popup.register.have-account'));
                return redirect(getLangUrl('/'));
                
            }
        } else {
            $name = $s_user->getName() ? $s_user->getName() : (!empty($s_user->getEmail()) ? explode('@', $s_user->getEmail() )[0] : 'User' );

            $is_blocked = User::checkBlocks($name, $s_user->getEmail());
            if( $is_blocked ) {
                Request::session()->flash('error-message', $is_blocked );
                return redirect(getLangUrl('registration'));                
            }

            $gender = !empty($s_user->user['gender']) ? ($s_user->user['gender']=='male' ? 'm' : 'f') : null;
            $birthyear = !empty($s_user->user['birthday']) ? explode('/', $s_user->user['birthday'])[2] : 0;

            if($birthyear && (intval(date('Y')) - $birthyear) < 18 ) {
                Request::session()->flash('error-message', nl2br(trans('front.page.login.over18')) );
                return redirect(getLangUrl('registration'));
            }

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

            }

            $password = $name.date('WY');
            $newuser = new User;
            $newuser->name = $name;
            $newuser->email = $s_user->getEmail() ? $s_user->getEmail() : '';
            $newuser->password = bcrypt($password);
            $newuser->country_id = $country_id;
            $newuser->city_id = $city_id;
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
                return redirect( getLangUrl('registration').'?suspended-popup' );
            }

            Auth::login($newuser, true);
            Request::session()->flash('success-message', trans('vox.page.registration.success'));
            return redirect(getLangUrl('welcome-to-dentavox'));
        }
    }

    public function civic() {
        $ret = [
            'success' => false
        ];

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
                    $ret['weak'] = true;
                } else {




                    if( session('new_auth') ) {
                        $user = $this->user;

                        $duplicate = User::where('civic_id', $data['userId'] )->first();

                        if( $duplicate ) {
                            $ret['message'] = 'There\'s another profile registered with this Civic Account';
                        } else {
                            $user->civic_id = $data['userId'];
                            $user->save();
                            session(['new_auth' => null]);

                            $ret['success'] = true;
                            $ret['redirect'] = getLangUrl('/');
                        }

                    } else {
                        $user = User::where( 'civic_id','LIKE', $data['userId'] )->first();
                        if(empty($user) && $email) {
                            $user = User::where( 'email','LIKE', $email )->first();            
                        }


                        if ($user) {
                            if($user->loggedFromBadIp()) {
                                
                                $ret['success'] = false;
                                $ret['popup'] = 'suspended-popup';

                            } else {

                                Auth::login($user, true);
                                if(empty($user->civic_id)) {
                                    $user->civic_id = $data['userId'];
                                    $user->save();      
                                }

                                $ret['success'] = true;
                                $ret['redirect'] = $user->isBanned('vox') ? getLangUrl('profile') : getLangUrl('/');

                                
                                $sess = [
                                    'login_patient' => true,
                                ];
                                session($sess);
                            }

                        } else {
                            $ret['message'] = trans('front.common.civic.not-found');
                        }
                    }
                }

            } else {
                $ret['weak'] = true;
            }
        }

        
        return Response::json( $ret );
    }
}