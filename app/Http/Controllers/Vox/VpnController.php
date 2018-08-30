<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Request;
use Response;

class VpnController extends FrontController
{

	public function list($locale=null) {
		return $this->ShowVoxView('vpn', array(
        ));
	}

}