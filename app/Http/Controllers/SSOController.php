<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Response;
use Request;
use Auth;

class SSOController extends BaseController
{
	protected function manageCustomCookie() {

        if(!empty(request('slug')) && !empty(request('type')) && !empty(request('token'))) {
            //logging
	        $slug = $this->decrypt(request('slug'));

            if(!empty(Auth::guard('admin')->user())) {
                dd($slug);
            }

            $user = User::find( $slug );

            if($user) {
            	$token = $this->decrypt(request('token'));
	            $type = $this->decrypt(request('type'));
                $approved_statuses = array('approved', 'test', 'added_by_clinic_claimed','added_by_dentist_claimed', 'clinic_branch');

                $external_patient = false;
                if ($user->platform == 'external' && (Request::getHost() == 'dentavox.dentacoin.com' || Request::getHost() == 'vox.dentacoin.com' || Request::getHost() == 'urgent.dentavox.dentacoin.com')) {
                    $external_patient = true;
                }

                if($user->self_deleted != NULL) {
                    return redirect(getLangUrl('page-not-found'));
                } else if($external_patient) {
                    $session_arr = [
                        'token' => $token,
                        'id' => $slug,
                        'type' => $type
                    ];
                    session(['logged_user' => $session_arr]);
                    Auth::login($user, true);

                    return redirect(getLangUrl('/'));
                } else if(!in_array($user->status, $approved_statuses) ) {
                    return redirect(getLangUrl('page-not-found'));
                } else {
                    $session_arr = [
                        'token' => $token,
                        'id' => $slug,
                        'type' => $type
                    ];
                    session(['logged_user' => $session_arr]);
                    Auth::login($user, true);

                    if(!empty(request('dentist_slug'))) {
                        return redirect(getLangUrl('dentist/'.request('dentist_slug')));
                    }

                    return redirect(getLangUrl('/'));
                }
            } else {
                return redirect(getLangUrl('page-not-found'));
            }
        } else if(!empty(request('logout-token'))) {
            //logging out
            $token = $this->decrypt(request('logout-token'));
            if(!empty(session('logged_user')['token']) && session('logged_user')['token'] == $token) {
                session([
                    'logged_user' => false
                ]);
            }

            //TRP / Vox
	        if( Auth::guard('web')->user() ) {
	            Auth::guard('web')->user()->logoutActions();
	        }
            Auth::guard('web')->logout();
        } else {
            return redirect(getLangUrl('page-not-found'));
        }
    }

    public function getLoginToken() {
        // $user = Auth::guard('web')->user();
        // if($user) {

        //     if( mb_substr(Request::path(), 0, 3)=='cms' || empty(Request::getHost()) ) {
        //         $platform = $user->platform;
        //     } else {
        //         $platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
        //     }

        //     $tokenobj = $user->createToken('LoginToken');
        //     $tokenobj->token->platform = $platform;
        //     $tokenobj->token->save();
        //     return User::encrypt($tokenobj->accessToken);
        // }

        return User::encrypt(session('logged_user')['token']);
    }

    public function decrypt($encrypted_text) {
        $arr = explode('|', $encrypted_text);
        if (count($arr)!=2) {
            return null;
        }
        $data = $arr[0];
        $iv = $arr[1];
        $iv = base64_decode($iv);

        try {
            $raw_text = openssl_decrypt($data, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), 0, $iv);
        } catch (\Exception $e) {
            $raw_text = false;
        }

        return $raw_text;
    }



}