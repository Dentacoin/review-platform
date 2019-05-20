<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use Validator;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\Country;
use Auth;


class AddDentistController extends FrontController
{


    public function invited_dentist_registration($locale=null, $id) {

        $user = User::find($id);

        if (!$user || $user->status != 'added_approved') {
            return redirect( getLangUrl('/') );
        }

        return $this->ShowView('invited-dentist-registration');
    }
    
	public function invite_new_dentist($locale=null) {

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
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $info = User::validateAddress( Country::find(request('country_id'))->name, request('address') );
            if(empty($info)) {
                $ret = array(
                    'success' => false,
                    'messages' => array(
                        'address' => trans('trp.common.invalid-address')
                    )
                );
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
                        'email' => trans('trp.common.invalid-email')
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
            $newdentist->address = Request::input('address');
            $newdentist->website = Request::input('website');
            $newdentist->is_dentist = 1;
            $newdentist->is_clinic = Request::input('mode')=='clinic' ? 1 : 0;
            $newdentist->invited_by = $this->user->id;

            $newdentist->save();


//             $mtext = $this->user->name.' invited his dentist to register<br/>
// Link to patients\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$this->user->id.'
// Link to invited dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$newdentist->id;

//             Mail::raw($mtext, function ($message) use ($newdentist) {
//                 $receiver = 'ali.hashem@dentacoin.com';
//                 $sender = config('mail.from.address');
//                 $sender_name = config('mail.from.name');

//                 $message->from($sender, $sender_name);
//                 $message->to( $receiver );
//                 //$message->to( 'dokinator@gmail.com' );
//                 $message->replyTo($receiver, $newdentist->getName());
//                 $message->subject('Patient invites dentist to register');
//             });


            return Response::json( [
                'success' => true,
            	'message' => trans('trp.page.invite.success', ['name' => $newdentist->name]),
            ] );

        }

    }
}