<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
                $locale = 'en';                
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

        
        //VPNs
        $myips = session('my-ips');
       
        if( !isset( $myips[Request::ip()] ) ) {
            if(!is_array($myips)) {
                $myips = [];
            }
            $myips[Request::ip()] = User::checkForBlockedIP();
            session(['my-ips' => $myips]);
        }
        if( $myips[Request::ip()] && $this->current_page!='vpn' ) {
            Redirect::to( getLangUrl('vpn') )->send();
        }
        if( !$myips[Request::ip()] && $this->current_page=='vpn' ) {
            Redirect::to( getLangUrl('/') )->send();
        }





        //$this->user = Auth::guard('web')->user();
        $this->middleware(function ($request, $next) {
            $this->admin = Auth::guard('admin')->user();
            $this->user = Auth::guard('web')->user();

            if($this->user && !session('login-logged')){
                $ul = new UserLogin;
                $ul->user_id = $this->user->id;
                $ul->ip = Request::ip();
                $ul->platform = mb_strpos( Request::getHost(), 'dentavox' )!==false ? 'vox' : 'trp';
                $ul->save();
                session(['login-logged' => time()]);

                if( !$this->user->isBanned('vox') && $this->user->bans->isNotEmpty() ) {
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

            if(mb_strpos(Request::url(), '//dev-') && !$this->admin) {
                echo '<a href="'.str_replace('//dev-', '//', Request::url()).'">Click here</a> or <a href="'.url('cms').'"> log in as admin </a>';
                exit;
            }

            return $next($request);
        });

        $this->categories = [];
        $clist = config('categories');
        foreach ($clist as $cat) {
            $this->categories[$cat] = trans('front.categories.'.$cat);
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

        $params['cache_version'] = '20181016-2';

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
                $params['unbanned_text'] = 'Now you can fill out surveys again and earn DCN by giving your honest answers!<br/>
                <br/>
                <b>Just take your time and focus!</b>';
            } else if( $params['unbanned_times']==2 ) {
                $params['unbanned_text'] = 'Ready to take surveys again? Hope you had enough time to refocus and will now give your earnest answers. <br/>
                <br/>
                <b>Just pick a topic and get it started!</b>';
            } else {
                $params['unbanned_text'] = 'Did you enjoy your week off? This is your last chance to prove that your answers are genuine and that you really want to help improve global dental care.<br/>
                <br/>
                <b>Your next ban will be permanent.</b>';
            }
        }
        return view('vox.'.$page, $params);
    }

    public function ShowView($page, $params=array()) {
        $this->PrepareViewData($page, $params, 'front');

        $params['cache_version'] = '20181016-2';
        // "2018-05-05 00:00:00.000000"
        
        return view('front.'.$page, $params);
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
        $params['social_image'] = !empty($params['social_image']) ? $params['social_image'] : url( $text_domain=='front' ? '/img/logo.png' : '/img-vox/logo-text.png'  );
        //dd($params['pages_header']);

        if( $text_domain=='vox' && !empty($this->user) && !$this->user->vox_active) {
            $this->user->vox_active = true;
            $this->user->save();
        }


        $params['just_registered'] = false;
        if( session('just_registered') ) {
            $params['just_registered'] = true;
            session([
                'just_registered' => false
            ]);
        }
    }

}
