<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\DentistTestimonial;
use App\Models\AnonymousUser;
use App\Models\UserStrength;
use App\Models\LeadMagnet;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\Review;
use App\Models\User;
use App\Models\City;

use Carbon\Carbon;

use Validator;
use Response;
use Request;

class IndexController extends FrontController {

    /**
     * Index page view for not logged users and for patients
     */
	public function home($locale=null) {
		if(!empty($this->user)) {
			if($this->user->isBanned('trp')) {
				return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
			}
			if($this->user->is_dentist) {
				return redirect( $this->user->getLink() );
			}
		}

		//reset search results
		session([
			'results-url' => null
		]);

		//get all dentists and clinics
		$featured = User::where('is_dentist', 1)
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNull('self_deleted')
		->has('country')
		->with('country')
		->orderBy('avg_rating', 'DESC');

		$homeDentists = collect();

		$city_id = null;
		if(!empty($this->city_id)) {
			$city_id = City::find($this->city_id);
		}

		if( !empty($this->user) ) {
			if( $homeDentists->count() < 12 && $this->user->city_name ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('city_name', 'LIKE', $this->user->city_name)
				->take( 12 - $homeDentists->count() )
				->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 && $this->user->state_name ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('state_name', 'LIKE', $this->user->state_name)
				->whereNotIn('id', $homeDentists->pluck('id')->toArray())
				->take( 12 - $homeDentists->count() )
				->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 && $this->user->country_id ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('country_id', $this->user->country_id)
				->whereNotIn('id', $homeDentists->pluck('id')->toArray())
				->take( 12 - $homeDentists->count() )
				->get();
				$homeDentists = $homeDentists->concat($addMore);
			}
		}

		if( $homeDentists->count() < 12 && $city_id ) {
			$addMore = clone $featured;
			$addMore = $addMore->where('city_name', 'LIKE', $city_id->name)
			->whereNotIn('id', $homeDentists->pluck('id')->toArray())
			->take( 12 - $homeDentists->count() )
			->get();
			$homeDentists = $homeDentists->concat($addMore);
		}

		if( $homeDentists->count() < 12 && $this->country_id ) {
			$addMore = clone $featured;
			$addMore = $addMore->where('country_id', $this->country_id)
			->whereNotIn('id', $homeDentists->pluck('id')->toArray())
			->take( 12 - $homeDentists->count() )
			->get();
			$homeDentists = $homeDentists->concat($addMore);				
		}

		if( $homeDentists->count() <= 2) {
			$addMore = clone $featured;
			$addMore = $addMore->take( 12 - $homeDentists->count() )->get();
			$homeDentists = $homeDentists->concat($addMore);	
		}

		$strength_arr = null;
		$completed_strength = null;
		// if ($this->user) {
		// 	$strength_arr = UserStrength::getStrengthPlatform('trp', $this->user);
		// 	$completed_strength = $this->user->getStrengthCompleted('trp');
		// }

		$seos = PageSeo::find(20);

		//countries for search bar countries dropdown
		$countriesAlphabetically = [];
        foreach(Country::has('dentists')->with(['dentists','translations'])->get() as $item) {
            $countriesAlphabetically[$item->name[0]][] = [
				'name' => $item->name,
				'dentist_count' => $item->dentists->count(),
				'id' => $item->id,
				'code' => $item->code,
			];
        }

		$params = [
			'countries' => !empty($this->user) ? Country::with('translations')->get() : [], //for add new dentist form
			'strength_arr' => $strength_arr,
			'completed_strength' => $completed_strength,
			'featured' => $homeDentists,
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'countriesAlphabetically' => $countriesAlphabetically,
			'js' => [
				'index.js',
                'search-form.js',
			],
			'css' => [
                'trp-index.css',
			]
		];

		// if (!empty($this->user)) {
		// 	$params['extra_body_class'] = 'strength-pb';
		// 	$params['css'][] = 'flickity.min.css'; //because of strength scale
		// 	$params['js'][] = '../js/flickity.min.js'; //because of strength scale
		// }

		return $this->ShowView('index', $params);	
	}

	/**
     * bottom content of the index page
     */
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

		$params = [
			'strength_arr' => $strength_arr,
			'completed_strength' => $completed_strength,
		];

		if (!empty($this->user)) {
			$params['extra_body_class'] = 'strength-pb';
		}

