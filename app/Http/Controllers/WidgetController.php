<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use App\Models\Review;
use App\Models\User;

use Route;
use App;

class WidgetController extends BaseController {

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        $roter_params = $request->route()->parameters();
        if(empty($roter_params['locale'])) {
            $locale = 'en';
        } else {
            if(!empty( config('langs.trp.'.$roter_params['locale']) )) {
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
        }

        return null;
    }    

    public function widget_new($locale=null,$user_id,$hash) {
        $user = User::find($user_id);
        if( !empty($user) && !empty($hash) && $user->get_widget_token()==$hash && !empty(request('layout'))) {
            if(!empty($_SERVER['HTTP_REFERER'])) {
                $parts = parse_url($_SERVER['HTTP_REFERER']);
                if(!empty($parts['host']) && mb_strpos( $parts['host'], 'dentacoin.com' )===false ) {
                    $user->widget_site = $_SERVER['HTTP_REFERER'];
                    $user->widget_activated = true;
                    $user->save();
                }
            }

            $layout = request('layout');

            if(!empty(request('review-type'))) {
                if (request('review-type') == 'all') {
                    $reviews = $user->reviews_in();

                    if(empty(request('review-all-count')) || (request('review-all-count') == 'all')) {
                        $reviews = $user->reviews_in();
                    } else {
                        $all_count = intval(request('review-all-count'));
                        $reviews = $user->reviews_in()->take($all_count);
                    }
                } else if (request('review-type') == 'trusted') {

                    if(empty(request('review-trusted-count')) || (request('review-trusted-count') == 'all')) {
                        $reviews = $user->reviews_in()->where('verified', 1);
                    } else {
                        $trusted_count = intval(request('review-trusted-count'));
                        $reviews = $user->reviews_in()->where('verified', 1)->take($trusted_count);
                    }
                    
                } else if(request('review-type') == 'custom') {
                    $reviews = [];
                    if (!empty(request('review-custom'))) {
                        foreach (request('review-custom') as $k => $cr) {
                            $reviews[] = Review::where('id', $cr)->where(function($query) use ($user_id) {
                                $query->where( 'dentist_id', $user_id)->orWhere('clinic_id', $user_id);
                            })->first();
                        }
                    }
                }
            }

            if(empty($reviews)) {
                $reviews = $user->reviews_in();
            }

            if (!empty(request('height'))) {
                $params['height'] = intval(request('height'));
            }
            if (!empty(request('width'))) {
                $params['width'] = intval(request('width'));
            }

            if (!empty(request('slide'))) {
                $params['slide'] = intval(request('slide'));
            }
            if (!empty(request('badge'))) {
                $params['badge'] = request('badge');
            }

            $params['layout'] = $layout;
            $params['user'] = $user;
            $params['reviews'] = $reviews;

            return response()->view('widget.new-widget', $params)
            ->header('Access-Control-Allow-Origin', '*');
        } else {
            dd( $user->get_widget_token() );
        }

        return null;
    }    

}