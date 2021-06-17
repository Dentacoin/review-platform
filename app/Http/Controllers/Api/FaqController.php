<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Input;

use App\Models\StopTransaction;
use App\Models\VoxCategory;
use App\Models\VoxAnswer;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;

use Validator;
use Response;
use Request;
use Cookie;
use Auth;
use Mail;
use App;

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