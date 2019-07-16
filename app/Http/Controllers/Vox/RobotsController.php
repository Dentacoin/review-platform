<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use Request;


class RobotsController extends FrontController
{
	public function content($locale=null) {

		$content = 'User-agent: *
Disallow:'.( Request::getHost() == 'vox.dentacoin.com' ? '/' : '' );

		return response($content, 200)
            ->header('Content-Type', 'text/plain');
        
	}

}