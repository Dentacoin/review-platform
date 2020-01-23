<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App;
use Request;

class FaqController extends FrontController
{

	public function home($locale=null) {

        $pathToFile = base_path().'/resources/lang/en/faq.php';
        $content = json_decode( file_get_contents($pathToFile), true );

		return $this->ShowVoxView('faq', array(
			'content' => $content,
			'js' => [
				'faq.js'
			],
			'css' => [
				'vox-faq.css'
			]
        ));

	}

}