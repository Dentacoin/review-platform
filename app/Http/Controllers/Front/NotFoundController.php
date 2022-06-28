<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Helpers\TrpHelper;
use App\Models\PageSeo;

class NotFoundController extends FrontController {

	/**
     * 404 page view
     */
	public function home($locale=null) {

		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		$seos = PageSeo::find(21);

		return $this->ShowView('404', [
			'featured' => TrpHelper::getFlickityDentists($this->user, $this->city_id, $this->country_id),
			'js' => [
				'index.js',
			],
            'css' => [
                'trp-404.css',
            ],
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
		], 404);	
	}
}