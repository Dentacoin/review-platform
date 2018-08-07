<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\FrontController;
use App\User;
use Illuminate\Http\RedirectResponse;
use Auth;
use Lang;
use Validator;
use Response;
use Session;
use App\Models\User as RealUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthenticateUser extends FrontController
{
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
    protected $redirectTo = '/';
    protected function guard() {
        return Auth::guard('web');
    }
    public function showLoginForm()
    {
        if(!empty($this->user)) {
            return redirect(getLangUrl('profile'));
        }
        
        return $this->ShowView('login');
    }
    public function showLoginFormVox()
    {
        if(!empty($this->user)) {
            return redirect(getLangUrl('profile'));
        }
        
        return redirect(getLangUrl('/'));
    }

    public function postLogin(Request $request)
    {

        if (Auth::guard('web')->attempt( ['email' => $request->input('email'), 'password' => $request->input('password') ], $request->input('remember') )) {
            if( $ban_info = Auth::guard('web')->user()->isBanned('vox') ) {
                Auth::guard('web')->logout();
                return redirect( getLangUrl('login') )
                ->withInput()
                ->with('error-message', trans('front.page.login.vox-ban'));
            }
            return redirect()->intended('/');
        } else {
            return redirect( getLangUrl('login') )
            ->withInput()
            ->with('error-message', trans('front.page.login.error'));
        }
    }

    public function postLoginVox(Request $request)
    {
        if (Auth::guard('web')->attempt( ['email' => $request->input('email'), 'password' => $request->input('password') ], $request->input('remember') )) {

            session([
                'our-intended' => null
            ]);

            return Response::json( [
                'success' => true,
                'url' => getLangUrl('/')
            ] );
        } else {
            return Response::json( [
                'success' => false,
                'banned' => false
            ] );
        }
    }

    public function getLogout() {
        Auth::guard('web')->logout();
        return redirect('/');
    }

}