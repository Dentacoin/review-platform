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
		
		return Response::json( array(
			'users_count' => User::getCount('vox'),
			'answers_count' => VoxAnswer::getCount(),
		) );
       
    }
}