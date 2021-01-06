<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

class BannedController extends FrontController {

	public function home($locale=null) {
		
		return $this->ShowVoxView('banned', array(
			'ban_expires' => session('ban-expires'),
			'noIndex' => true,
			'js' => [
				'banned.js'
			]
        ));
	}

	public function profile_redirect($locale=null) {

		if (!empty($this->user) && !$this->user->isBanned('vox')) {
			return redirect( getVoxUrl('page-not-found'));
		}
			
		return $this->ShowVoxView('profile-redirect', array(
			'noIndex' => true,
        ));
	}

}