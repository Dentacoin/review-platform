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

        


        $this->request = $request;

    }

}