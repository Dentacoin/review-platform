<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

class LoginController extends FrontController {
    
    /**
     * get user's email and name for TRP & Dentavox Blogs comments, if he is already logged in
     */
    public function status() {
        return !empty($this->user) ? $this->user->convertForResponse() : null;
    }
}