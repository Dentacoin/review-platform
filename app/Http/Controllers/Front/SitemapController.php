<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App;
use App\Models\User;
use App\Models\Country;


class SitemapController extends FrontController
{
	public function links($locale=null) {

		$links = [
			getLangUrl('/'),
			getLangUrl('welcome-dentist'),
			getLangUrl('faq'),
			getLangUrl('dentist-listings-by-country'),
		];

		$dentists = User::where('is_dentist', '1')->where('status', 'approved')->get();

		foreach ($dentists as $dentist) {
			$links[] = $dentist->getLink();
		}

		$dentists = User::where('is_dentist', 1)->whereNotNull('country_id')->groupBy('country_id')->get()->pluck('country_id');
        $dentist_countries = Country::whereIn('id', $dentists )->get();

        foreach ($dentist_countries as $country) {
        	$links[] = getLangUrl('dentists-in-'.$country->slug);
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