<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Vox;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\VoxAnswer;
use App\Models\PageSeo;
use App\Models\UserStrength;
use App\Models\VoxCategory;
use App\Models\Country;
use App\Models\Recommendation;
use Carbon\Carbon;

use Mail;
use App;
use Cookie;
use Request;
use Response;
use Validator;

class IndexController extends FrontController
{
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

		$strength_arr = null;
		$completed_strength = null;
		if ($this->user) {
			$strength_arr = UserStrength::getStrengthPlatform('vox', $this->user);
			$completed_strength = $this->user->getStrengthCompleted('vox');
		}

		if( $this->user ) {
			$voxList = !empty($this->admin) ? User::getAllVoxes() : $this->user->voxesTargeting();
		} else {
			$voxList = User::getAllVoxes();
		}
		$voxList = $voxList->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->orderBy('created_at', 'DESC')->get();

		if ($this->user && !empty($this->user->country_id)) {
			$arr = [];
			foreach ($voxList as $vl) {
				$has_started_the_survey = VoxAnswer::where('vox_id', $vl->id)->where('user_id', $this->user->id)->first();

	            if(!empty($vl->country_percentage) && !empty($vl->users_percentage) && array_key_exists($this->user->country_id, $vl->users_percentage) && $vl->users_percentage[$this->user->country_id] >= $vl->country_percentage  && empty($has_started_the_survey)) {
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

		$seos = PageSeo::find(2);

		return $this->ShowVoxView('home', array(
			'strength_arr' => $strength_arr,
			'completed_strength' => $completed_strength,
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
                'flickity.min.js',
        	],
        	'css' => [
        		'vox-home.css',
        		'flickity.min.css'
        	],
	        'jscdn' => [
	            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js',
	        ],
	        'csscdn' => [
	            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css',
	        ],
		));
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

			$featured_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->where('featured', true)->orderBy('sort_order', 'ASC')->take(9)->get();

			if( $featured_voxes->count() < 9 ) {

				$arr_v = [];
				foreach ($featured_voxes as $fv) {
					$arr_v[] = $fv->id;
				}

				$swiper_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->whereNotIn('id', $arr_v)->orderBy('sort_order', 'ASC')->take( 9 - $featured_voxes->count() )->get();

				$featured_voxes = $featured_voxes->concat($swiper_voxes);
			}
			
			return $this->ShowVoxView('index', array(
				'subtitle' => $subtitle,
				'title' => $title,
				'users_count' => User::getCount('vox'),
	        	'voxes' => $featured_voxes,
	        	'taken' => $this->user ? $this->user->filledVoxes() : [],
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	        	'js' => [
	        		'index.js',
					'swiper.min.js'
	        	],
	        	'css' => [
	        		'vox-index.css',
					'swiper.min.css'
	        	],
	        ));			
		}
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
					'index.js'
				],
				'css' => [
					'vox-questionnaries.css'
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
      
            	$mtext = 'New survey request from '.$this->user->getName().'
	                
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
		            $message->replyTo($this->user->email, $this->user->getName());
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
	            $message->subject('Survey Request From Patient or Not Logged User');
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

}