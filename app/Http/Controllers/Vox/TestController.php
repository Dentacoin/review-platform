<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Auth;
use Mail;

class TestController extends FrontController
{
	public function list($locale=null) {
		return $this->ShowVoxView('test', array(
        ));
	}
}