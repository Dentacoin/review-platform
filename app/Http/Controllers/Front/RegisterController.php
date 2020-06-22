<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use Illuminate\Support\Facades\Input;

use App\Models\IncompleteRegistration;
use App\Models\UserCategory;
use App\Models\UserAction;
use App\Models\UserInvite;
use App\Models\UserLogin;
use App\Models\UserTeam;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\Civic;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Response;
use Redirect;
use Request;
use Image;
use Auth;
use Mail;

class RegisterController extends FrontController
{
    public function register($locale=null) {

        return redirect( getLangUrl('welcome-dentist'), 301 );
    }

    public function upload($locale=null) {

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = User::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }

    public function register_invite($locale=null) {

        if (request('user_id')) {
            $user = User::find(request('user_id'));

            if( (request('user_hash') == $user->get_token()) && request('clinic_id') && !$user->is_clinic ) {
                $clinic = User::find( request('clinic_id') );

                if(!empty($clinic)) {
                    $team = UserTeam::where('dentist_id', $user->id)->where('user_id', $clinic->id)->first();

                    if (!$team) {
                        $newclinic = new UserTeam;
                        $newclinic->dentist_id = $user->id;
                        $newclinic->user_id = $clinic->id;
                        $newclinic->approved = 0;
                        $newclinic->save();

                        $clinic->sendTemplate(34, [
                            'dentist-name' => $user->getName(),
                            'profile-link' => $user->getLink()
                        ], 'trp');
                    }

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.popup.verification-popup.join-workplace.success', ['clinic-name' => request('clinic_name')]),
                    ] );
                }
            }
        }
        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.join-workplace.error'),
        ] );

    }

    public function invite_dentist($locale=null) {

        if (request('user_id')) {
            $user = User::find(request('user_id'));

            if( (request('user_hash') == $user->get_token()) && request('dentist_id') && $user->is_clinic ) {
                $dentist = User::find( request('dentist_id') );

                if(!empty($dentist)) {
                    $team = UserTeam::where('dentist_id', $dentist->id)->where('user_id', $user->id)->first();

                    if (!$team) {
                        $newdentist = new UserTeam;
                        $newdentist->dentist_id = $dentist->id;
                        $newdentist->user_id = $user->id;
                        $newdentist->approved = 0;
                        $newdentist->new_clinic = 1;
                        $newdentist->save();

                        // $dentist->sendTemplate(33, [
                        //     'clinic-name' => $user->getName(),
                        //     'clinic-link' => $user->getLink()
                        // ], 'trp');
                    }

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.popup.verification-popup.dentist-invite.success', ['dentist-name' => $dentist->getName()]),
                    ] );
                }
            }
        }
        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.dentist-invite.error'),
        ] );

    }

    public function invite_clinic($locale=null) {

        if (request('user_id')) {
            $user = User::find(request('user_id'));

            $validator = Validator::make(Request::all(), [
                'clinic_name' => array('required', 'min:3'),
                'clinic_email' => array('required', 'email', 'unique:users,email'),
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

                $invitation = new UserInvite;
                $invitation->user_id = $user->id;
                $invitation->invited_email = Request::Input('clinic_email');
                $invitation->invited_name = Request::Input('clinic_name');
                $invitation->save();

                //Mega hack
                $dentist_name = $user->name;
                $dentist_email = $user->email;
                $user->name = Request::Input('clinic_name');
                $user->email = Request::Input('clinic_email');
                $user->save();

                $user->sendTemplate( 42  , [
                    'dentist_name' => $dentist_name,
                ], 'trp');

                //Back to original
                $user->name = $dentist_name;
                $user->email = $dentist_email;
                $user->save();

                return Response::json( [
                    'success' => true,
                    'message' => trans('trp.popup.verification-popup.workplace.success', ['clinic-name' => request('clinic_name')]),
                ] );
            }
        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.workplace.error'),
        ] );

    }


    public function verification_dentist($locale=null) {
        if (request('user_id') && !empty(User::find(request('user_id'))) && !empty(request('user_hash'))) {

            $user = User::find(request('user_id'));

            if($user->get_token() == request('user_hash')) {

                $validator = Validator::make(Request::all(), [
                    'description' => array('required', 'max:512'),
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

                    $user->description = Request::Input('description');
                    $user->save();

                    return Response::json( [
                        'success' => true,
                        'user' => $user->is_clinic ? 'clinic' : 'dentist',
                        'message' => trans('trp.popup.verification-popup.user-info.success'),
                    ] );
                }
            }

        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.user-info.error'),
        ] );
    }

    public function add_work_hours($locale=null) {
        if (request('last_user_id') && !empty(User::find(request('last_user_id'))) && !empty(request('last_user_hash'))) {

            $user = User::find(request('last_user_id'));

            if(request('last_user_hash') == $user->get_token()) {

                $wh = Request::input('work_hours');
                foreach ($wh as $k => $v) {
                    if( empty($wh[$k][0][0]) || empty($wh[$k][0][1]) || empty($wh[$k][1][0]) || empty($wh[$k][1][1]) || empty(Request::input('day-'.$k))) { 
                        unset($wh[$k]);
                        continue;
                    }

                    if( !empty($wh[$k][0]) && !empty(Request::input('day-'.$k))) {
                        $wh[$k][0] = implode(':', $wh[$k][0]);
                    }
                    if( !empty($wh[$k][1]) && !empty(Request::input('day-'.$k)) ) {
                        $wh[$k][1] = implode(':', $wh[$k][1]);
                    }
                }

                $user->work_hours = $wh;
                $user->save();

                return Response::json( [
                    'success' => true,
                ] );
            }
        }

        return Response::json( [
            'success' => false,
            'message' => trans('trp.common.something-wrong'),
        ] );

    }
}