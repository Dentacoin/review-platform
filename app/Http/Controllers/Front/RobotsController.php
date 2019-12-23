<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use Request;


class RobotsController extends FrontController
{
	public function content($locale=null) {

		if (Request::getHost() == 'urgent.reviews.dentacoin.com' || Request::getHost() == 'dev.reviews.dentacoin.com') {

			$content = 'User-agent: *
Disallow: /';

		} else {

			$content = 'User-agent: *
Disallow: /cms/
Disallow: /question-count/
Disallow: /suggest-clinic/
Disallow: /suggest-dentist/
Disallow: /want-to-invite-dentist/
Disallow: /lead-magnet-session/
Disallow: /lead-magnet-results/
';
		}

		return response($content, 200)
            ->header('Content-Type', 'text/plain');
        
	}

}