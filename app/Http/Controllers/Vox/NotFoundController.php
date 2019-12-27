<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use CArbon\Carbon;

use App;
use Mail;
use Response;
use Request;
use Cookie;
use Validator;

class NotFoundController extends FrontController
{

	public function home($locale=null) {
		if(!empty($this->user) && $this->user->isBanned('vox')) {
			return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
		}

		return $this->ShowVoxView('404', [] , 404);	
	}

	public function catch($locale=null) {

		return redirect(getVoxUrl('page-not-found'));
	}
}