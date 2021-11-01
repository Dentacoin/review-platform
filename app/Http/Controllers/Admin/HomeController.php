<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use PragmaRX\Google2FAQRCode\Google2FA;
use Request;
use Auth;

class HomeController extends AdminController {
	
    public function list() {
    	return $this->ShowView('home', array(
    	));
    }

    public function authentication() {

        $admin = Auth::guard('admin')->user();
        if(empty($admin)) {
            return redirect('cms/login');
        }
        
        if(Request::isMethod('post')) {

            $google2fa = new Google2FA();
            if(Request::input('kyc_code')) {

                if($google2fa->verifyKey(env('GOOGLE_AUTHENTICATOR_APP_SALT'), Request::input('kyc_code'))) {
                    $admin->logged_in = false;
                    $admin->two_factor_auth = true;
                    $admin->save();

                    return redirect('cms/');
                } else {
                    return redirect('cms/admin-authentication')
                    ->withInput()
                    ->with('error-message', 'Wrong code!');
                }
            } else {
                return redirect('cms/admin-authentication')
                ->withInput()
                ->with('error-message', 'KYC code is required.');
            }
        }

        $qrCodeUrl = null;

        if(!$admin->two_factor_auth) {

            $google2fa = new Google2FA();
            $qrCodeUrl = $google2fa->getQRCodeInline(
                'TRP Admin',
                $admin->email,
                env('GOOGLE_AUTHENTICATOR_APP_SALT'),
                // $google2fa->generateSecretKey(),
                250
            );
        }

        return $this->ShowView('authentication', array(
            'qrCodeUrl' => $qrCodeUrl,
    	));
    }    
}