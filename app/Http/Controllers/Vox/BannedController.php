<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

class BannedController extends FrontController {

	/**
     * view for banned patients
     */
	public function home($locale=null) {
		
		return $this->ShowVoxView('banned', array(
			'ban_expires' => session('ban-expires'),
			'noIndex' => true,
			'js' => [
				'banned.js'
			]
        ));
	}
	
	/**
     * redirect the banned patient to account
     */
	public function profile_redirect($locale=null) {

		if (!empty($this->user) && !$this->user->isBanned('vox')) {
			return redirect( getVoxUrl('page-not-found'));
		}
			
		return $this->ShowVoxView('profile-redirect', array(
			'noIndex' => true,
        ));
	}

}