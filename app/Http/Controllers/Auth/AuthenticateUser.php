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
        return redirect( getLangUrl('/').'?popup=popup-login' );
    }
    public function showLoginFormVox()
    {
        if(!empty($this->user)) {
            return redirect(getLangUrl('profile'));
        }

        
        return $this->ShowVoxView('login',[
            'workaround' => session('vox-redirect-workaround'),
            'noindex' => ' ',
            'js' => [
                'login.js'
            ],
            'jscdn' => [
                'https://hosted-sip.civic.com/js/civic.sip.min.js',
            ],
            'csscdn' => [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ],
        ]);
    }

    public function postLogin(Request $request)
    {
        if (Auth::guard('web')->attempt( ['email' => $request->input('email'), 'password' => $request->input('password') ], $request->input('remember') )) {
            
            if( $ban_info = Auth::guard('web')->user()->isBanned('vox') ) {
                Auth::guard('web')->logout();
                return Response::json( [
                    'success' => false, 
                    'popup' => 'banned-popup'
                ] );
            }

            if(Auth::guard('web')->user()->loggedFromBadIp()) {
                Auth::guard('web')->logout();
                return Response::json( [
                    'success' => false, 
                    'popup' => 'suspended-popup'
                ] );
            }

            if( Auth::guard('web')->user()->is_dentist && Auth::guard('web')->user()->status!='approved' && Auth::guard('web')->user()->status!='added_approved' && Auth::guard('web')->user()->status!='test') {
                $array = [
                    'success' => false, 
                    'popup' => 'verification-popup',
                    'hash' => Auth::guard('web')->user()->get_token(),
                    'id' => Auth::guard('web')->user()->id,
                    'short_description' => Auth::guard('web')->user()->short_description,
                    'is_clinic' => Auth::guard('web')->user()->is_clinic,
                ];
                Auth::guard('web')->logout();
                return Response::json( $array );
            }

            $sess = [
                'just_login' => true,
            ];
            session($sess);

            return Response::json( [
                'success' => true
            ] );
        } else {
            return Response::json( [
                'success' => false, 
                'message' => trans('front.page.login.error')
            ] );
        }
    }

    public function postLoginVox(Request $request)
    {
        if (Auth::guard('web')->attempt( ['email' => $request->input('email'), 'password' => $request->input('password') ], $request->input('remember') )) {

            if(Auth::guard('web')->user()->isBanned('vox')) {
                return redirect( getLangUrl('banned'));
            }

            if(Auth::guard('web')->user()->loggedFromBadIp()) {
                Auth::guard('web')->logout();
                return redirect( getLangUrl('login').'?suspended-popup' );
            }

            $sess = [
                'just_login' => true,
            ];
            session($sess);
            
            $intended = session()->pull('our-intended');

            return redirect( $intended ? $intended : getLangUrl('/') );
        } else {
            return redirect( getLangUrl('login') )
            ->withInput()
            ->with('error-message', trans('front.page.login.error'));         
        }
    }

    public function getLogout() {
        if( Auth::guard('web')->user() ) {
            Auth::guard('web')->user()->logoutActions();
        }
        Auth::guard('web')->logout();
        return redirect( getLangUrl('/') );
    }

}