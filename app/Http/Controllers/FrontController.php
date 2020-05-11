<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Models\UserGuidedTour;
use App\Models\WhitelistIp;
use App\Models\PollAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxAnswer;
use App\Models\VoxScale;
use App\Models\Category;
use App\Models\Country;
use App\Models\Reward;
use App\Models\User;
use App\Models\City;
use App\Models\Poll;
use App\Models\Vox;

use Carbon\Carbon;

use Redirect;
use Session;
use Request;
use Cookie;
use Route;
use Auth;
use App;
use DB;

class FrontController extends BaseController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $request;
    public $current_page;
    public $user;

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        $to_redirect_404 = false;

        $roter_params = $request->route()->parameters();
        if(empty($roter_params['locale'])) { // || $roter_params['locale']=='_debugbar'
            $locale = 'en';
        } else {
            if(!empty( config('langs.'.$roter_params['locale']) )) {
                $locale = $roter_params['locale'];
            } else {
                $locale = 'en';

                $to_redirect_404 = true;
            }
        }

        App::setLocale( $locale );

        date_default_timezone_set("Europe/Sofia");


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

        if (!empty($to_redirect_404)) {
            Redirect::to(getLangUrl('page-not-found'))->send();
        }
        // Fck FB
        if( Request::getHost() == 'vox.dentacoin.com' && Request::server('HTTP_REFERER') && Request::isMethod('get') && request()->url() != 'https://vox.dentacoin.com/en/registration' && request()->url() != 'https://vox.dentacoin.com/en/login') {
            Redirect::to( str_replace('vox.', 'dentavox.', Request::url() ) )->send();
        }
        
        //VPNs
        // $myips = session('my-ips');
       
        // if( !isset( $myips[User::getRealIp()] ) ) {
        //     if(!is_array($myips)) {
        //         $myips = [];
        //     }
        //     $myips[User::getRealIp()] = User::checkForBlockedIP();
        //     session(['my-ips' => $myips]);
        // }
        // if($myips[User::getRealIp()] && $this->current_page!='vpn' ) {
        //     Redirect::to( getLangUrl('vpn') )->send();
        // }
        // if( !$myips[User::getRealIp()] && $this->current_page=='vpn' ) {
        //     Redirect::to( getLangUrl('/') )->send();
        // }

        $this->trackEvents = [];

        //$this->user = Auth::guard('web')->user();
        $this->middleware(function ($request, $next) {


            $this->admin = Auth::guard('admin')->user();
            $this->user = Auth::guard('web')->user();

            if (!empty($this->user) && !$this->user->is_dentist && session('intended') && !$this->user->isBanned('vox')) {
                $intended = session()->pull('intended');
                
                if( 'https://'.Request::getHost().'/'.request()->path() != trim($intended, '/') ) {
                    Redirect::to($intended)->send();
                } else {
                    session([
                        'intended' => null
                    ]);
                }
            }

            if(!empty($this->user) && Cookie::get('daily_poll')){
                $cv = json_decode(Cookie::get('daily_poll'), true);
                $given_reward = false;

                foreach ($cv as $pid => $aid) {
                    $taken_reward = DcnReward::where('user_id', $this->user->id)->where('reference_id', $pid)->where('platform', 'vox')->where('type', 'daily_poll')->first();

                    if (empty($taken_reward)) {
                        $given_reward = true;

                        $reward = new DcnReward;
                        $reward->user_id = $this->user->id;
                        $reward->reference_id = $pid;
                        $reward->platform = 'vox';
                        $reward->type = 'daily_poll';
                        $reward->reward = Reward::getReward('daily_polls');

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

                        PollAnswer::where('id', $aid)->update([
                            'user_id' => $this->user->id
                        ]);
                    }
                }

                Cookie::queue(Cookie::forget('daily_poll'));

                if ($given_reward) {
                    return redirect(getLangUrl('/').'?daily-answer');
                }
                
            }

            if(!empty($this->user) && session('login-logged')!=$this->user->id){
                $ul = new UserLogin;
                $ul->user_id = $this->user->id;
                $ul->ip = User::getRealIp();
                $ul->platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
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

                $is_whitelist_ip = WhitelistIp::where('ip', 'like', User::getRealIp())->first();
                if (User::getRealIp() != '213.91.254.194' && empty($this->admin) && empty($is_whitelist_ip)) {
                    $ul->save();
                }

                if($this->user->is_dentist) {
                    $gt_exist = UserGuidedTour::where('user_id', $this->user->id)->first();

                    if(!empty($gt_exist)) {

                        if(empty($gt_exist->first_login_trp) && mb_strpos( Request::getHost(), 'vox' )===false) {
                            $gt_exist->first_login_trp = true;
                            $gt_exist->save();
                        }

                    } else {

                        $gt = new UserGuidedTour;
                        $gt->user_id = $this->user->id;

                        if(mb_strpos( Request::getHost(), 'vox' )===false) {
                            $gt->first_login_trp = true;
                        }

                        $gt->save();
                    }
                }


                $tokenobj = $this->user->createToken('LoginToken');
                $tokenobj->token->platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
                $tokenobj->token->save();

                session([
                    'login-logged' => $this->user->id,
                    'mark-login' => mb_strpos( Request::getHost(), 'vox' )!==false ? 'DV' : 'TRP',
                    'logged_user' => [
                        'token' => $tokenobj->accessToken,
                        'id' => $this->user->id,
                        'type' => $this->user->is_dentist ? 'dentist' : 'patient',
                    ],
                ]);

                if( !$this->user->isBanned('vox') && !$this->user->isBanned('trp') && $this->user->bans->isNotEmpty() ) {
                    $last = $this->user->bans->last();
                    if(!$last->notified) {
                        $last->notified = true;
                        $last->save();
                        session(['unbanned' => true]);
                    }
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

            if( Cookie::get('first-login-recommendation') ) {
                Cookie::queue(Cookie::forget('first-login-recommendation'));
            }

            if(!empty($this->user) && count($this->user->filledVoxes()) >= 5 && empty($this->user->first_login_recommendation) && !empty(Cookie::get('marketing_cookies')) && empty(Cookie::get('first-login-recommendation')) && (Request::getHost() == 'dentavox.dentacoin.com' || Request::getHost() == 'urgent.dentavox.dentacoin.com' )) {
                Cookie::queue('first-login-recommendation', true, 1440, null, null, false, false);
                $this->user->first_login_recommendation = true;
                $this->user->save();
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

            $response = $next($request);
            $response->headers->set('Referrer-Policy', 'no-referrer');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
     
            return $response;

            //return $next($request);
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

    public function ShowVoxView($page, $params=array(), $statusCode=null) {

        if( session('login-logged') && $this->user && !Cookie::get('prev-login') && !empty(Cookie::get('functionality_cookies')) ) {
            Cookie::queue('prev-login', $this->user->id, 60*24*31);
        }

        if( $this->current_page=='welcome-survey' ) {
            if( Cookie::get('prev-login') ) {
                $params['prev_user'] = User::find( Cookie::get('prev-login') );
            }

            if(empty( $params['prev_user'] ) && !empty(Cookie::get('functionality_cookies'))) {
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

        if( session('unbanned') ) {
            session(['unbanned' => null]);
            $params['unbanned'] = true;
            $params['unbanned_times'] = $this->user->getPrevBansCount('vox');
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

        ///Daily Polls

        $daily_poll = Poll::with('translations')->where('launched_at', date('Y-m-d') )->where('status', 'open')->first();

        if(!empty($daily_poll)) {
            
            $restrictions = false;

            if(!empty($this->user) && !empty($this->user->country_id)) {
                $restrictions = $daily_poll->isPollRestricted($this->user->country_id);
            } else {

                $country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
                $country_db = Country::where('code', 'like', $country_code)->first();

                if (!empty($country_db)) {
                    $restrictions =  $daily_poll->isPollRestricted($country_db->id);
                }
            }

            if($restrictions) {
                $daily_poll = null;
            }
        }

        if (!empty($daily_poll)) {
            $params['daily_poll'] = $daily_poll;

            if(!empty($this->user)) {
                $taken_daily_poll = PollAnswer::where('poll_id', $daily_poll->id)->where('user_id', $this->user->id)->first();

                if ($taken_daily_poll) {
                    $params['taken_daily_poll'] = true;
                }
            }
        }

        $closed_daily_poll = Poll::with('translations')->where('launched_at', date('Y-m-d') )->where('status', 'closed')->first();

        if (!empty($closed_daily_poll)) {
            $params['closed_daily_poll'] = $closed_daily_poll;
        }

        $params['daily_poll_reward'] = Reward::getReward('daily_polls');

        if (Cookie::get('daily_poll')) {
            $params['session_polls'] = true;
        }

        $slist = VoxScale::get();
        $poll_scales = [];
        foreach ($slist as $sitem) {
            $poll_scales[$sitem->id] = $sitem;
        }

        $params['poll_scales'] = $poll_scales;

        if (!empty($statusCode)) {
            return response()->view('vox.'.$page, $params, $statusCode);
        } else {
            return view('vox.'.$page, $params);
        }
        
    }

    public function ShowView($page, $params=array(), $statusCode=null) {

        $this->PrepareViewData($page, $params, 'trp');    

        if (empty($this->user)) {
            $params['hours'] = [];
            for($i=0;$i<=23;$i++) {
                $h = str_pad($i, 2, "0", STR_PAD_LEFT);
                $params['hours'][$h] = $h;
            }

            $params['minutes'] = [
                '00' => '00',
                '10' => '10',
                '20' => '20',
                '30' => '30',
                '40' => '40',
                '50' => '50',
            ];
        }
        
        if (!empty($statusCode)) {
            return response()->view('trp.'.$page, $params, $statusCode);
        } else {
            return view('trp.'.$page, $params);
        }
        
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

        $params['seo_title'] = !empty($params['seo_title']) ? $params['seo_title'] : trans($text_domain.'.seo.'.$this->current_page.'.title');
        $params['seo_description'] = !empty($params['seo_description']) ? $params['seo_description'] : trans($text_domain.'.seo.'.$this->current_page.'.description');

        $params['social_title'] = !empty($params['social_title']) ? $params['social_title'] : trans($text_domain.'.social.'.$this->current_page.'.title');
        $params['social_description'] = !empty($params['social_description']) ? $params['social_description'] : trans($text_domain.'.social.'.$this->current_page.'.description');

        $params['canonical'] = !empty($params['canonical']) ? $params['canonical'] : getLangUrl($this->current_page);
        $params['social_image'] = !empty($params['social_image']) ? $params['social_image'] : url( $text_domain=='trp' ? '/img-trp/socials-cover.jpg' : '/img-vox/logo-text.png'  );
        //dd($params['pages_header']);

        //
        //Global
        //
        $platfrom = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';

        $params['trackEvents'] = [];
        if( session('mark-login') && empty($params['skipSSO']) ) {
            $ep = session('mark-login');
            session([
                'mark-login' => false
            ]);

            if (!empty($this->user)) {
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
            }

            $params['markLogin'] = true;
        }
        if( session('login-logged-out') && empty($params['skipSSO']) ) {
            $params['markLogout'] = session('login-logged-out');
            session([
                'login-logged-out' => false
            ]);
        }

        $params['cache_version'] = '2020-05-07-02';
    }
}