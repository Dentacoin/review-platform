<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

class LoginController extends FrontController {
    
    public function status() {
        return !empty($this->user) ? $this->user->convertForResponse() : null;
    }
}