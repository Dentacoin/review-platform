<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Models\AdminIp;
use App\Models\User as UserModel;
use App\User;

use Carbon\Carbon;

use Validator;
use Auth;

class AuthenticateAdmin extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = 'cms';
    protected function guard() {
        return Auth::guard('admin');
    }

    public function showLoginForm() {
        return view('admin.login');
    }

    public function postLogin(Request $request) {
        // AdminIps

        $safeIp = AdminIp::where('ip', UserModel::getRealIp())->first();

        if($safeIp) {

            if (Auth::guard('admin')->attempt( ['username' => $request->input('username'), 'password' => $request->input('password') ], $request->input('remember') )) {

                $admin = Auth::guard('admin')->user();
                $admin->logged_in = true;
                $admin->save();

                if(Auth::guard('admin')->user() && Auth::guard('admin')->user()->password_last_updated_at->toDateTimeString() < Carbon::now()->addDays(-60)->toDateTimeString()) {
                    return redirect('cms/password-expired');
                }

                if(Auth::guard('admin')->user() && Auth::guard('admin')->user()->logged_in) {
                    return redirect('cms/admin-authentication');
                }

                return redirect()->intended('');
            } else {
                return redirect('cms/login')
                ->withInput()
                ->with('error-message', 'Wrong username or password!');
            }
        } else {
            return redirect('cms/login')
            ->withInput()
            ->with('error-message', 'You can\'t login with this IP!');
        }
    }

    public function getLogout() {
        Auth::guard('admin')->logout();
        return redirect('cms/');
    }

    public function passwordExpired(Request $request) {

        $admin = Auth::guard('admin')->user();
        if(empty($admin)) {
            return redirect('cms/login');
        }
        
        if($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'current-password' => array('required'),
                'new-password' => array('required','min:10','regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/'),
                // 'new-password' => array('required'),
                'new-password-repeat' => array('required','same:new-password'),
            ]);
    
            if ($validator->fails()) {
                $msg = $validator->getMessageBag()->toArray();

                foreach ($msg as $field => $errors) {
                    if($errors[0] == 'The new-password format is invalid.') {
                        return redirect('cms/password-expired')
                        ->withInput()
                        ->with('error-message', 'The password must contain an uppercase letter a lowercase letter and a number');
                    }
                }

                return redirect('cms/password-expired')
                ->withInput()
                ->withErrors($validator);
            } else {

                if (!\Hash::check(request('current-password'), $admin->password)) {
                    return redirect('cms/password-expired')
                    ->withInput()
                    ->with('error-message', 'The current password is incorrect.');
                }

                $admin->password = bcrypt($request->input('new-password'));
                $admin->password_last_updated_at = Carbon::now();
                $admin->save();
                
                return redirect('cms/');
            }
        }

        return view('admin.password-expire');
    }
}