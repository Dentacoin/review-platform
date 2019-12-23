<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use Request;


class RobotsController extends FrontController
{
	public function content($locale=null) {

		if (Request::getHost() == 'vox.dentacoin.com' || Request::getHost() == 'urgent.dentavox.dentacoin.com') {

			$content = 'User-agent: *
Disallow: /';

		} else {

			$content = 'User-agent: *
Disallow: /cms/
Disallow: /question-count/
Disallow: /suggest-clinic/
Disallow: /suggest-dentist/
Disallow: /banned/
Disallow: /profile-redirect/
Disallow: /status/
';

		}

		return response($content, 200)
            ->header('Content-Type', 'text/plain');
        
	}

}