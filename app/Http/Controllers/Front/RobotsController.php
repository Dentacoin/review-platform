<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use Request;

class RobotsController extends FrontController {

	/**
     * robots.txt file content
     */
	public function content($locale=null) {

		if (Request::getHost() == 'urgent.reviews.dentacoin.com' || Request::getHost() == 'dev.reviews.dentacoin.com') {

			$content = 'User-agent: *
Disallow: /';

		} else {

			$content = 'User-agent: *
Disallow: /cms/
Disallow: /suggest-clinic/
Disallow: /suggest-dentist/
Disallow: /want-to-invite-dentist/
Disallow: /lead-magnet-session/
Disallow: /review-score-test/
Disallow: /review-score-results/
';
		}

		return response($content, 200)->header('Content-Type', 'text/plain');
	}
}