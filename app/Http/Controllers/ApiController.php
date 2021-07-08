<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Request;
use Route;
use Auth;
use App;

class ApiController extends BaseController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $request;
    public $current_page;
    public $user;

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

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
            }
        }

        App::setLocale( $locale );

        date_default_timezone_set("Europe/Sofia");

        
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('api')->user();

            $request->attributes->add([
                'user' => $this->user,
                // 'country_id' => $this->country_id,
                // 'city_id' => $this->city_id,
            ]);

            $response = $next($request);
            $response->headers->set('Referrer-Policy', 'no-referrer');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            //$response->headers->set('X-Frame-Options', 'DENY');
     
            return $response;

        });

        $this->request = $request;

    }

}