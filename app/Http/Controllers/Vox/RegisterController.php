<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

class RegisterController extends FrontController {

	/**
     * old register link redirect
     */
	public function list() {
		return redirect( getVoxUrl('/').'?dcn-gateway-type=patient-register');
	}
}