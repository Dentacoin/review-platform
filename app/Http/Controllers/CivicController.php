<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use App\Models\Civic;

use Response;
use Request;

class CivicController extends BaseController {

	public function add() {
        header('Access-Control-Allow-Origin: *');
		
		if( Request::input('jwtToken') && Request::input('data') ) {
			$c = Civic::where('jwtToken', 'LIKE', Request::input('jwtToken'))->first();
			if(empty($c)) {
				$c = new Civic;				
			}
			$c->jwtToken = Request::input('jwtToken');
			$data = Request::input('data');

			$cardInfo = [];
			$fields = [];
			if(!empty( $data['data'] )) {
				foreach ($data['data'] as $key => $value) {
					$fields[$value['label']] = $value['value'];
					if( mb_strpos( $value['label'], 'documents.' ) !==false ) {
						$data['data'][$key]['value'] = 'Masked';
					}
				}
			}
			$hashFields = [
				'documents.genericId.type',
				'documents.genericId.number',
				'documents.genericId.dateOfBirth',
				'documents.genericId.dateOfExpiry',
			];
			foreach ($hashFields as $f) {
				$cardInfo[] = !empty($fields[$f]) ? $fields[$f] : '';
			}

			if(isset($fields['documents.genericId.country'])) {
				$c->country_code = $fields['documents.genericId.country'];
			}
			$c->response = json_encode($data);
			$c->hash = md5(implode('|', $cardInfo));
			$c->save();
		}

		return Response::json( $data );
	}
}