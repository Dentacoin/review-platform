<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\Country;
use App\Models\Reward;
use App\Models\Civic;
use App\Models\User;
use App\Models\Dcn;

use Carbon\Carbon;

use Socialite;
use Response;
use Request;
use Image;
use Mail;
use Auth;

class LoginController extends FrontController {

    public function facebook_login($locale=null) {
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return Socialite::driver('facebook')->redirect();
    }

    public function facebook_callback() {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('login'));
        }
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);

        try {
            $user = Socialite::driver('facebook')->user();
        } catch (\Exception $e) {
            $user = false;
        }
        if (!empty($user)) {
            return $this->try_social_login($user);
        } else {
            return redirect(getLangUrl('/'));
        }
    }

    private function try_social_login($s_user) {

        if( session('new_auth') && !empty($this->user) && empty($this->user->fb_id) && empty($this->user->civic_id) ) {
            $user = $this->user;

            $duplicate = User::where('fb_id', $s_user->getId() )->first();

            if( $duplicate ) {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'banned-popup']))
                ->withInput();
            } else {

                if( $user->loggedFromBadIp() ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                    ->withInput();
                } else if($user->self_deleted) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-login']))
                    ->withInput()
                    ->with('error-message', trans('trp.popup.login.error-fb.self-deleted') );
                }

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
                if( $user->isBanned('trp') ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'banned-popup']));
                } else if($user->self_deleted) {
                    return redirect()->to( getLangUrl('/', null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'popup-login']))
                    ->withInput()
                    ->with('error-message', trans('trp.popup.login.error-fb.self-deleted') );
                } else if( $user->loggedFromBadIp() ) {

                    $ul = new UserLogin;
                    $ul->user_id = $user->id;
                    $ul->ip = User::getRealIp();
                    $ul->platform = 'trp';
                    $ul->country = \GeoIP::getLocation()->country;

                    $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                    $dd = new DeviceDetector($userAgent);
                    $dd->parse();

                    if ($dd->isBot()) {
                        // handle bots,spiders,crawlers,...
                        $ul->device = $dd->getBot();
                    } else {
                        $ul->device = $dd->getDeviceName();
                        $ul->brand = $dd->getBrandName();
                        $ul->model = $dd->getModel();
                        $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                    }
                    
                    $ul->save();

                    $action = new UserAction;
                    $action->user_id = $user->id;
                    $action->action = 'deleted';
                    $action->reason = 'Automatically - Bad IP (FB login)';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $user->deleteActions();
                    User::destroy( $user->id );

                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                    ->withInput();
                }

                $sess = [
                    'login_patient' => true,
                ];
                session($sess);

                $fb_name = $s_user->getName() ? $s_user->getName() : ( !empty($s_user->getNickname()) ? $s_user->getNickname() : null );

                if (!empty($user->civic_id) && !empty($fb_name)) {
                    $user->name = $fb_name;
                    $user->save();
                }

                Auth::login($user, true);

                if(!empty(session('invitation_id'))) {

                    $inv_id = session('invitation_id');
                    $inv = UserInvite::find($inv_id);

                    if (!empty($inv) && empty($inv->invited_id)) {
                        $inv->invited_id = $user->id;

                        if ($inv->invited_email == 'whatsapp') {
                            $inv->invited_email = $user->email;
                            $inv->invited_name = $user->name;
                        }

                        $inv->rewarded = true;
                        $inv->save();

                        $dentist_invitor = User::find($inv->user_id);

                        if (!empty($dentist_invitor)) {
                            return redirect($dentist_invitor->getLink().'?'. http_build_query(['popup'=>'submit-review-popup']));
                        }
                    } else {

                        $intended = session()->pull('intended-sess');
                        return redirect( $intended ? $intended : getLangUrl('/'));
                    }
                } else {

                    $want_to_invite = false;
                    if(session('want_to_invite_dentist')) {
                        $want_to_invite = true;
                        session([
                            'want_to_invite_dentist' => null,
                        ]);
                    }

                    $intended = session()->pull('intended-sess');
                    return redirect( $intended ? $intended : getLangUrl('/').($want_to_invite ? '?'.http_build_query(['popup'=>'invite-new-dentist-popup']) : '' ));
                }

            } else {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-login']))
                    ->withInput()
                    ->with('error-message', trans('trp.popup.login.error-fb', [
                    'link' => '<a class="sign-in-button" href="javascript:;">',
                    'endlink' => '</a>',
                ]) );
            }
        }

    }

    public function facebook_register($locale=null, $type='patient') {
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        return Socialite::driver('facebook')->scopes(['user_location'])
        ->redirect();
    }

    public function facebook_callback_register() {
        
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }

        try {
            $user = Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'location'])->user();
        } catch (\Exception $e) {
            $user = false;
        }
        if (!empty($user)) {
            return $this->try_social_register($user, 'fb');
        } else {
            return redirect(getLangUrl('/'));
        }
    }

    private function try_social_register($s_user, $network) {

        //dd($s_user);
        // return redirect( getLangUrl('register') )
        // ->withInput()
        // ->with('error-message', 'Due to the overwhelming surge in popularity, new registrations on Trusted Review Platform are currently disabled to allow for infrastructure & security upgrades. Thank you for your understanding!');

        $is_dentist = session('is_dentist');
        $is_clinic = session('is_clinic');
        if($s_user->getId()) {
            $user = User::where( 'fb_id','LIKE', $s_user->getId() )->withTrashed()->first();
        }
        if(empty($user) && $s_user->getEmail()) {
            $user = User::where( 'email','LIKE', $s_user->getEmail() )->withTrashed()->first();            
        }

        if ($s_user->getEmail() && !empty(User::where( 'email','LIKE', $s_user->getEmail() )->withTrashed()->first())) {
            return redirect( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']).'&error-message='.urlencode(trans('trp.popup.registration.existing-email', [
                'link' => '<a href="javascript:;" class="log-in-button button-login-patient">',
                'endlink' => '</a>',
            ])));
        }

        if ($user) {

            if($user->deleted_at || $user->isBanned('trp')) {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'banned-popup']));
            } else if($user->self_deleted) {
                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-login']))
                ->withInput()
                ->with('error-message', trans('trp.popup.login.error-fb.self-deleted') );
            } else if( $user->loggedFromBadIp() ) {

                $ul = new UserLogin;
                $ul->user_id = $user->id;
                $ul->ip = User::getRealIp();
                $ul->platform = 'trp';
                $ul->country = \GeoIP::getLocation()->country;

                $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                $dd = new DeviceDetector($userAgent);
                $dd->parse();

                if ($dd->isBot()) {
                    // handle bots,spiders,crawlers,...
                    $ul->device = $dd->getBot();
                } else {
                    $ul->device = $dd->getDeviceName();
                    $ul->brand = $dd->getBrandName();
                    $ul->model = $dd->getModel();
                    $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                }

                $action = new UserAction;
                $action->user_id = $user->id;
                $action->action = 'deleted';
                $action->reason = 'Automatically - Bad IP (from register form FB login - TELL GERGANA ABOUT THIS!! )';
                $action->actioned_at = Carbon::now();
                $action->save();

                $user->deleteActions();
                User::destroy( $user->id );

                return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                ->withInput();
            }
            
            Auth::login($user, true);
            if(empty($user->fb_id)) {
                $user->fb_id = $s_user->getId();
                $user->save();      
            }

            if(!empty(session('invitation_id'))) {
                $inv_id = session('invitation_id');
                $inv = UserInvite::find($inv_id);

                if(!empty($inv) && empty($inv->invited_id)) {

                    $inv->invited_id = $user->id;
                    if ($inv->invited_email == 'whatsapp') {
                        $inv->invited_email = $user->email;
                        $inv->invited_name = $user->name;
                    }
                    $inv->rewarded = true;
                    $inv->save();

                    $dentist_invitor = User::find($inv->user_id);

                    if (!empty($dentist_invitor)) {
                        Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                        return redirect($dentist_invitor->getLink().'?'. http_build_query(['popup'=>'submit-review-popup']));
                    }
                }

                Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                return redirect(getLangUrl('/'));

            } else {
                $want_to_invite = false;
                if(session('want_to_invite_dentist')) {
                    $want_to_invite = true;
                    session([
                        'want_to_invite_dentist' => null,
                    ]);
                }

                Request::session()->flash('success-message', trans('trp.popup.registration.have-account'));
                return redirect(getLangUrl('/').($want_to_invite ? '?'.http_build_query(['popup'=>'invite-new-dentist-popup']) : '' ));
            }

        } else {

            if (!empty($s_user->getEmail())) {

                $name = $s_user->getName() ? $s_user->getName() : ( !empty($s_user->getNickname()) ? $s_user->getNickname() : ( !empty($s_user->user['first_name']) && !empty($s_user->user['last_name']) ? $s_user->user['first_name'].' '.$s_user->user['last_name'] : ( !empty($s_user->getEmail()) ? explode('@', $s_user->getEmail() )[0] : 'User' ) ));


                $is_blocked = User::checkBlocks( $name , $s_user->getEmail() );
                if( $is_blocked ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']))
                    ->withInput()
                    ->with('error-message', $is_blocked );
                }

                if($s_user->getEmail() && (User::validateEmail($s_user->getEmail()) == true)) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']))
                    ->withInput()
                    ->with('error-message', nl2br(trans('trp.popup.login.existing_email')) );
                }


                $gender = !empty($s_user->user['gender']) ? ($s_user->user['gender']=='male' ? 'm' : 'f') : null;
                $birthyear = !empty($s_user->user['birthday']) ? explode('/', $s_user->user['birthday'])[2] : 0;

                if($birthyear && (intval(date('Y')) - $birthyear) < 18 ) {
                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']))
                    ->withInput()
                    ->with('error-message', nl2br(trans('trp.popup.login.over18')) );
                }

                $country_id = null;
                $state_name = null;
                $state_slug = null;
                $city_name = null;
                $lat = null;
                $lon = null;
                if (!empty($s_user->user['location']['name'])) {
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
                $newuser->gender = $gender;
                $newuser->birthyear = !empty($birthyear) ? $birthyear : '';
                $newuser->fb_id = $s_user->getId();
                $newuser->gdpr_privacy = true;
                $newuser->platform = 'trp';
                $newuser->status = 'approved';
                
                if(!empty(session('invited_by'))) {
                    $newuser->invited_by = session('invited_by');
                }
                
                $newuser->save();

                $newuser->slug = $newuser->makeSlug();
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

                if(!empty(session('invitation_by_patient'))) {
                    if($newuser->invited_by && $newuser->invitor->canInvite('trp') && !empty(session('invited_by'))) {
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

                        $inv->rewarded = true;
                        $inv->invited_id = $newuser->id;
                        $inv->created_at = Carbon::now();
                        $inv->save();

                        $reward = new DcnReward;
                        $reward->user_id = $newuser->invited_by;
                        $reward->reference_id = $newuser->id;
                        $reward->type = 'invitation';
                        $reward->platform = 'trp';
                        $reward->reward = Reward::getReward('reward_invite');

                        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                        $dd = new DeviceDetector($userAgent);
                        $dd->parse();

                        if ($dd->isBot()) {
                            // handle bots,spiders,crawlers,...
                            $reward->device = $dd->getBot();
                        } else {
                            $reward->device = $dd->getDeviceName();
                            $reward->brand = $dd->getBrandName();
                            $reward->model = $dd->getModel();
                            $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                        }

                        $reward->save();
                    }
                } else {
                    if($newuser->invited_by && $newuser->invitor->canInvite('trp') && !empty(session('invitation_id'))) {
                        $inv_id = session('invitation_id');
                        $inv = UserInvite::find($inv_id);

                        if ($inv && empty($inv->invited_id)) {
                            $inv->invited_id = $newuser->id;

                            if ($inv->invited_email == 'whatsapp') {
                                $inv->invited_email = $newuser->email;
                                $inv->invited_name = $newuser->name;
                            }
                            $inv->rewarded = true;
                            $inv->save();
                        }
                    }
                }

                $sess = [
                    'invited_by' => null,
                    'invitation_name' => null,
                    'invitation_email' => null,
                    'invitation_by_patient' => null,
                    'invitation_id' => null,
                    'just_registered' => true,
                ];
                session($sess);

                if( $newuser->loggedFromBadIp() ) {

                    $ul = new UserLogin;
                    $ul->user_id = $newuser->id;
                    $ul->ip = User::getRealIp();
                    $ul->platform = 'trp';
                    $ul->country = \GeoIP::getLocation()->country;

                    $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                    $dd = new DeviceDetector($userAgent);
                    $dd->parse();

                    if ($dd->isBot()) {
                        // handle bots,spiders,crawlers,...
                        $ul->device = $dd->getBot();
                    } else {
                        $ul->device = $dd->getDeviceName();
                        $ul->brand = $dd->getBrandName();
                        $ul->model = $dd->getModel();
                        $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                    }
                    
                    $ul->save();

                    $action = new UserAction;
                    $action->user_id = $newuser->id;
                    $action->action = 'deleted';
                    $action->reason = 'Automatically - Bad IP ( FB register )';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $newuser->deleteActions();
                    User::destroy( $newuser->id );

                    return redirect()->to( getLangUrl('/').'?'. http_build_query(['popup'=>'suspended-popup']))
                    ->withInput();
                }
                
                if( $newuser->email ) {
                    $newuser->sendGridTemplate( 4 );
                }

                Auth::login($newuser, true);

                $want_to_invite = false;
                if(session('want_to_invite_dentist')) {
                    $want_to_invite = true;
                    session([
                        'want_to_invite_dentist' => null,
                    ]);
                }

                return redirect( $newuser->invited_by && $newuser->invitor->is_dentist ? $newuser->invitor->getLink().'?'.http_build_query(['popup'=>'submit-review-popup']) : getLangUrl('/').($want_to_invite ? '?'.http_build_query(['popup'=>'invite-new-dentist-popup']) : '' ) );
            } else {
                return redirect( getLangUrl('/').'?'. http_build_query(['popup'=>'popup-register']).'&error-message='.urlencode(trans('trp.popup.login.no-fb-email')));
            }
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

                if(empty($email) || empty($phone)) {
                    $ret['weak'] = true;
                } else {

                    if( session('new_auth') ) {
                        $user = $this->user;

                        $duplicate = User::where('civic_id', $data['userId'] )->first();

                        if( $duplicate ) {
                            $ret['message'] = trans('trp.common.civic.duplicate');
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
                            if( $user->isBanned('trp')) {
                                $ret['popup'] = 'banned-popup';
                            } else if( $user->loggedFromBadIp() ) {

                                $ul = new UserLogin;
                                $ul->user_id = $user->id;
                                $ul->ip = User::getRealIp();
                                $ul->platform = 'trp';
                                $ul->country = \GeoIP::getLocation()->country;

                                $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                                $dd = new DeviceDetector($userAgent);
                                $dd->parse();

                                if ($dd->isBot()) {
                                    // handle bots,spiders,crawlers,...
                                    $ul->device = $dd->getBot();
                                } else {
                                    $ul->device = $dd->getDeviceName();
                                    $ul->brand = $dd->getBrandName();
                                    $ul->model = $dd->getModel();
                                    $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                                }
                                
                                $ul->save();

                                $action = new UserAction;
                                $action->user_id = $user->id;
                                $action->action = 'deleted';
                                $action->reason = 'Automatically - Bad IP ( Civic login )';
                                $action->actioned_at = Carbon::now();
                                $action->save();

                                $user->deleteActions();
                                User::destroy( $user->id );

                                $ret['popup'] = 'suspended-popup';
                            } else if($user->self_deleted) {
                                return Response::json( [
                                    'success' => false, 
                                    'message' => trans('popup.login.error-fb.self-deleted'),
                                ] );
                            } else {

                                $existing_phone = User::where('id', '!=', $user->id)->where('phone', 'LIKE', $phone)->first();

                                if ($existing_phone) {
                                    return Response::json( [
                                        'success' => false, 
                                        'message' => trans('trp.common.civic.duplicate-phone'),
                                    ] );
                                }

                                $sess = [
                                    'login_patient' => true,
                                ];
                                session($sess);
                                
                                Auth::login($user, true);
                                if(empty($user->civic_id)) {
                                    $user->civic_id = $data['userId'];
                                    $user->save();      
                                }

                                $ret['success'] = true;

                                if(!empty(session('invitation_id'))) {

                                    $inv_id = session('invitation_id');
                                    $inv = UserInvite::find($inv_id);

                                    if ($inv && empty($inv->invited_id)) {
                                        $inv->invited_id = $user->id;

                                        if ($inv->invited_email == 'whatsapp') {
                                            $inv->invited_email = $user->email;
                                            $inv->invited_name = $user->name;
                                        }
                                        $inv->rewarded = true;
                                        $inv->save();
                                    }
                                    $ret['redirect'] = getLangUrl('/');

                                    $dentist_invitor = User::find($inv->user_id);

                                    if (!empty($dentist_invitor)) {
                                        $ret['redirect'] = $dentist_invitor->getLink().'?'. http_build_query(['popup'=>'submit-review-popup']);
                                    } else {
                                        $ret['redirect'] = getLangUrl('/');
                                    }
                                } else {
                                    $want_to_invite = false;
                                    if(session('want_to_invite_dentist')) {
                                        $want_to_invite = true;
                                        session([
                                            'want_to_invite_dentist' => null,
                                        ]);
                                    }

                                    $ret['redirect'] = getLangUrl('/').($want_to_invite ? '?'.http_build_query(['popup'=>'invite-new-dentist-popup']) : '' );
                                }
                            }
                        } else {

                            $ret['message'] = trans('trp.common.civic.not-found');
                        }
                    }
                }

            } else {
                $ret['weak'] = true;
            }
        }
        
        return Response::json( $ret );
    }
    
    public function status() {
        return !empty($this->user) ? $this->user->convertForResponse() : null;
    }
}