<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Models\PageSeo;

use App;
use Request;

class FaqController extends FrontController {
	/**
     * FAQ page view
     */
	public function home($locale=null) {

        $pathToFile = base_path().'/resources/lang/en/faq.php';
        $content = json_decode( file_get_contents($pathToFile), true );

		$seos = PageSeo::find(6);

		return $this->ShowVoxView('faq', array(
			'content' => $content,
			'js' => [
				'faq.js'
			],
			'css' => [
				'vox-faq.css'
			],
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
        ));

	}

}