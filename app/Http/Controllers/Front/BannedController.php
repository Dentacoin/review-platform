<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

class BannedController extends FrontController {

	public function home($locale=null) {
			
		return $this->ShowView('banned', array(
			'ban_expires' => session('ban-expires'),
			'noIndex' => true,
			'js' => [
				'banned.js'
			]
        ));
	}

	public function profile_redirect($locale=null) {

		if (!empty($this->user) && !$this->user->isBanned('trp')) {
			return redirect( getLangUrl('page-not-found'));
		}
			
		return $this->ShowView('profile-redirect', array(
			'noIndex' => true,
        ));
	}

}