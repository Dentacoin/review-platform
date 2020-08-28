<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use Illuminate\Support\Facades\Input;

use App\Models\VoxAnswer;
use App\Models\User;

use Validator;
use Response;
use Request;
use Cookie;
use Auth;
use Mail;

class IndexController extends ApiController {
    
	public function stats() {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
		header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X- 
		Request-With');
		
		return Response::json( array(
			'users_count' => User::getCount('vox'),
			'answers_count' => VoxAnswer::getCount(),
		) );
       
    }
}