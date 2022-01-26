<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Models\Country;
use App\Models\User;

class SitemapController extends FrontController {

	/**
     * sitemaps for TRP
     */
	public function links($locale=null) {

		$links = [
			getLangUrl('/'),
			getLangUrl('welcome-dentist'),
			getLangUrl('faq'),
			getLangUrl('dentist-listings-by-country'),
			getLangUrl('dentists/worldwide'),
			getLangUrl('dentists/bucuresti-municipiul-bucuresti-romania/'),
		];

		$dentists = User::where('is_dentist', '1')
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNull('self_deleted')
		->get();

		foreach ($dentists as $dentist) {
			if($dentist->address) {
				$links[] = $dentist->getLink();
			}
		}

		$dentists = User::where('is_dentist', 1)
		->whereNotNull('address')
		->whereNotNull('country_id')
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNotNull('city_name')
		->groupBy('country_id')
		->get()
		->pluck('country_id');

        $dentist_countries = Country::whereIn('id', $dentists )->get();

        $dentist_cities = User::where('is_dentist', 1)
		->whereNotNull('address')
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNotNull('country_id')
		->whereNotNull('state_name')
		->whereNotNull('city_name')
		->groupBy('state_name')
		->groupBy('city_name')
		->get();
        
        $dentist_states = User::where('is_dentist', 1)
		->whereNotNull('address')
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNotNull('country_id')
		->whereNotNull('state_name')
		->whereNotNull('city_name')
		->groupBy('state_name')
		->get();

        foreach ($dentist_countries as $country) {
        	$links[] = getLangUrl('dentists-in-'.$country->slug);
        }

    	foreach ($dentist_states as $user) {
    		$links[] = getLangUrl( 'dentists-in-'.$user->country->slug.'/'.$user->state_slug);
    	}

    	foreach ($dentist_cities as $user) {
    		$links[] = getLangUrl( str_replace([' ', "'", '.'], ['-', '',''], 'dentists/'.strtolower($user->city_name).'-'.$user->state_slug.'-'.$user->country->slug));
    	}

		$content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		foreach ($links as $link) {
			$content .= '<url><loc>'.$link.'</loc></url>';
		}
		$content .= '</urlset>';

		return response($content, 200)->header('Content-Type', 'application/xml');
	}

	/**
     * sitemaps list for TRP & TRP blog
     */
	public function sitemap($locale=null) {

        $content = '<?xml version="1.0" encoding="UTF-8"?>
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		   <sitemap>
		      <loc>https://reviews.dentacoin.com/sitemap-trusted-reviews.xml</loc>
		      <lastmod>2019-11-10</lastmod>
		   </sitemap>
		   <sitemap>
		      <loc>https://reviews.dentacoin.com/blog/sitemap_index.xml</loc>
		      <lastmod>2019-11-10</lastmod>
		   </sitemap>
		</sitemapindex>';
        
		return response($content, 200)->header('Content-Type', 'application/xml');
	}

}