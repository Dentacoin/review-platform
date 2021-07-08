<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use Response;

class FaqController extends ApiController {

	public function getFaq() {
		$pathToFile = base_path().'/resources/lang/en/faq.php';
        $content = json_decode( file_get_contents($pathToFile), true );
		$pathToFileiOS = base_path().'/resources/lang/en/faq-ios.php';
        $contentiOS = json_decode( file_get_contents($pathToFileiOS), true );

        return Response::json( array(
			'faq' => $content,
			'faq_ios' => $contentiOS,
		) );
	}
    
}