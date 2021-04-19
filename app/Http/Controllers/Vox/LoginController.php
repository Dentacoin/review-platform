<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

class LoginController extends FrontController {

	/**
     * get user's email and name for Vox & Dentavox Blog comments, if he is already logged in
     */
    public function status() {
        return !empty($this->user) ? $this->user->convertForResponse() : null;
    }

}