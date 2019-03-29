<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App;
use App\Models\User;
use App\Models\Country;


class SitemapController extends FrontController
{
	public function links($locale=null) {

		// $u = User::where('is_dentist', 1)->where('id','>=',70000)->where('id','<',80000)->get();
		// $i=0;
		// foreach ($u as $user) {
		// 	echo ++$i.'<br/>';
		// 	$user->address = $user->address.'';
		// }
		// exit;

		$links = [
			getLangUrl('/'),
			getLangUrl('welcome-dentist'),
			getLangUrl('faq'),
			getLangUrl('dentist-listings-by-country'),
			getLangUrl('dentists/worldwide'),
			getLangUrl('dentists/bucuresti-municipiul-bucuresti-romania/'),
		];

		$dentists = User::where('is_dentist', '1')->where('status', 'approved')->get();

		foreach ($dentists as $dentist) {
			if($dentist->address) {
				$links[] = $dentist->getLink();
			}
		}

		$dentists = User::where('is_dentist', 1)->whereNotNull('address')->whereNotNull('country_id')->where('status', 'approved')->whereNotNull('city_name')->groupBy('country_id')->get()->pluck('country_id');
        $dentist_countries = Country::whereIn('id', $dentists )->get();
        $dentist_cities = User::where('is_dentist', 1)->whereNotNull('address')->where('status', 'approved')->whereNotNull('country_id')->whereNotNull('state_name')->whereNotNull('city_name')->groupBy('state_name')->groupBy('city_name')->get();
        $dentist_states = User::where('is_dentist', 1)->whereNotNull('address')->where('status', 'approved')->whereNotNull('country_id')->whereNotNull('state_name')->whereNotNull('city_name')->groupBy('state_name')->get();

        foreach ($dentist_countries as $country) {
        	$links[] = getLangUrl('dentists-in-'.$country->slug);
        }

    	foreach ($dentist_states as $user) {
    		$links[] = getLangUrl( 'dentists-in-'.$user->country->slug.'/'.$user->state_slug);
    	}

    	foreach ($dentist_cities as $user) {
    		$links[] = getLangUrl( str_replace(' ', '-', 'dentists/'.strtolower($user->city_name).'-'.$user->state_slug.'-'.$user->country->slug));
    	}

		$content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		foreach ($links as $link) {
			$content .= '<url><loc>'.$link.'</loc></url>';
		}
		$content .= '</urlset>';

		return response($content, 200)
            ->header('Content-Type', 'application/xml');
        
	}

}