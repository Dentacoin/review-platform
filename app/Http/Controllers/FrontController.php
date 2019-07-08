<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App;
use Auth;
use Session;
use DB;
use Request;
use Route;
use Cookie;
use Redirect;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Page;
use App\Models\Category;
use App\Models\Country;
use App\Models\City;
use App\Models\VoxAnswer;
use App\Models\Vox;
use App\Models\UserLogin;

class FrontController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $request;
    public $current_page;
    public $user;

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        $roter_params = $request->route()->parameters();
        if(empty($roter_params['locale'])) {
            $locale = 'en';
        } else {
            if(!empty( config('langs.'.$roter_params['locale']) )) {
                $locale = $roter_params['locale'];
            } else {
                abort(404);
            }
        }
        App::setLocale( $locale );

        $this->request = $request;
        $path = explode('/', Request::path());
        $this->current_page = isset($path[1]) ? $path[1] : null;
        if(empty($this->current_page)) {
            $this->current_page='index';
        }
        
        $this->current_subpage = isset($path[2]) ? $path[2] : null;
        if(empty($this->current_subpage)) {
            $this->current_subpage='home';
        }

        // Fck FB
        if( Request::getHost() == 'vox.dentacoin.com' && Request::server('HTTP_REFERER') && Request::isMethod('get') && request()->url() != 'https://vox.dentacoin.com/en/registration' && request()->url() != 'https://vox.dentacoin.com/en/login') {
            Redirect::to( str_replace('vox.', 'dentavox.', Request::url() ) )->send();
        }
        
        //VPNs
        $myips = session('my-ips');
       
        if( !isset( $myips[User::getRealIp()] ) ) {
            if(!is_array($myips)) {
                $myips = [];
            }
            $myips[User::getRealIp()] = User::checkForBlockedIP();
            session(['my-ips' => $myips]);
        }
        if($myips[User::getRealIp()] && $this->current_page!='vpn' ) {
            Redirect::to( getLangUrl('vpn') )->send();
        }
        if( !$myips[User::getRealIp()] && $this->current_page=='vpn' ) {
            Redirect::to( getLangUrl('/') )->send();
        }

        $this->trackEvents = [];


        //$this->user = Auth::guard('web')->user();
        $this->middleware(function ($request, $next) {
            $this->admin = Auth::guard('admin')->user();
            $this->user = Auth::guard('web')->user();

            if($this->user && session('login-logged')!=$this->user->id){
                $ul = new UserLogin;
                $ul->user_id = $this->user->id;
                $ul->ip = User::getRealIp();
                $ul->platform = mb_strpos( Request::getHost(), 'dentavox' )!==false ? 'vox' : 'trp';

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
                    $ul->os = $dd->getOs()['name'];
                }
                
                if (User::getRealIp() != '213.91.254.194') {
                    $ul->save();
                }

                $tokenobj = $this->user->createToken('LoginToken');
                $tokenobj->token->platform = mb_strpos( Request::getHost(), 'dentavox' )!==false ? 'vox' : 'trp';
                $tokenobj->token->save();

                session([
                    'login-logged' => $this->user->id,
                    'mark-login' => mb_strpos( Request::getHost(), 'dentavox' )!==false ? 'DV' : 'TRP',
                    'logged_user' => [
                        'token' => $tokenobj->accessToken,
                        'id' => $this->user->id,
                        'type' => $this->user->is_dentist ? 'dentist' : 'patient',
                    ]
                ]);

                if( !$this->user->isBanned('vox') && $this->user->bans->isNotEmpty() ) {
                    $last = $this->user->bans->last();
                    if(!$last->notified) {
                        $last->notified = true;
                        $last->save();
                        session(['unbanned' => true]);
                    }
                }

                if( !$this->user->fb_id && !$this->user->civic_id && !$this->user->is_dentist ) {
                    session(['new_auth' => true]);
                } else {
                    session(['new_auth' => null]);
                }
            }


            $this->country_id = !empty($this->user->country_id) ? $this->user->country_id : session('country_id');
            $this->city_id = !empty($this->user->city_id) ? $this->user->city_id : session('city_id');

            if(!$this->country_id || !$this->city_id) {
                $location = \GeoIP::getLocation();
                if(!empty($location)) {
                    if(empty($this->country_id) && !empty($location['iso_code'])) {
                        $c = Country::where('code', 'LIKE', $location['iso_code'])->first();
                        if(!empty($c)) {
                            $this->country_id = $c->id;
                            session(['country_id' => $c->id]);
                        }
                    }

                    if(empty($this->city_id) && !empty($this->country_id) && !empty($location['city'])) {
                        $city_name = $location['city'];
                        $c = City::where('country_id', $this->country_id)->whereHas('translations', function ($query) use ($city_name) {
                            $query->where('locale', 'en')
                            ->where('name', 'LIKE', $city_name);
                        })->first();
                        if(!empty($c)) {
                            $this->city_id = $c->id;
                            session(['city_id' => $c->id]);
                        }

                    }
                }                
            }


            // if($this->user) {
            //     $details_vox = Vox::where('type', 'user_details')->first();
            //     if( !empty($this->admin) && !empty($details_vox) && !$this->user->madeTest(  $details_vox->id ) && $request->fullUrl() != getLangUrl('questionnaire/'.$details_vox->id) ) {
            //         $this->welcome_test = getLangUrl('questionnaire/'.$details_vox->id);
            //     }
            // }


            $request->attributes->add([
                'admin' => $this->admin,
                'user' => $this->user,
                'country_id' => $this->country_id,
                'city_id' => $this->city_id,
            ]);

            // if(mb_strpos(Request::url(), '//dev.') && !$this->admin) {
            //     echo '<a href="'.str_replace('//dev.', '//', Request::url()).'">Click here</a> or <a href="'.url('cms').'"> log in as admin </a>';
            //     exit;
            // }

            return $next($request);
        });

        $this->categories = [];
        $clist = config('categories');
        foreach ($clist as $cat) {
            $this->categories[$cat] = trans('trp.categories.'.$cat);
        }

        $this->categories_dentists = [];
        $clist = config('categories');
        foreach ($clist as $cat) {
            $this->categories_dentists[$cat] = trans('trp.categories-dentists.'.$cat);
        }

    }

    public function ShowVoxView($page, $params=array()) {

        if( session('login-logged') && $this->user && !Cookie::get('prev-login') ) {
            Cookie::queue('prev-login', $this->user->id, 60*24*31);
        }

        if( $this->current_page=='welcome-survey' ) {
            if( Cookie::get('prev-login') ) {
                $params['prev_user'] = User::find( Cookie::get('prev-login') );
            }

            if(empty( $params['prev_user'] )) {
                $uid = User::lastLoginUserId();
                if($uid) {
                    $params['prev_user'] = User::find( $uid->user_id );
                }
            }
        }

        $this->PrepareViewData($page, $params, 'vox');

        $params['genders'] = [
            'm' => trans('vox.common.gender.m'),
            'f' => trans('vox.common.gender.f'),
        ];
        $params['years'] = range( date('Y'), date('Y')-90 );
        $params['header_questions'] = VoxAnswer::getCount();
        $params['users_count'] = User::getCount('vox');
        //dd($params['header_questions']);


        $params['show_tutorial'] = false;
        // if($this->user) {
        //     if(empty($_COOKIE['show_tutorial3'])) {
        //         $params['show_tutorial'] = true;
        //         setcookie('show_tutorial3', time(), time()+86400*7);
        //     }
        // }

        if( session('unbanned') ) {
            session(['unbanned' => null]);
            $params['unbanned'] = true;
            $params['unbanned_times'] = $this->user->getPrevBansCount();
            if( $params['unbanned_times']==1 ) {
                $params['unbanned_text'] = nl2br(trans('vox.page.bans.unbanned-text-1'));
            } else if( $params['unbanned_times']==2 ) {
                $params['unbanned_text'] = nl2br(trans('vox.page.bans.unbanned-text-2'));
            } else {
                $params['unbanned_text'] = nl2br(trans('vox.page.bans.unbanned-text-3'));
            }
        }

        // Fck FB
        if( Request::getHost() == 'vox.dentacoin.com' ) {
            $params['noindex'] = true;
        }

        return view('vox.'.$page, $params);
    }

    public function ShowView($page, $params=array()) {

        $this->PrepareViewData($page, $params, 'trp');

        if( empty( $this->user ) ) {
            $params['countries'] = Country::get();
            
            if(is_array($params['jscdn'])) {
                if( array_search('https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en', $params['jscdn'])===false ) {
                    $params['jscdn'][] = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en';
                }
            } else {
                $params['jscdn'] = [
                    'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en',
                ];
            }
        }
        
        return view('trp.'.$page, $params);
    }    
    public function PrepareViewData($page, &$params, $text_domain) {

        $params['dcn_price'] = file_get_contents('/tmp/dcn_price');
        $params['dcn_change'] = file_get_contents('/tmp/dcn_change');
        $params['welcome_test'] = !empty($this->welcome_test) ? $this->welcome_test : null;
        $params['country_id'] = $this->country_id;
        $params['city_id'] = $this->city_id;
        $params['categories'] = $this->categories;
        $params['current_page'] = $this->current_page;
        $params['current_subpage'] = $this->current_subpage;
        $params['request'] = $this->request;
        $params['admin'] = $this->admin;
        $params['user'] = $this->user;
        $params['is_ajax'] = !empty($params['is_ajax']) ? $params['is_ajax'] : false;

        if($this->user && !$this->user->phone_verified) {
            $countries = Country::get();
            $params['phone_codes'] = [];
            foreach ($countries as $country) {
                $params['phone_codes'][$country->id] = mb_strtoupper($country->code).' ('.$country->phone_code.')';
            }
        }

        $params['pages_header'] = Page::translatedIn(App::getLocale())->where('header','>',0)->orderBy('header', 'asc')->get();
        $params['pages_footer'] = Page::translatedIn(App::getLocale())->where('footer','>',0)->orderBy('footer', 'asc')->get();

        $params['seo_title'] = !empty($params['seo_title']) ? $params['seo_title'] : trans($text_domain.'.seo.'.$this->current_page.'.title');
        $params['seo_description'] = !empty($params['seo_description']) ? $params['seo_description'] : trans($text_domain.'.seo.'.$this->current_page.'.description');

        $params['social_title'] = !empty($params['social_title']) ? $params['social_title'] : trans($text_domain.'.social.'.$this->current_page.'.title');
        $params['social_description'] = !empty($params['social_description']) ? $params['social_description'] : trans($text_domain.'.social.'.$this->current_page.'.description');

        $params['canonical'] = !empty($params['canonical']) ? $params['canonical'] : getLangUrl($this->current_page);
        $params['social_image'] = !empty($params['social_image']) ? $params['social_image'] : url( $text_domain=='trp' ? '/img-trp/socials-cover.jpg' : '/img-vox/logo-text.png'  );
        //dd($params['pages_header']);

        if( $text_domain=='vox' && !empty($this->user) && !$this->user->vox_active) {
            $this->user->vox_active = true;
            $this->user->save();
        }

        //
        //Global
        //
        $platfrom = mb_strpos( Request::getHost(), 'dentavox' )!==false ? 'vox' : 'trp';

        $params['trackEvents'] = [];
        if( session('mark-login') && empty($params['skipSSO']) ) {
            $ep = session('mark-login');
            session([
                'mark-login' => false
            ]);

            if( $this->user->is_dentist ) {
                $params['trackEvents'][] = [
                    'fb' => $ep.'DentistLoginSaved',
                    'ga_category' => 'DentistLogin',
                    'ga_action' => 'NoButton',
                    'ga_label' => 'DentistLoginSaved',
                ];

            } else {
                $params['trackEvents'][] = [
                    'fb' => $ep.'PatientLoginSaved',
                    'ga_category' => 'PatientLogin',
                    'ga_action' => 'NoButton',
                    'ga_label' => 'PatientLoginSaved',
                ];
            }

            $params['markLogin'] = true;
        }
        if( session('login-logged-out') && empty($params['skipSSO']) ) {
            $params['markLogout'] = session('login-logged-out');
            session([
                'login-logged-out' => false
            ]);
        }


        if( session('login_patient') ) {
            session([
                'login_patient' => false
            ]);

            if( $platfrom=='trp' ) {
                $params['trackEvents'][] = [
                    'fb' => 'PatientLoginSuccess',
                    'ga_action' => 'ClickLogin',
                    'ga_category' => 'PatientLogin',
                    'ga_label' => 'LoginSuccess',
                ];
            } else {
                $params['trackEvents'][] = [
                    'fb' => 'DVPatientLogin',
                    'ga_action' => 'ClickLogin',
                    'ga_category' => 'PatientLogin',
                    'ga_label' => 'PatientLoginSuccess',
                ];

            }
        }


        if( session('just_login') ) {
            session([
                'just_login' => false
            ]);

            if( $this->user->is_dentist ) {
                if( $platfrom=='trp' ) {
                    $params['trackEvents'][] = [
                        'fb' => 'DentistLogin',
                        'ga_action' => 'ClickLogin',
                        'ga_category' => 'DentistLogin',
                        'ga_label' => 'DentistLogin',
                    ];
                } else {
                    $params['trackEvents'][] = [
                        'fb' => 'DVDentistLogin',
                        'ga_action' => 'ClickLogin',
                        'ga_category' => 'DentistLogin',
                        'ga_label' => 'DentistLoginSuccess',
                    ];

                }

            }
        }

        //
        //TRP
        //

        if( session('just_registered') ) {
            session([
                'just_registered' => false
            ]);

            $civic_registered = false;
            if( session('civic_registered') ) {
                $civic_registered = true;
                session([
                    'civic_registered' => false
                ]);
            }


            if( !$this->user->is_dentist ) {
                if( $civic_registered) {
                    $params['trackEvents'][] = [
                        'fb' => 'CompleteRegistrationCivic',
                        'ga_action' => 'ClickCivic',
                        'ga_category' => 'PatientRegistration',
                        'ga_label' => 'TRPCivicPatientRegistration',
                    ];
                } else {
                    $params['trackEvents'][] = [
                        'fb' => 'CompleteRegistrationFB',
                        'ga_action' => 'ClickFB',
                        'ga_category' => 'PatientRegistration',
                        'ga_label' => 'FBPatientRegistration',
                    ];

                }
            }

        }


        //
        //Vox
        //

        if( session('just_registered_patient_vox') ) {
            session([
                'just_registered_patient_vox' => false
            ]);
            $params['trackEvents'][] = [
                'fb' => 'DVPatientRegistration',
                'ga_action' => 'ClickContinue',
                'ga_category' => 'PatientRegistration',
                'ga_label' => 'PatientRegistrationComplete',
            ];
        }


        if( session('just_registered_dentist_vox') ) {
            session([
                'just_registered_dentist_vox' => false
            ]);
            $params['trackEvents'][] = [
                'fb' => 'DVDentistRegistrationStep1',
                'ga_action' => 'ClickSubmit',
                'ga_category' => 'DentistRegistration',
                'ga_label' => 'DentistRegistrationStep1',
            ];
        }

        if( session('success_registered_dentist_vox') ) {
            session([
                'success_registered_dentist_vox' => false
            ]);
            $params['trackEvents'][] = [
                'fb' => 'DVDentistRegistrationComplete',
                'ga_action' => 'ClickSubmit',
                'ga_category' => 'DentistRegistration',
                'ga_label' => 'DentistRegistrationComplete',
            ];
        }





        $params['new_auth'] = false;
        if( session('new_auth') && !empty($this->user) && empty($this->user->fb_id) && empty($this->user->civic_id) ) {
            $params['new_auth'] = true;
            if(is_array($params['js'])) {
                $params['js'][] = 'login.js';
            } else {
                $params['js'] = ['login.js'];
            }


            if(is_array($params['jscdn'])) {
                $params['jscdn'][] = 'https://hosted-sip.civic.com/js/civic.sip.min.js';
            } else {
                $params['jscdn'] = [
                    'https://hosted-sip.civic.com/js/civic.sip.min.js',
                ];
            }

            if(is_array($params['csscdn'])) {
                $params['csscdn'][] = 'https://hosted-sip.civic.com/css/civic-modal.min.css';
            } else {
                $params['csscdn'] = [
                    'https://hosted-sip.civic.com/css/civic-modal.min.css',
                ];
            }
        }

        $params['cache_version'] = '2019-07-08';
    }
}