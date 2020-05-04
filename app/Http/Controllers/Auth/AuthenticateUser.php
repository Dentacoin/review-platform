<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\FrontController;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Models\User as RealUser;
use App\Models\PageSeo;

use App\User;

use Validator;
use Response;
use Session;
use Cookie;
use Auth;
use Lang;

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
            return redirect(getLangUrl('/'));
        }
        return redirect( getLangUrl('/').'?popup=popup-login' );
    }

    public function postLogin(Request $request)
    {
        if (Auth::guard('web')->attempt( ['email' => $request->input('email'), 'password' => $request->input('password') ], $request->input('remember') )) {
            
            if( Auth::guard('web')->user()->isBanned('trp') ) {
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

            if( Auth::guard('web')->user()->is_dentist && Auth::guard('web')->user()->status!='approved' && Auth::guard('web')->user()->status!='added_by_clinic_claimed' && Auth::guard('web')->user()->status!='added_by_dentist_claimed' && Auth::guard('web')->user()->status!='test') {
                $array = [
                    'success' => false, 
                    'popup' => 'verification-popup',
                    'hash' => Auth::guard('web')->user()->get_token(),
                    'id' => Auth::guard('web')->user()->id,
                    'work_hours' => Auth::guard('web')->user()->work_hours,
                    'description' => Auth::guard('web')->user()->description,
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

    // public function postLoginVox(Request $request)
    // {
    //     if (Auth::guard('web')->attempt( ['email' => $request->input('email'), 'password' => $request->input('password') ], $request->input('remember') )) {

    //         if(Auth::guard('web')->user()->isBanned('vox')) {
    //             return redirect( getLangUrl('banned'));
    //         }

    //         if(Auth::guard('web')->user()->loggedFromBadIp()) {
    //             Auth::guard('web')->logout();
    //             return redirect( getLangUrl('login').'?suspended-popup' );
    //         }

    //         if( Auth::guard('web')->user()->is_dentist && Auth::guard('web')->user()->status!='approved' && Auth::guard('web')->user()->status!='approved' && Auth::guard('web')->user()->status!='test') {
    //             return redirect( getLangUrl('welcome-to-dentavox') );
    //         }

    //         $sess = [
    //             'just_login' => true,
    //         ];
    //         session($sess);
            
    //         $intended = session()->pull('our-intended');

    //         return redirect( $intended ? $intended : ( $request->input('intended') ? $request->input('intended') : getLangUrl('/')) );
    //     } else {
    //         return redirect( getLangUrl('login') )
    //         ->withInput()
    //         ->with('error-message', trans('front.page.login.error'));         
    //     }
    // }

    public function getLogout() {
        if( Auth::guard('web')->user() ) {
            Auth::guard('web')->user()->logoutActions();
        }
        Auth::guard('web')->logout();
        return redirect( getLangUrl('/') );
    }

    public function authenticateUser(Request $request) {

        $validator = Validator::make($request->input(), [
            'token' => array('required'),
            'id' => array('required'),
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $checkToken = $this->checkUserIdAndToken($request->input('id'), $request->input('token'));

            if(is_object($checkToken) && property_exists($checkToken, 'success') && $checkToken->success) {

                $user = User::find($request->input('id'));

                if(!empty($user)) {

                    Auth::login($user);
                    
                    return Response::json( [
                        'success' => true
                    ] );
                }
            }

            return Response::json( [
                'error' => true
            ] );
        }
    }


    public function checkUserIdAndToken($id, $token)  {
        $header = array();
        $header[] = 'Accept: */*';
        $header[] = 'Authorization: Bearer ' . $token;
        $header[] = 'Cache-Control: no-cache';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_URL => 'https://api.dentacoin.com/api/check-user-info/',
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => array(
                'user_id' => $id
            )
        ));

        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        if(!empty($resp))   {
            return $resp;
        }else {
            return false;
        }
    }

}