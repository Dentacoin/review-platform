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

class ApiController extends BaseController {

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

        //$this->user = Auth::guard('web')->user();
        $this->middleware(function ($request, $next) {

            $this->admin = Auth::guard('admin')->user();
            $this->user = Auth::guard('web')->user();
            $this->country_id = !empty($this->user->country_id) ? $this->user->country_id : session('country_id');
            $this->city_id = !empty($this->user->city_id) ? $this->user->city_id : session('city_id');

            $request->attributes->add([
                'admin' => $this->admin,
                'user' => $this->user,
                'country_id' => $this->country_id,
                'city_id' => $this->city_id,
            ]);

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

}