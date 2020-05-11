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


    public function showLoginForm() {

        if(!empty($this->user)) {
            return redirect(getLangUrl('/'));
        }
        return redirect( getLangUrl('/').'?'. http_build_query(['dcn-gateway-type'=>'patient-login']) );
    }

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