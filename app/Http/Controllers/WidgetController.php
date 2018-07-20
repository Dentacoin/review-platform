<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App;
use Request;
use Route;

use App\Models\User;

class WidgetController extends BaseController
{

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

    }

    public function widget($locale,$user_id,$hash,$mode) {
        $user = User::find($user_id);
        if( !empty($user) && !empty($hash) && $user->get_widget_token()==$hash ) {
            if(!empty($_SERVER['HTTP_REFERER'])) {
                $parts = parse_url($_SERVER['HTTP_REFERER']);
                if(!empty($parts['host']) && mb_strpos( $parts['host'], 'dentacoin.com' )===false ) {
                    $user->widget_activated = true;
                    $user->save();
                }
            }


            $params['user'] = $user;
            $params['reviews'] = intval($mode) ? $user->reviews_in()->where('verified', 1) : $user->reviews_in();
            return response()->view('widget.widget', $params)
            ->header('Access-Control-Allow-Origin', '*');
        } else {
            dd( $user->get_widget_token() );
        }

        return null;
    }    

}