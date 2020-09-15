<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;
use App\Models\User;

use Validator;
use Response;
use Request;

class MiscController extends FrontController {

	/**
     * share/send dentist's profile
     */
	public function share($locale=null) {

		if( request('email') && request('address') && mb_strpos( request('address'),  request()->getHttpHost() )!==false ) {

			$validator_arr = [
                'email' => ['required', 'email'],
            ];
            $validator = Validator::make(Request::all(), $validator_arr);
            if (!$validator->fails()) {
	            //Mega hack
		        $temp_email = $this->user->email;
		        $this->user->email = request('email');
		        $this->user->save();

		        $this->user->sendTemplate( 10, [
		            'link' => request('address')
		        ]);

		        //Back to original
		        $this->user->email = $temp_email;
		        $this->user->save();

				return Response::json( [
		            'success' => true,
		        ] );
            }
		}

		return Response::json( [
            'success' => false,
        ] );
	}
}