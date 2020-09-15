<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

class VpnController extends FrontController {

	/**
     * VPN page view (not using in now)
     */
	public function list($locale=null) {
		
		return $this->ShowView('vpn', array(
			'extra_body_class' => 'white-header',
        ));
	}
}