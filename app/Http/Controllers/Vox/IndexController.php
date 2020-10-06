<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use App\Models\StopTransaction;
use App\Models\Recommendation;
use App\Models\UserStrength;
use App\Models\VoxCategory;
use App\Models\UserLogin;
use App\Models\VoxAnswer;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Cookie;
use Mail;
use App;

class IndexController extends FrontController {

	private function getVoxList($slice_from=0) {
		if( $this->user ) {
			$voxes = !empty($this->admin) ? User::getAllVoxes() : $this->user->voxesTargeting();
		} else {
			$voxes = User::getAllVoxes();
		}

		if(!empty($this->user) && $this->user->is_dentist && !$slice_from) {
			$slice_count = 5;
		} else {
			$slice_count = 6;
		}

		$voxList = $voxes->where('type', 'normal')->get();

		$sort = 'featured';
		$voxList = $voxList->sortByDesc(function ($voxlist) use ($sort) {

            if(!empty($voxlist->$sort)) {
                return 100000;
            } else {
                return 10000 - $voxlist->sort_order;
            }
        });
		// $voxList = $voxList->slice($slice_from, $slice_count);

		if ($this->user && !empty($this->user->country_id)) {
			$user = $this->user;
			$restricted_voxes = $voxList->filter(function($vox) use ($user) {
	         	return !empty($vox->country_percentage) && !empty($vox->users_percentage) && array_key_exists($user->country_id, $vox->users_percentage) && $vox->users_percentage[$user->country_id] >= $vox->country_percentage;
	       	});

			$arr = [];

			if($restricted_voxes->count()) {
				foreach ($restricted_voxes as $vl) {
					$has_started_the_survey = VoxAnswer::where('vox_id', $vl->id)->where('user_id', $this->user->id)->first();

		            if(empty($has_started_the_survey)) {
		                $arr[] = $vl->id;
		            }
				}

				if (!empty($arr)) {
					foreach ($arr as $ar) {
						$voxList = $voxList->filter(function($item) use ($ar) {
						    return $item->id != $ar;
						});
					}
				}
			}
		}

		//sort

		// if($voxList->count() < 6) {

		// }

		return $voxList;
	}

	public function survey_list($locale=null) {
		$sorts = [
			// 'featured' => trans('vox.page.home.sort-featured'),
			'newest' => trans('vox.page.home.sort-newest'),
			'popular' => trans('vox.page.home.sort-popular'),
			'reward' => trans('vox.page.home.sort-reward'),
			'duration' => trans('vox.page.home.sort-time'),
		];

        $filters = [
			'untaken' => trans('vox.page.home.sort-untaken'),
			'taken' => trans('vox.page.home.sort-taken'),
			'all' => trans('vox.page.home.sort-all'),
		];

		$voxList = $this->getVoxList();

		// session('voxes-sort');

		$seos = PageSeo::find(2);
        $is_warning_message_shown = StopTransaction::find(1)->show_warning_text;

        $arr = array(
            'is_warning_message_shown' => $is_warning_message_shown,
			'countries' => Country::with('translations')->get(),
			'keywords' => 'paid surveys, online surveys, dentavox, dentavox surveys',
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'sorts' => $sorts,
			'filters' => $filters,
			'taken' => !empty($this->user) ? $this->user->filledVoxes() : null,
        	'voxes' => $voxList,
        	'vox_categories' => VoxCategory::with('translations')->whereHas('voxes')->get()->pluck('name', 'id')->toArray(),
        	'js' => [
        		'home.js',
        	],
        	'css' => [
	        	'vox-index-home.css',
        		'vox-home.css',
        	],
		);

		if (!empty($this->user)) {
			$arr['css'][] = 'select2.min.css';
			$arr['js'][] = 'select2.min.js';
		}

		return $this->ShowVoxView('home', $arr);
	}

	public function getVoxes() {
		$voxList = $this->getVoxList((request('slice') * 6) );

		if($voxList->count()) {
			return $this->ShowVoxView('template-parts.home-voxes', array(
				'voxes' => $voxList,
				'user' => $this->user,
				'taken' => !empty($this->user) ? $this->user->filledVoxes() : null,
	        ));
		} else {
			return '';
		}
	}
	
