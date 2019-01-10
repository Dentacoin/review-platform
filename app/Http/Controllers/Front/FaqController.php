<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App;
use Request;

class FaqController extends FrontController
{

	public function home($locale=null) {

        $pathToFile = base_path().'/resources/lang/en/faq-trp.php';
        $content = json_decode( file_get_contents($pathToFile), true );


		return $this->ShowView('faq', array(
			'content' => $content,
			'social_image' => url('/img-trp/socials-cover-faq.jpg'),
			'js' => [
				'faq.js'
			]
        ));

	}

}