<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;


class RobotsController extends FrontController
{
	public function content($locale=null) {

		$content = 'User-agent: *
Disallow:';

		return response($content, 200)
            ->header('Content-Type', 'text/plain');
        
	}

}