	public function home($locale=null) {

		$first = Vox::where('type', 'home')->first();
		if(!empty($this->user)) {
			$this->user->checkForWelcomeCompletion();			
		}

		if(!empty($this->user)) {
			
	        if($this->user->is_dentist && $this->user->status != 'approved' && $this->user->status!='added_by_clinic_claimed' && $this->user->status!='added_by_dentist_claimed' && $this->user->status != 'test') {
	            return redirect(getLangUrl('/'));
	        }

	        if($this->user->isBanned('vox')) {
	            return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
	        }

	        return $this->survey_list($locale);
		} else {

			if (Request::input('h1')) {
				$title = ucwords(Request::input('h1'));
			} else {
				$title = nl2br(trans('vox.page.index.title'));
			}

			if (Request::input('h2')) {
				$subtitle = ucfirst(Request::input('h2'));
			} else {
				$subtitle = nl2br(trans('vox.page.index.subtitle'));
			}

			$seos = PageSeo::find(3);
			
			return $this->ShowVoxView('index', array(
				'subtitle' => $subtitle,
				'title' => $title,
				'users_count' => User::getCount('vox'),
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	        	'js' => [
					'all-surveys.js',
	        		'index-new.js',
					'swiper.min.js'
	        	],
	        	'css' => [
	        		'vox-index.css',
	        		'vox-index-home.css',
					'swiper.min.css'
	        	],
	        ));
		}
	}

	public function index_down($locale=null) {
		$featured_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->where('featured', true)->orderBy('sort_order', 'ASC')->take(9)->get();

		if( $featured_voxes->count() < 9 ) {

			$arr_v = [];
			foreach ($featured_voxes as $fv) {
				$arr_v[] = $fv->id;
			}

			$swiper_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->whereNotIn('id', $arr_v)->orderBy('sort_order', 'ASC')->take( 9 - $featured_voxes->count() )->get();

			$featured_voxes = $featured_voxes->concat($swiper_voxes);
		}
		return $this->ShowVoxView('index-down', array(
        	'voxes' => $featured_voxes,
        	'taken' => $this->user ? $this->user->filledVoxes() : [],
        ));	
	}
	
	public function surveys_public($locale=null) {

		if(empty($this->user) || (!empty($this->user) && !$this->user->madeTest(11) ) ) {
			return $this->survey_list($locale);
		} else {
			return redirect(getLangUrl('/'));
		}
	}

	public function welcome($locale=null) {
		$first = Vox::with('questions.translations')->where('type', 'home')->first();

		if ($this->user && $this->user->madeTest($first->id)) {
			return redirect( getLangUrl('page-not-found') );
		} else {

			$has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;

			if($has_test) {
				if($this->user) {
					return redirect( getLangUrl('page-not-found') );
				} else {
					return redirect(getLangUrl('/'));
				}
			}

			$total_questions = $first->questions->count() + 3;
			$seos = PageSeo::find(13);

			return $this->ShowVoxView('welcome', array(
				'vox' => $first,
				'total_questions' => $total_questions,
				'js' => [
					'all-surveys.js',
					'index-new.js',
					'all-surveys.js',
				],
				'css' => [
					'vox-questionnaries.css',
					'vox-welcome.css',
				],
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	        ));
	    }
	}

