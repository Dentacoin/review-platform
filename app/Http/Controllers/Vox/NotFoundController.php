<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use App\Models\PageSeo;

class NotFoundController extends FrontController {
	/**
     * 404 page view
     */
	public function home($locale=null) {
		if(!empty($this->user) && $this->user->isBanned('vox')) {
			return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
		}

		$seos = PageSeo::find(4);

		return $this->ShowVoxView('404', array(
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
            'css' => [
            	'vox-404.css'
            ],
		), 404);	
	}

	public function catch($locale=null) {

		return redirect(getVoxUrl('page-not-found'));
	}
}