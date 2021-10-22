<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Illuminate\Support\Facades\Input;

use App\Helpers\GeneralHelper;

use App\Models\UserTeam;
use App\Models\User;

use Validator;
use Response;
use Request;
use Image;

class RegisterController extends FrontController {

    /**
     * old link for registration
     */
    public function register($locale=null) {
        return redirect( getLangUrl('welcome-dentist'), 301 );
    }

    /**
     * after register popup - add a team member photo
     */
    public function upload($locale=null) {
        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            list($thumb, $full, $name) = GeneralHelper::addTempImage($img);
            return Response::json(['success' => true, 'thumb' => $thumb, 'name' => $name ]);
        }
    }

    /**
     * after register popup - dentist sends a request for clinic team
     */
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
                            'dentist-name' => $user->getNames(),
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

    /**
     * after register popup - clinic adds an existing dentist to team
     */
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
                    }

                    return Response::json( [
                        'success' => true,
                        'message' => trans('trp.popup.verification-popup.dentist-invite.success', ['dentist-name' => $dentist->getNames()]),
                    ] );
                }
            }
        }
        return Response::json( [
            'success' => false,
            'message' => trans('trp.popup.verification-popup.dentist-invite.error'),
        ] );
    }

    /**
     * after register popup - add a description
     */
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

    /**
     * after register popup - add work hours
     */
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