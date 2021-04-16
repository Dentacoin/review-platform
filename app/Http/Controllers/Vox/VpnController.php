<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Request;
use Response;

class VpnController extends FrontController {

	/**
     * VPN page view (not using in now)
     */
	public function list($locale=null) {
		return $this->ShowVoxView('vpn', array(
        ));
	}

}