		return $this->ShowView('index-down', $params);	
	}

	/**
     * Welcome dentist page view
     */
	public function dentist($locale=null, $session_id=null, $hash=null, $unsubscribe = false, $claim_id = false) {
		
		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		if(!empty($this->user)) {
			return redirect( getLangUrl('/') );
		}

		$seos = PageSeo::find(23);

		$claim_user = !empty($claim_id) ? User::find($claim_id) : null;

		return $this->ShowView('welcome-dentist', [
			'extra_body_class' => 'white-header',
			'claim_user' => $claim_user,
			'js' => [
				'address.js',
				'welcome-dentist.js',
			],
			'css' => [
				'trp-welcome-dentist.css',
			],
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
		]);	
	}

	/**
     * bottom content of the welcome dentist page
     */
	public function index_dentist_down($locale=null) {
		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		if(!empty($this->user)) {
			return redirect( getLangUrl('/') );
		}

    	$testimonials = DentistTestimonial::with('translations')
		->orderBy('order_dentist', 'asc')
		->get();

		return $this->ShowView('welcome-dentist-down', array(
			'testimonials' => $testimonials,
        ));	
	}

	/**
     * Old claim dentists profile link redirecting to the correct one
     */
	public function claim($locale=null, $id) {

		$user = User::find($id);

		if(!empty($user)) {
			return redirect( getLangUrl( 'dentist/'.$user->slug.'/claim/'.$user->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query([
				'popup'=>'claim-popup', 
				'utm_content' => '1'
			]), 301 );
		} else {
			return redirect( getLangUrl('page-not-found') );
		}
	}

	/**
     * Lead magnet form user details
     */
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

			$existing_user = User::where('email', 'LIKE', Request::Input('email'))->first();
			if( empty($existing_user)) {				
				$existing_anonymous = AnonymousUser::where('email', 'LIKE', Request::Input('email'))->first();

	            if(empty($existing_anonymous)) {
	                $new_anonymous_user = new AnonymousUser;
	                $new_anonymous_user->email = Request::Input('email');
	                $new_anonymous_user->save();
	            }
			}

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
            ]);
        }
	}

	/**
     * Lead magnet form answers
     */
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
		$review_collection = intval($points['answer-2'][Request::input('answer-2')]) + (!empty(Request::input('answer-4')) ? intval($points['answer-4'][Request::input('answer-4')]) : 0);
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
			$lead->total = $total_points ? round(($total_points / 15) * 100) : 0;
			$lead->review_collection = $review_collection ? round(($review_collection / 12) * 100) : 0;
			$lead->review_volume = $review_volume ? round(($review_volume / 9) * 100) : 0;
			$lead->impact = $impact ? round(($impact / 9)  * 100) : 0;
			$lead->save();
		}

		session([
			'lead_magnet' => $session
		]);
		
		return Response::json([
            'success' => true,
            'total_points' => $lead->total,
        ]);
	}

	/**
     * Lead magnet survey page
     */
    public function leadMagnetSurvey($locale=null) {

    	if (!empty(session('lead_magnet')) && !empty(session('lead_magnet')['points'])) {
			return redirect( getLangUrl('review-score-results'));
    	} else {
			if((!empty($this->user) && $this->user->is_dentist) || empty($this->user)) {

				$seos = PageSeo::find(25);

				return $this->ShowView('lead-magnet', [
					'social_image' => $seos->getImageUrl(),
					'seo_title' => $seos->seo_title,
					'seo_description' => $seos->seo_description,
					'social_title' => $seos->social_title,
					'social_description' => $seos->social_description,
					'countries' => Country::with('translations')->get(),
					'css' => [
						'trp-popup-lead-magnet.css',
						'flickity.min.css'
					],
					'js' => [
						'../js/flickity.min.js',
						'lead-magnet.js'
					],
				]);
			} else {
				if(!empty($this->user)) {
					return redirect( getLangUrl('/') );
				} else {
					return redirect( getLangUrl('welcome-dentist') );
				}   
			}
    	}
    }

	/**
     * Lead magnet results page view
     */
    public function leadMagnetResults($locale=null) {

    	if (!empty(session('lead_magnet')) && !empty(session('lead_magnet')['points'])) {

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
		            $avg_country_rating = number_format($country_rating / $country_reviews->count(), 1);
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

	            $avg_country_rating = $country_rating && $country_reviews->count() ? number_format($country_rating / $country_reviews->count(), 1) : 0;
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

    		$seos = PageSeo::find(25);

	        return $this->ShowView('lead-magnet', [
	        	'total_points' => $total_points,
	        	'review_collection' => $review_collection,
	        	'review_volume' => $review_volume,
	        	'impact' => $impact,
	        	'avg_country_rating' => $avg_country_rating,
	        	'country_reviews' => $country_reviews,
	        	'first_answer' => $first_answer,
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	            'css' => [
	            	'trp-lead-magnet.css'
	            ],
			]);
    	} else {
			if((!empty($this->user) && $this->user->is_dentist) || empty($this->user)) {
				return redirect( getLangUrl('review-score-test'));
			} else {
				if(!empty($this->user)) {
					return redirect( getLangUrl('/') );
				} else {
					return redirect( getLangUrl('welcome-dentist') );
				}   
			}
    	}
    }

    /**
     * get lead magnet resutls
     */
    public function lead_magnet_session($locale=null) {

    	if (!empty(session('lead_magnet')) && !empty(session('lead_magnet')['points'])) {
    		return Response::json([
	            'session' => true,
	            'url' => getLangUrl('review-score-results')
	        ]);
    	} else {
    		return Response::json([
	            'session' => false,
	        ]);
    	}
    }

    /**
     * redirect lead magnet to new link
     */
    public function redirectPage($locale=null) {
    	return redirect( getLangUrl('review-score-test'), 301);
    }

    /**
     * get popups content
     */
	public function getPopup() {

		//dd(request('id'));
		if(request('id') == 'popup-share') {

			return $this->ShowView('popups/share');

		} else if(request('id') == 'verification-popup' && empty($this->user)) {

			return $this->ShowView('popups/dentist-verification');

		} else if(request('id') == 'failed-popup' && empty($this->user)) {
			
			return $this->ShowView('popups/failed-reg-login');

		} else if(request('id') == 'popup-existing-dentist' && empty($this->user)) {
			
			return $this->ShowView('popups/existing-team-dentist');

		}
	}

	public function removeBanner() {
		session([
			'withoutBanner' => true
		]);
	}
}