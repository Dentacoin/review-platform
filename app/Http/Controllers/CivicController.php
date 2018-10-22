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
        header('Access-Control-Allow-Origin: *');
		if( Request::input('jwtToken') && Request::input('data') ) {
			$c = Civic::where('jwtToken', 'LIKE', Request::input('jwtToken'))->first();
			if(empty($c)) {
				$c = new Civic;				
			}
			$c->jwtToken = Request::input('jwtToken');
			$data = Request::input('data');
			if(!empty( $data['data'] )) {
				foreach ($data['data'] as $key => $value) {
					if( mb_strpos( $value['label'], 'documents.' ) !==false ) {
						$data['data'][$key]['value'] = 'Masked';
					}
				}
			}
			var_dump($data);
			$c->response = json_encode($data);
			$c->save();
		}
		exit;
	}
}