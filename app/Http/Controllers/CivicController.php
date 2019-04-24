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
			$hash = '';
			$cardInfo = [];
			if(!empty( $data['data'] )) {
				foreach ($data['data'] as $key => $value) {
					if( mb_strpos( $value['label'], 'documents.' ) !==false ) {
						$data['data'][$key]['value'] = 'Masked';
					}
					if( $value['label'] == 'documents.genericId.type' || $value['label'] == 'documents.genericId.number' || $value['label'] == 'documents.genericId.dateOfBirth' || $value['label'] == 'documents.genericId.dateOfExpiry' ) {
						$cardInfo[] = $value['value'];
					}

					
				}
			}
			$c->response = json_encode($data);
			$c->cardInfo = implode('|', $cardInfo);
			$c->hash = $c->cardInfo ? bcrypt($c->cardInfo) : '';
			$c->save();
		}

		return Response::json( $data );
	}
}