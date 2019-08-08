<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use App\Models\DentistClaim;
use App\Models\IncompleteRegistration;
use CArbon\Carbon;

use App;
use Mail;
use Response;
use Request;
use Validator;

class IndexController extends FrontController
{

	public function home($locale=null) {
		if(!empty($this->user) && $this->user->is_dentist) {
			return redirect( $this->user->getLink() );
		}

		$featured = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved'])->orderBy('avg_rating', 'DESC');
		$refined = clone $featured;
		if( !empty($this->user) ) {
			if( $this->user->country_id ) {
				$refined->where('country_id', $this->user->country_id);
				
				if( $this->user->state_name ) {
					$refined->where('state_name', 'LIKE', $this->user->state_name);

					if( $this->user->city_name ) {
						$refined->where('city_name', 'LIKE', $this->user->city_name);
					}
				}
			}
		} else {
			if( $this->country_id ) {
				$refined->where('country_id', $this->country_id);
				if( $this->city_id ) {
					$refined->where('city_id', $this->city_id);
				}
			}
		}

		$refined = $refined->take(12)->get();

		if($refined->isEmpty()) {
			$refined = clone $featured;
			if( !empty($this->user) ) {
				if( $this->user->country_id ) {
					$refined->where('country_id', $this->user->country_id);
				}
			} else {
				if( $this->country_id ) {
					$refined->where('country_id', $this->country_id);
				}
			}
			$refined = $refined->take(12)->get();
		}


		if($refined->isEmpty()) {
			$refined = clone $featured;
			$refined = $refined->take(12)->get();
		}

		$params = array(
            'countries' => Country::get(),
			'featured' => $refined,
			'js' => [
				'index.js',
                'search.js',
                'address.js'
			],
			'jscdn' => [
				'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
			]
        );

		if (!empty($this->user)) {
			$params['extra_body_class'] = 'strength-pb';
		}

		return $this->ShowView('index', $params);	
	}


	public function unsubscribe ($locale=null, $session_id=null, $hash=null) {
		return $this->dentist($locale, $session_id, $hash, true);
	}

	public function dentist($locale=null, $session_id=null, $hash=null, $unsubscribe = false) {

		if(!empty($this->user)) {
			return redirect( getLangUrl('/') );
		}

		$unsubscribed = false;
		$regData = null;
        if($session_id && $hash) {
        	$regData = IncompleteRegistration::find($session_id);
        	if(!empty($regData) && $hash!=md5($session_id.env('SALT_INVITE'))) {
        		$regData = null;
        	}

        	if($regData && $unsubscribe) {
        		$regData->unsubscribed = true;
        		$regData->save();
        		$regData = null;
        		$unsubscribed = true;
        	}
        }

        if(empty($regData) && session('incomplete-registration')) {
        	$regData = IncompleteRegistration::find(session('incomplete-registration'));
        }


		return $this->ShowView('index-dentist', array(
			'extra_body_class' => 'white-header',
			'js' => [
				'index-dentist.js'
			],
			'regData' => $regData,
			'unsubscribed' => $unsubscribed,
        ));	
	}

	public function gdpr($locale=null) {

		$this->user->gdpr_privacy = true;
		$this->user->save();

		return redirect( getLangUrl('/') );
	}

	public function claim ($locale=null, $id) {
		$user = User::find($id);

        if (!$user || $user->status != 'added_approved') {
            return redirect( getLangUrl('/') );
        }

		if(Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), [
	            'name' => array('required', 'min:3'),
	            'email' => 'sometimes|required|email',
	            'phone' =>  array('required', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
	            'job' =>  array('required', 'string'),
	            'explain-related' =>  array('required'),
                'password' => array('required', 'min:6'),
            	'password-repeat' => 'required|same:password',
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

	            if(User::validateLatin(Request::input('name')) == false) {
	                return Response::json( [
	                    'success' => false, 
	                    'messages' => [
	                        'name' => trans('trp.common.invalid-name')
	                    ]
	                ] );
	            }

	            $claim = new DentistClaim;
	            $claim->dentist_id = $user->id;
	            $claim->name = Request::input('name');
	            $claim->email = Request::input('email') ? Request::input('email') : $user->email;
	            $claim->phone = Request::input('phone');
	            $claim->password = bcrypt(Request::input('password'));
	            $claim->job = Request::input('job');
	            $claim->explain_related = Request::input('explain-related');
	            $claim->status = 'waiting';
	            $claim->save();


	            $mtext = 'Dentist claimed his profile<br/>
Name: '.Request::input('name').' <br/>
Phone: '.Request::input('phone').' <br/>
Email: '.Request::input('email') ? Request::input('email') : $user->email.' <br/>
Job position: '.Request::input('job').' <br/>
Explain how dentist is related to this office: '.Request::input('explain-related').' <br/>
Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$user->id;

				Mail::send([], [], function ($message) use ($mtext, $user) {
					$receiver = 'ali.hashem@dentacoin.com';
					//$receiver = 'gergana@youpluswe.com';
		            $sender = config('mail.from.address');
		            $sender_name = config('mail.from.name');

		            $message->from($sender, $sender_name);
		            $message->to( $receiver ); //$sender
		            //$message->to( 'dokinator@gmail.com' );
		            $message->replyTo($user->email, $user->getName());
		            $message->subject('Invited Dentist Claimed His Profile');
		            $message->setBody($mtext, 'text/html'); // for HTML rich messages
		        });

	            return Response::json( [
	                'success' => true,
	            ] );
	        }
        }

        return $this->dentist($locale);

	}

	public function want_to_invite_dentist($locale=null) {

		$sess = [
            'want_to_invite_dentist' => true,
        ];
        session($sess);

	}	

}