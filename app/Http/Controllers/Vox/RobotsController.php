<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use Request;

class RobotsController extends FrontController {	
	/**
     * robots.txt file content
     */
	public function content($locale=null) {

		if (in_array(Request::getHost(), ['vox.dentacoin.com', 'urgent.dentavox.dentacoin.com', 'dev.dentavox.dentacoin.com'])) {

			$content = 'User-agent: *
Disallow: /';

		} else {

			$content = 'User-agent: *
Disallow: /cms/
Disallow: /suggest-clinic/
Disallow: /suggest-dentist/
Disallow: /banned/
Disallow: /profile-redirect/
Disallow: /status/
';
		}

		return response($content, 200)->header('Content-Type', 'text/plain');
	}
}