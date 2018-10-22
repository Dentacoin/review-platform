<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Civic;
use Response;
use Request;
use Validator;

class CivicController extends BaseController
{

	public function add() {
		if( Request::input('jwtToken') && Request::input('data') ) {
			$c = Civic::where('jwtToken', 'LIKE', Request::input('jwtToken'))->first();
			if(empty($c)) {
				$c = new Civic;				
			}
			$c->jwtToken = Request::input('jwtToken');
			$data = Request::input('data');
			dd($data);
			foreach ($data as $key => $value) {
			}
			$c->response = json_encode();
			$c->save();
		}
		var_dump(Request::input());
		exit;
	}
}