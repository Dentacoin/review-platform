<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\Review;
use App\Models\User;
use App\Models\DentistClaim;
use App\Models\IncompleteRegistration;
use App\Models\DentistTestimonial;
use CArbon\Carbon;
use App\Models\UserStrength;
use App\Models\LeadMagnet;

use App;
use Mail;
use Response;
use Request;
use Cookie;
use Validator;

class IndexController extends FrontController
{

	public function home($locale=null) {
		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		if(!empty($this->user) && $this->user->is_dentist) {
			return redirect( $this->user->getLink() );
		}

		if(Request::isMethod('post')) {

			$validator = Validator::make(Request::all(), [
	            'dentists-city' => array('required'),
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

				$info = \GoogleMaps::load('geocoding')
		        ->setParam ([
		            'address'    => Request::input('dentists-city'),
		        ])
		        ->get();

		        if (!empty($info)) {
	        		$info = json_decode($info);
	        		$components = $info->results[0]->address_components;

		        	$ret = [];

			        $country_fields = [
			            'country',
			        ];

			        foreach ($country_fields as $sf) {
			            if( empty($ret['country_name']) ) {
			                foreach ($components as $ac) {
			                    if( in_array($sf, $ac->types) ) {
			                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
			                        $cname = iconv('ASCII', 'UTF-8', $cname);
			                        $ret['country_name'] = $cname;
			                        break;
			                    }
			                }
			            } else {
			                break;
			            }
			        }

			        $city_fields = [
			            'locality',
			        ];

			        foreach ($city_fields as $cf) {
			            if( empty($ret['city_name']) ) {
			                foreach ($components as $ac) {
			                    if( in_array($cf, $ac->types) ) {
			                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
			                        $cname = iconv('ASCII', 'UTF-8', $cname);
			                        $ret['city_name'] = $cname;
			                        break;
			                    }
			                }
			            } else {
			                break;
			            }
			        }

			        if (!empty($ret['country_name']) && !empty($ret['city_name'])) {
			        	
			        	$final_info = User::validateAddress($ret['country_name'], $ret['city_name']);

				        if(!empty(Request::input('change-city'))) {
				        	$this->user->address = $ret['city_name'].','.$ret['country_name'];
							$this->user->save();
				        }

				        Cookie::queue('dentists_city', json_encode($final_info), 60*24*31);

			            return Response::json( ['success' => true] );
			        } else {
			        	return Response::json( ['success' => false] );
			        }
		        }
			}
		}


		$city_cookie = json_decode(Cookie::get('dentists_city'), true);
		//dd($city_cookie);

		$featured = User::where('is_dentist', 1)->with('country.translations')->whereIn('status', ['approved','added_approved','admin_imported'])->orderBy('avg_rating', 'DESC');
		$homeDentists = collect();

		if (!empty($city_cookie)) {
			if( $homeDentists->count() < 12 ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('city_name', 'LIKE', $city_cookie['city_name'])->take( 12 - $homeDentists->count() )->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('state_name', 'LIKE', $city_cookie['state_name'])->take( 12 - $homeDentists->count() )->whereNotIn('id', $homeDentists->pluck('id')->toArray())->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 ) {
				$country_n = $city_cookie['country_name'];
				$country = Country::with('translations')->whereHas('translations', function ($query) use ($country_n) {
	                $query->where('name', 'LIKE', $country_n);
	            })->first();

				$addMore = clone $featured;
				$addMore = $addMore->where('country_id', $country->id)->take( 12 - $homeDentists->count() )->whereNotIn('id', $homeDentists->pluck('id')->toArray())->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

		} else {

			if( !empty($this->user) ) {
				if( $homeDentists->count() < 12 && $this->user->city_name ) {
					$addMore = clone $featured;
					$addMore = $addMore->where('city_name', 'LIKE', $this->user->city_name)->take( 12 - $homeDentists->count() )->get();
					$homeDentists = $homeDentists->concat($addMore);
				}

				if( $homeDentists->count() < 12 && $this->user->state_name ) {
					$addMore = clone $featured;
					$addMore = $addMore->where('state_name', 'LIKE', $this->user->state_name)->take( 12 - $homeDentists->count() )->whereNotIn('id', $homeDentists->pluck('id')->toArray())->get();
					$homeDentists = $homeDentists->concat($addMore);
				}

				if( $homeDentists->count() < 12 && $this->user->country_id ) {
					$addMore = clone $featured;
					$addMore = $addMore->where('country_id', $this->user->country_id)->take( 12 - $homeDentists->count() )->whereNotIn('id', $homeDentists->pluck('id')->toArray())->get();
					$homeDentists = $homeDentists->concat($addMore);
				}

			} else {

				// if( $homeDentists->count() < 12 && $this->city_id ) {
				// 	$addMore = clone $featured;
				// 	$addMore = $addMore->where('city_id', $this->city_id)->take( 12 - $homeDentists->count() )->get();
				// 	$homeDentists = $homeDentists->concat($addMore);
				// }

				if( $homeDentists->count() < 12 && $this->country_id ) {
					$addMore = clone $featured;
					$addMore = $addMore->where('country_id', $this->country_id)->take( 12 - $homeDentists->count() )->get();
					$homeDentists = $homeDentists->concat($addMore);				
				}
			}
		}

		if( $homeDentists->count() <= 2) {
			$addMore = clone $featured;
			$addMore = $addMore->take( 12 - $homeDentists->count() )->get();
			$homeDentists = $homeDentists->concat($addMore);	
		}

		$social_image = url('img-trp/reviews-social-new-image.png');

		$strength_arr = null;
		$completed_strength = null;
		if ($this->user) {
			$strength_arr = UserStrength::getStrengthPlatform('trp', $this->user);
			$completed_strength = $this->user->getStrengthCompleted('trp');
		}

		if (!empty(Cookie::get('functionality_cookies'))) {
			$current_city = \GeoIP::getLocation()->city;
			$current_country = \GeoIP::getLocation()->country;
		} else {
			$current_city = null;
			$current_country = null;
		}
		

		$params = array(
			'strength_arr' => $strength_arr,
			'completed_strength' => $completed_strength,
			'featured' => $homeDentists,
			'city_cookie' => $city_cookie,
			'social_image' => $social_image,
			'current_city' => $current_city,
			'current_country' => $current_country,
			'js' => [
				'index.js',
                'search.js',
                'address.js'
			],
			'jscdn' => [
				'https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js'
			],
			'csscdn' => [
				'https://unpkg.com/flickity@2/dist/flickity.min.css'
			]
        );

		if (!empty($this->user)) {
			$params['extra_body_class'] = 'strength-pb';
		}

		return $this->ShowView('index', $params);	
	}


	public function index_down($locale=null) {
		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		if(!empty($this->user) && $this->user->is_dentist) {
			return redirect( $this->user->getLink() );
		}

		$strength_arr = null;
		$completed_strength = null;
		if ($this->user) {
			$strength_arr = UserStrength::getStrengthPlatform('trp', $this->user);
			$completed_strength = $this->user->getStrengthCompleted('trp');
		}

		$params = array(
			'strength_arr' => $strength_arr,
			'completed_strength' => $completed_strength,
        );

		if (!empty($this->user)) {
			$params['extra_body_class'] = 'strength-pb';
		}

		return $this->ShowView('index-down', $params);	
	}


	public function unsubscribe ($locale=null, $session_id=null, $hash=null) {
		return $this->dentist($locale, $session_id, $hash, true);
	}

	public function dentist($locale=null, $session_id=null, $hash=null, $unsubscribe = false) {
		
		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

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

    	$testimonials = DentistTestimonial::orderBy('id', 'desc')->get();

		$social_image = url('img-trp/welcome-dentist-social-image.jpg');

		return $this->ShowView('index-dentist', array(
			//'extra_body_class' => 'white-header',
			'js' => [
				'address.js',
				'index-dentist.js'
			],
			'css' => [
				'trp-index-dentist.css'
			],
			'regData' => $regData,
			'unsubscribed' => $unsubscribed,
			'testimonials' => $testimonials,
			'social_image' => $social_image,
			'jscdn' => [
				'https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js'
			],
			'csscdn' => [
				'https://unpkg.com/flickity@2/dist/flickity.min.css'
			]
        ));	
	}

	public function claim ($locale=null, $id) {
		$user = User::find($id);

        if (!$user || ($user->status != 'added_approved' && $user->status != 'admin_imported')) {
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

	            $fromm = !empty(Request::input('email')) ? 'from site' : 'from mail';

	            $claim = new DentistClaim;
	            $claim->dentist_id = $user->id;
	            $claim->name = Request::input('name');
	            $claim->email = Request::input('email') ? Request::input('email') : $user->email;
	            $claim->phone = Request::input('phone');
	            $claim->password = bcrypt(Request::input('password'));
	            $claim->job = Request::input('job');
	            $claim->explain_related = Request::input('explain-related');
	            $claim->status = 'waiting';
	            $claim->from_mail = !empty(Request::input('email')) ? false : true;
	            $claim->save();


	            $mtext = 'Dentist claimed his profile '.$fromm.'<br/>
Name: '.$claim->name.' <br/>
Phone: '.$claim->phone.' <br/>
Email: '.$claim->email.' <br/>
Job position: '.$claim->job.' <br/>
Explain how dentist is related to this office: '.$claim->explain_related.' <br/>
Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$user->id;

				Mail::send([], [], function ($message) use ($mtext, $user) {
		            $sender = config('mail.from.address');
		            $sender_name = config('mail.from.name');

		            $message->from($sender, $sender_name);
                    $message->to( 'ali.hashem@dentacoin.com' );
                    $message->to( 'betina.bogdanova@dentacoin.com' );
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

	public function lead_magnet_step1($locale=null) {

		if (request('website') && mb_strpos(mb_strtolower(request('website')), 'http') !== 0) {
            request()->merge([
                'website' => 'http://'.request('website')
            ]);
        }

		$validator = Validator::make(Request::all(), [
            'firstname' => array('required', 'min:3'),
            'country' => array('required', 'exists:countries,id'),
            'website' =>  array('required', 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'),
            'email' => array('required', 'email'),
            'agree' =>  array('required', 'accepted'),
        ]);

        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                if($field=='website') {
                    $ret['messages'][$field] = trans('trp.common.invalid-website');
                } else {
                    $ret['messages'][$field] = implode(', ', $errors);
                }
            }

            return Response::json( $ret );
        } else {
			$new_lead = new LeadMagnet;
			$new_lead->name = Request::input('firstname');
			$new_lead->email = Request::input('email');
			$new_lead->website = Request::input('website');
			$new_lead->country_id = Request::input('country');
			$new_lead->save();

        	$sess = [
	            'lead_magnet' => [
	            	'answers' => [
		            	'firstname' => Request::input('firstname'),
		            	'country' => Request::input('country'),
		            	'website' => Request::input('website'),
		            	'email' => Request::input('email'),
	            	],
	            	'id' => $new_lead->id,
	            ]
	        ];


	        session($sess);

            return Response::json([
                'success' => true,
            ] );

        }
	}

	public function lead_magnet_step2($locale=null) {

		$points = [
			'answer-2' => [
				'1' => 1,
				'2' => 1,
				'3' => 1,
				'4' => 2,
				'5' => 3,
				'6' => 0,
			], 
			'answer-3' => [
				'1' => 1,
				'2' => 3,
				'3' => 2,
				'4' => 0,
			],  
			'answer-4' => [
				'1' => 3,
				'2' => 2,
				'3' => 1,
			], 
			'answer-5' => [
				'1' => 3,
				'2' => 2,
				'3' => 1,
				'4' => 0,
			],  

		];

		$total_points = 0;

		foreach (Request::all() as $key => $value) {
			if (isset($points[$key])) {

				if ($key == 'answer-3') {
					foreach ($value as $k => $v) {
						$total_points += $points[$key][$v];
					}
				} else {
					$total_points += $points[$key][$value];
				}
				
			}
		}	
		$review_collection = $points['answer-2'][Request::input('answer-2')] + (!empty(Request::input('answer-4')) ? $points['answer-4'][Request::input('answer-4')] : 0);
		$review_volume = !empty(Request::input('answer-4')) ? $points['answer-4'][Request::input('answer-4')] : 0;
		$impact = $points['answer-5'][Request::input('answer-5')];

		foreach (Request::input('answer-3') as $key => $value) {
			$review_collection += $points['answer-3'][$value];
			$review_volume += $points['answer-3'][$value];
			$impact += $points['answer-3'][$value];
		}

		$session = session('lead_magnet');

		$session['answers']['answer-1'] = Request::input('answer-1');
		$session['answers']['answer-2'] = Request::input('answer-2');
		$session['answers']['answer-3'] = Request::input('answer-3');
		$session['answers']['answer-4'] = !empty(Request::input('answer-4')) ? Request::input('answer-4') : '';
		$session['answers']['answer-5'] = Request::input('answer-5');

		$session['points'] = [
			'total_points' => $total_points,
			'review_collection' => $review_collection,
			'review_volume' => $review_volume,
			'impact' => $impact,
		];

		$answers_arr = [
			1 => Request::input('answer-1'),
			2 => Request::input('answer-2'),
			3 => Request::input('answer-3'),
			4 => !empty(Request::input('answer-4')) ? Request::input('answer-4') : '',
			5 => Request::input('answer-5'),
		];

		$lead = LeadMagnet::find(session('lead_magnet')['id']);

		if (!empty($lead)) {
			$lead->answers = json_encode($answers_arr);
			$lead->total = round(($total_points / 15) * 100);
			$lead->review_collection = round(($review_collection / 12) * 100);
			$lead->review_volume = round(($review_volume / 9) * 100);
			$lead->impact = round(($impact / 9) * 100);
			$lead->save();
		}


		session([
			'lead_magnet' => $session
		]);
		
		return Response::json([
            'success' => true,
            'url' => getLangUrl('lead-magnet-results')
        ] );
	}

    public function lead_magnet_results($locale=null) {

    	if (!empty(session('lead_magnet')) && !empty(session('lead_magnet')['points']) && empty($this->user)) {

    		$country_id = $this->country_id;
    		$to_month = Carbon::now()->modify('-0 months');
        	$from_month = Carbon::now()->modify('-1 months');

    		if(!empty($country_id)) {


	            $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
	                $query->where('country_id', $country_id);
	            })
	            ->where('created_at', '>=', $from_month)
	        	->where('created_at', '<=', $to_month)
	        	->get();

	            $country_rating = 0;
	            foreach ($country_reviews as $c_review) {
	                $country_rating += $c_review->rating;
	            }

	            if (!empty($country_rating) && !empty($country_reviews->count())) {
		            $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);
		            $country_reviews = $country_reviews->count();
	            } else {
	            	$country_reviews = null;
	            }

    		} else {
    			$country_reviews = null;
    		}


            if (empty($country_reviews)) {
            	$country_reviews = Review::whereHas('user')
	            ->where('created_at', '>=', $from_month)
	        	->where('created_at', '<=', $to_month)
	        	->get();

	            $country_rating = 0;
	            foreach ($country_reviews as $c_review) {
	                $country_rating += $c_review->rating;
	            }

	            $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);
	            $country_reviews = $country_reviews->count();
            }

            if ($country_reviews <= 15) {
            	$country_reviews = 16;
            }

    		$total_points = session('lead_magnet')['points']['total_points'];
    		$review_collection = session('lead_magnet')['points']['review_collection'];
    		$review_volume = session('lead_magnet')['points']['review_volume'];
    		$impact = session('lead_magnet')['points']['impact'];
    		$first_answer = session('lead_magnet')['answers']['answer-1'];

	        return $this->ShowView('lead-magnet', array(
	        	'total_points' => $total_points,
	        	'review_collection' => $review_collection,
	        	'review_volume' => $review_volume,
	        	'impact' => $impact,
	        	'avg_country_rating' => $avg_country_rating,
	        	'country_reviews' => $country_reviews,
	        	'first_answer' => $first_answer
	        ));
    	} else {
    		return redirect( getLangUrl('welcome-dentist') );
    	}

    }

    public function lead_magnet_session($locale=null) {

    	if (!empty(session('lead_magnet')) && !empty(session('lead_magnet')['points'])) {
    		return Response::json([
	            'session' => true,
	            'url' => getLangUrl('lead-magnet-results')
	        ] );
    	} else {
    		return Response::json([
	            'session' => false,
	        ] );
    	}
    }

}