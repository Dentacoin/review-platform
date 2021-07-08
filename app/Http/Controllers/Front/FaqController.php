<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\PageSeo;

class FaqController extends FrontController {

	/**
     * FAQ page view
     */
	public function home($locale=null) {

        $pathToFile = base_path().'/resources/lang/en/faq-trp.php';
        $content = json_decode( file_get_contents($pathToFile), true );

		$seos = PageSeo::find(22);

		return $this->ShowView('faq', array(
			'content' => $content,
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
            'css' => [
            	'trp-faq.css'
            ],
			'js' => [
				'faq.js'
			]
        ));

	}

}