	public function request_survey($locale=null) {

		if(!empty($this->user) && $this->user->is_dentist) {

			$validator = Validator::make(Request::all(), [
                'title' => array('required', 'min:6'),
                'target' => array('required', 'in:worldwide,specific'),
                'target-countries' => array('required_if:target,==,specific'),
                'other-specifics' => array('required'),
                'topics' => array('required'),
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

            	$target_countries = [];
				foreach (request('target-countries') as $v) {
					$target_countries[] = Country::find($v)->name;
				}
      
            	$mtext = 'New survey request from '.$this->user->getNames().'
	                
		        Link to CMS: '.url("/cms/users/edit/".$this->user->id).'
		        Survey title: '.request('title').'
		        Survey target group location/s: '.request('target');

		        if (request('target') == 'specific') {
		        	$mtext .= '
		        Survey target group countries: '.implode(',', $target_countries);
		        }
		        
		        $mtext .= '
		        Other specifics of survey target group: '.request('other-specifics').'
		        Survey topics and the questions: '.request('topics');

		        Mail::raw($mtext, function ($message) {

		            $sender = config('mail.from.address-vox');
		            $sender_name = config('mail.from.name-vox');

		            $message->from($sender, $sender_name);
		            $message->to( 'dentavox@dentacoin.com' );
		            $message->to( 'donika.kraeva@dentacoin.com' );
		            $message->replyTo($this->user->email, $this->user->getNames());
		            $message->subject('Survey Request');
		        });

                return Response::json( [
                    'success' => true,
                ] );

            }
		}
	}

	public function request_survey_patients($locale=null) {

		$validator = Validator::make(Request::all(), [
            'topics' => array('required'),
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
        	$mtext = 'New survey request from '.(!empty($this->user) ? 'patient '.$this->user->name.' with User ID '.$this->user->id : 'not logged user').'
Survey topics and the questions: '.request('topics');

	        Mail::raw($mtext, function ($message) {

	            $sender = config('mail.from.address-vox');
                $sender_name = config('mail.from.name-vox');

                $message->from($sender, $sender_name);
                $message->to( 'dentavox@dentacoin.com' );
                $message->to( 'donika.kraeva@dentacoin.com' );
	            $message->subject('Survey Request From Patient');
	        });

            return Response::json( [
                'success' => true,
            ] );

        }
	}

	public function recommend($locale=null) {

		if(!empty($this->user)) {

			$validator = Validator::make(Request::all(), [
                'scale' => array('required'),
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

            	if (session('recommendation')) {
            		$new_recommendation = Recommendation::find(session('recommendation'));

                } else {
                	$new_recommendation = new Recommendation;
                	$new_recommendation->save();
                    session([
                        'recommendation' => $new_recommendation->id
                    ]);
                }
        		
        		$new_recommendation->user_id = $this->user->id;
        		$new_recommendation->scale = Request::input('scale');
        		$new_recommendation->save();

            	if (intval(Request::input('scale')) > 3) {
            		$this->user->fb_recommendation = false;
            		$this->user->save();

            		return Response::json( [
	                    'success' => true,
	                    'recommend' => true,
	                    'description' => false,
	                ] );
            	}

            	if (intval(Request::input('scale')) <= 3) {
            		$this->user->fb_recommendation = true;
            		$this->user->save();
            	}

            	if (!empty(Request::input('description'))) {
            		$new_recommendation->description = Request::input('description');
            		$new_recommendation->save();

            		return Response::json( [
	                    'success' => true,
		                'recommend' => false,
		                'description' => true,
	                ] );
            	}

                return Response::json( [
                    'success' => true,
	                'recommend' => false,
		            'description' => false,
                ] );
            }
		}
	}

	public function voxesSort($locale=null) {
		if(request('sort')) {
			session([
	            'voxes-sort' => request('sort')
	        ]);			
		}
		return Response::json( [
            'sort' => session('voxes-sort'),
        ] );
	}

	public function getPopup() {

		//dd(request('id'));
		if(request('id') == 'request-survey-popup' && !empty($this->user) && $this->user->is_dentist && ($this->user->status == 'approved' || $this->user->status == 'test' || $this->user->status == 'added_by_clinic_claimed' || $this->user->status == 'added_by_dentist_claimed')) {

			return $this->ShowVoxView('popups/request-survey', [
				'countries' => Country::with('translations')->get(),
			]);

		} else if(request('id') == 'request-survey-patient-popup' && !empty($this->user) && !$this->user->is_dentist) {

			return $this->ShowVoxView('popups/request-survey-patients');

		} else if(request('id') == 'recommend-popup' && !empty($this->user)) {

			return $this->ShowVoxView('popups/recommend');

		} else if(request('id') == 'failed-popup' && empty($this->user)) {
			
			return $this->ShowVoxView('popups/failed-reg-login');

		} else if(request('id') == 'login-register-popup' && empty($this->user)) {
			
			return $this->ShowVoxView('popups/login-register');

		} else if(request('id') == 'poll-popup') {

			return $this->ShowVoxView('popups/daily-poll-popup');
		}
	}
}