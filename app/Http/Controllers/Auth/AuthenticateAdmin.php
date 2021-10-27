<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Models\AdminIp;
use App\Models\User as UserModel;
use App\Models\Admin;
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

                if(Auth::guard('admin')->user()->password_last_updated_at->toDateTimeString() < Carbon::now()->addDays(-60)->toDateTimeString()) {
                    
                // if(Auth::guard('admin')->user()->password_last_updated_at->toDateTimeString() > Carbon::now()->addDays(-60)->toDateTimeString()) {

                    return redirect('cms/password-expired');
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
        
        if($request->isMethod('post')) {
            $admin = Auth::guard('admin')->user();

            if(!empty($admin)) {
                $validator = Validator::make($request->all(), [
                    'current-password' => array('required', function ($attribute, $value, $fail) use ($admin) {
                        if (!\Hash::check($value, $admin->password)) {
                            return redirect('cms/password-expired')
                            ->withInput()
                            ->with('error-message', 'The current password is incorrect.');
                        }
                    }),
                    'new-password' => array('required','min:10','regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/'),
                    // 'new-password' => array('required'),
                    'new-password-repeat' => array('required','same:new-password'),
                ]);
        
                if ($validator->fails()) {
                    return redirect('cms/password-expired')
                    ->withInput()
                    ->withErrors($validator);
                } else {
                    
                    $admin->password = bcrypt($request->input('new-password'));
                    $admin->save();
                    
                    return redirect('cms/');
                }
            }

            return redirect('cms/login');
        }

        return view('admin.password-expire');
    }

}