<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Models\Country;
use App\Models\User;

use Validator;
use Response;
use Request;
use Mail;

class AddDentistController extends FrontController {
    
    /**
     * Patient adds a new dentist
     */
	public function invite_new_dentist($locale=null) {

        if(Request::isMethod('post')) {

            if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
                request()->merge([
                    'website' => 'http://'.request('website')
                ]);
            }

            $validator = Validator::make(Request::all(), [
                'mode' => array('required', 'in:dentist,clinic'),
                'name' => array('required', 'min:3'),
                'email' => array('required', 'email', 'unique:users,email'),
                'address' =>  array('required', 'string'),
                'website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
                'phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                foreach ($msg as $field => $errors) {
                    if($field == 'mode') {
                        $ret['messages']['mode'] = trans('trp.invite.mode.error');
                    } else {
                        $ret['messages'][$field] = implode(', ', $errors);
                    }                    
                }

                return Response::json( $ret );
            } else {

                if(User::validateName(Request::input('name')) == true) {
                    $ret = array(
                        'success' => false,
                        'messages' =>[
                            'name' => trans('trp.invite.taken-name')
                        ]
                    );
                    return Response::json( $ret );
                }

                if(User::validateLatin(Request::input('name')) == false) {
                    return Response::json( [
                        'success' => false, 
                        'messages' => [
                            'name' => trans('trp.common.invalid-name')
                        ]
                    ] );
                }

                if(User::validateEmail(Request::input('email')) == true) {
                    $ret = array(
                        'success' => false,
                        'messages' =>[
                            'email' => trans('trp.invite.invalid-email')
                        ]
                    );
                    return Response::json( $ret );
                }

                $info = User::validateAddress( Country::find(request('country_id'))->name, request('address') );
                if(empty($info)) {
                    $ret = array(
                        'success' => false,
                        'messages' => array(
                            'address' => trans('trp.common.invalid-address')
                        )
                    );
                }

                if(User::validateWebsite(Request::input('website')) == true) {
                    $ret = array(
                        'success' => false,
                        'messages' =>[
                            'website' => trans('trp.invite.invalid-website')
                        ]
                    );
                    return Response::json( $ret );
                }

                if (!empty($this->user) && !empty($this->user->country_id) && Request::input('country_id') != $this->user->country_id) {
                    $ret = array(
                        'success' => false,
                        'messages' =>[
                            'address' => trans('trp.invite.invalid-address')
                        ]
                    );
                    return Response::json( $ret );
                } else if (!empty($this->country_id) && Request::input('country_id') != $this->country_id) {
                    $ret = array(
                        'success' => false,
                        'messages' =>[
                            'address' => trans('trp.invite.invalid-address')
                        ]
                    );
                    return Response::json( $ret );
                }
                
                $newdentist = new User;
                $newdentist->name = Request::input('name');
                $newdentist->email = Request::input('email');
                $newdentist->country_id = Request::input('country_id');
                $newdentist->phone = Request::input('phone');
                $newdentist->platform = 'trp';
                $newdentist->status = 'added_new';
                $newdentist->ownership = 'unverified';
                $newdentist->address = Request::input('address');
                $newdentist->website = Request::input('website');
                $newdentist->is_dentist = 1;
                $newdentist->is_clinic = Request::input('mode')=='clinic' ? 1 : 0;
                $newdentist->invited_by = $this->user ? $this->user->id : 0;
                $newdentist->invited_from_form = true;

                $newdentist->save();

                session(['invite_new_dentist' => $newdentist->id]);
                
                if(!empty($this->user)) {
                    $mtext = 'Patient - '.$this->user->name.' invited his dentist to register 
Link to patients\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$this->user->id.'
Link to invited dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$newdentist->id;

                    $patient = $this->user;

                    Mail::raw($mtext, function ($message) use ($patient) {
                        $sender = config('mail.from.address');
                        $sender_name = config('mail.from.name');

                        $message->from($sender, $sender_name);
                        $message->to( 'ali.hashem@dentacoin.com' );
                        $message->to( 'betina.bogdanova@dentacoin.com' );
                        $message->replyTo($patient->email, $patient->name);
                        $message->subject('Patient invites dentist to register');
                    });
                } else {
                    $mtext = 'Not registered patient invited his dentist to register
Link to invited dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$newdentist->id;

                    Mail::raw($mtext, function ($message) {
                        $sender = config('mail.from.address');
                        $sender_name = config('mail.from.name');

                        $message->from($sender, $sender_name);
                        $message->to( 'ali.hashem@dentacoin.com' );
                        $message->to( 'betina.bogdanova@dentacoin.com' );
                        $message->subject('Not registered patient invites dentist to register');
                    });
                }

                return Response::json( [
                    'success' => true,
                	'message' => trans('trp.page.invite.success', ['name' => $newdentist->name]),
                    'dentist_name' => $newdentist->name,
                ] );
            }
        }
    }
}