<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

class HomeController extends AdminController {
	
    public function list() {
    	return $this->ShowView('home', array(
    	));
    }
}
