<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Models\UserGuidedTour;
use App\Models\PollAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxAnswer;
use App\Models\VoxScale;
use App\Models\Country;
use App\Models\Reward;
use App\Models\User;
use App\Models\City;
use App\Models\Poll;

use Carbon\Carbon;

use Redirect;
use Request;
use Cookie;
use Route;
use Auth;
use App;

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
            if(!empty( config('langs.'.$roter_params['locale']) ) ) {
                if(Request::getHost() == 'reviews.dentacoin.com' || Request::getHost() == 'urgent.reviews.dentacoin.com') {

                    $locale = $roter_params['locale'];
                } else {
                    $locale = 'en';
                }
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

            if(!empty($this->user) && !empty($this->user->is_logout)) {
                $this->user->logoutActions();
                $this->user->is_logout = null;
                $this->user->save();

                Auth::guard('web')->logout();
                return redirect(getLangUrl('/'));
            }

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

            if(!empty($this->user) && session('invite_new_dentist')) {
                $new_dentist = User::find(session('invite_new_dentist'));

                if(!empty($new_dentist) && $new_dentist->is_dentist && $new_dentist->invited_by === 0) {
                    $new_dentist->invited_by = $this->user->id;
                    $new_dentist->save();

                    if($new_dentist->status == 'added_approved' || $new_dentist->status == 'approved') {
                        $amount = Reward::getReward('patient_add_dentist');
                        $reward = new DcnReward();
                        $reward->user_id = $this->user->id;
                        $reward->reward = $amount;
                        $reward->platform = 'trp';
                        $reward->type = 'added_dentist';
                        $reward->reference_id = $new_dentist->id;

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

                        $substitutions = [
                            'added_dentist_name' => $new_dentist->getNames(),
                            'trp_added_dentist_prf' => $new_dentist->getLink().'?dcn-gateway-type=patient-login',
                        ];

                        $this->user->sendGridTemplate(65, $substitutions, 'trp');
                    }
                }

                session()->pull('invite_new_dentist');
            }

            if(!empty($this->user) && session('login-logged')!=$this->user->id) {

                //after login actions

                if($this->user->is_dentist) {
                    $gt_exist = UserGuidedTour::where('user_id', $this->user->id)->first();

                    if(!empty($gt_exist)) {

                        if(empty($gt_exist->first_login_trp) && mb_strpos( Request::getHost(), 'vox' )===false) {
                            $gt_exist->first_login_trp = true;
                            $gt_exist->save();

                            session(['first_guided_tour' => true]);

                        } else if(empty($gt_exist->login_after_first_review) && mb_strpos( Request::getHost(), 'vox' )===false && empty(session('first_guided_tour')) && empty($this->user->widget_activated) && $this->user->dentist_fb_page->isEmpty() && $this->user->reviews_in_standard()->count() == 1 ) {

                            $date = null;

                            foreach ($this->user->reviews_in_standard() as $review) {
                                $date = $review->created_at;
                            }

                            $last_login = UserLogin::where('user_id', $this->user->id)->where('created_at', '<=', Carbon::now()->addMinutes(-10))->where('created_at', '>', $date)->first();

                            if(empty($last_login)) {
                                $gt_exist->login_after_first_review = true;
                                $gt_exist->save();
                                
                                session(['reviews_guided_tour' => true]);
                            }
                        }

                    } else {

                        $gt = new UserGuidedTour;
                        $gt->user_id = $this->user->id;

                        if(mb_strpos( Request::getHost(), 'vox' )===false) {
                            $gt->first_login_trp = true;
                            
                            session(['first_guided_tour' => true]);
                        }

                        $gt->save();
                    }

                }


                if(!session('login-logged')) {

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
                }

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

            if(!empty($this->user) && !empty(Cookie::get('marketing_cookies')) && empty(Cookie::get('first-login-recommendation')) && (Request::getHost() == 'dentavox.dentacoin.com' || Request::getHost() == 'urgent.dentavox.dentacoin.com' ) && empty($this->user->first_login_recommendation) && $this->user->filledVoxesCount() >= 5) {
                Cookie::queue('first-login-recommendation', true, 1440, null, null, false, false);
                $this->user->first_login_recommendation = true;
                $this->user->save();
            }

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
            //$response->headers->set('X-Frame-Options', 'DENY');
     
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
            if(!empty($this->user)) {
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
        }

        // Fck FB
        if( Request::getHost() == 'vox.dentacoin.com' ) {
            $params['noindex'] = true;
        }



        if(isset($_SERVER['HTTP_USER_AGENT'])) {

            $useragent=$_SERVER['HTTP_USER_AGENT'];

            if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
                $params['phone'] = true;
            } else {
                $params['phone'] = false;
            }
        } else {
            $params['phone'] = false;
        }

        ///Daily Polls

        $daily_polls = Poll::with('translations')->where('launched_at', date('Y-m-d') )->whereIn('status', ['open', 'closed'])->get();

        if(!empty($daily_polls)) {
            
            $daily_poll = null;

            foreach($daily_polls as $dp) {
                if($dp->status == 'open') {
                    $daily_poll = $dp;
                } else {
                    $params['closed_daily_poll'] = $dp;
                }
            }

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
                
                $slist = VoxScale::get();
                $poll_scales = [];
                foreach ($slist as $sitem) {
                    $poll_scales[$sitem->id] = $sitem;
                }

                $params['poll_scales'] = $poll_scales;

                if(!empty($this->user)) {
                    $taken_daily_poll = PollAnswer::where('poll_id', $daily_poll->id)->where('user_id', $this->user->id)->first();

                    if ($taken_daily_poll) {
                        $params['taken_daily_poll'] = true;
                    }
                }
            }
        }

        $params['daily_poll_reward'] = Reward::getReward('daily_polls');

        if (Cookie::get('daily_poll')) {
            $params['session_polls'] = true;
        }

        $params['dark_mode'] = !empty($this->user) && $this->user->dark_mode ? true : false;

        if(!empty($this->user) && !Request::isMethod('post')) {
            $params['user_total_balance'] = $this->user->getTotalBalance();
        } else {
            $params['user_total_balance'] = 0;
        }

        if(!isset($params['xframe'])) {
            return response()->view('vox.'.$page, $params, $statusCode ? $statusCode : 200)->header('X-Frame-Options', 'DENY');
        } else {
            return response()->view('vox.'.$page, $params, $statusCode ? $statusCode : 200);
        }
        
    }

    public function ShowView($page, $params=array(), $statusCode=null) {
        
        $this->PrepareViewData($page, $params, 'trp');

        $params['clinicBranches'] = false;
        $params['has_review_notification'] = false;
        if(!empty($this->user) && $this->user->is_clinic && $this->user->branches->isNotEmpty()) {
            $params['clinicBranches'] = [];
            foreach($this->user->branches as $branch) {

                $branchClinic = $branch->branchClinic;

                $params['clinicBranches'][$branchClinic->id] = [
                    'name' => $branchClinic->getNames(),
                    'avatar' => $branchClinic->getImageUrl(true),
                    'notification' => $branchClinic->review_notification ? true : false,
                ];
            }
            foreach($this->user->branches as $branch) {

                $branchClinic = $branch->branchClinic;

                $has_ask_notification = false;
                if ($branchClinic->asks->isNotEmpty()) {
                    foreach ($branchClinic->asks as $p_ask) {
                        if ($p_ask->status == 'waiting') {
                            $has_ask_notification = true;
                        }
                    }
                }

                if($branchClinic->review_notification || $has_ask_notification) {
                    //user asks
                    $params['has_review_notification'] = true;
                    break;
                }
            }
        }

        if($params['clinicBranches']) {
            $params['clinicBranches'] = json_encode($params['clinicBranches']);
        }

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

        if(!isset($params['xframe'])) {
            return response()->view('trp.'.$page, $params, $statusCode ? $statusCode : 200)->header('X-Frame-Options', 'DENY');
        } else {
            return response()->view('trp.'.$page, $params, $statusCode ? $statusCode : 200);
        }
    }

    public function PrepareViewData($page, &$params, $text_domain) {

        $params['dcn_price'] = @file_get_contents('/tmp/dcn_price');
        $params['dcn_original_price'] = @file_get_contents('/tmp/dcn_original_price');
        $params['dcn_change'] = @file_get_contents('/tmp/dcn_change');
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
            //dd(session('mark-login'));
            $ep = session('mark-login');
            session([
                'mark-login' => false
            ]);

            $params['markLogin'] = true;
        }
        if( session('login-logged-out') && empty($params['skipSSO']) ) {
            //dd(session('login-logged-out'));
            $params['markLogout'] = session('login-logged-out');
            session([
                'login-logged-out' => false
            ]);
        }

        $params['cache_version'] = '20210811';
    }
}