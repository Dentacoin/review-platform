<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Request;
use Response;

class VpnController extends FrontController
{

	public function list($locale=null) {
		return $this->ShowView('vpn', array(
        ));
	}

}