<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
use Auth;
use App;
use DB;

class IndexController extends FrontController {

	public function getVoxList() {


		$user = Auth::guard('api')->user() ? Auth::guard('api')->user() : $this->user;

		if( $user ) {
			$taken = $user->filledVoxes();

			$voxes = !empty($this->admin) ? User::getAllVoxes() : $user->voxesTargeting();
			if(request('filter_item')) {
				if(request('filter_item') == 'taken') {
					$voxes = $voxes->whereIn('id', $taken);
				} else if(request('filter_item') == 'untaken') {
					$voxes = $voxes->whereNotIn('id', $taken);
				} else if(request('filter_item') == 'all') {

				}
			} else {
				if($taken) {
					$voxes = $voxes->whereNotIn('id', $taken);
				}
			}
		} else {
			$voxes = User::getAllVoxes();
		}

		$voxes = $voxes->where('type', 'normal');

		if(request('category') && request('category') != 'all') {
			$cat = request('category');
			$voxes->whereHas('categories', function($query) use ($cat) {
				$query->whereHas('category', function($q) use ($cat) {
					$q->where('id', $cat);
				});
			});
		}

		if(request('survey_search')) {

			$searchTitle = trim(Request::input('survey_search'));
			$titles = preg_split('/\s+/', $searchTitle, -1, PREG_SPLIT_NO_EMPTY);

			$voxes->whereHas('translations', function ($query) use ($titles) {
				foreach ($titles as $title) {
					$query->where('title', 'LIKE', '%'.$title.'%')->where('locale', 'LIKE', 'en');
		        }
			});
		}

		$voxList = $voxes->get();

		$sort = request('sortable_items') ?? 'newest-desc';
		$voxList = $voxList->sortByDesc(function ($voxlist) use ($sort) {
			$sort_name = explode('-', $sort)[0];
			$sort_type = explode('-', $sort)[1];

			if($sort_name == 'newest') {

				if($sort_type == 'desc') {

		            if(!empty($voxlist->featured)) {
		                return 100000 - $voxlist->sort_order;
		            } else {
		                return 10000 - $voxlist->sort_order;
		            }
				} else {
					if(!empty($voxlist->featured)) {
		                return 100000 + $voxlist->sort_order;
		            } else {
		                return 10000 + $voxlist->sort_order;
		            }
				}
			} else if($sort_name == 'popular') {

				if($sort_type == 'desc') {

		            if(!empty($voxlist->featured)) {
		                return 100000 + $voxlist->rewardsCount();
		            } else {
		                return 10000 + $voxlist->rewardsCount();
		            }
				} else {
					if(!empty($voxlist->featured)) {
		                return 100000 - $voxlist->rewardsCount();
		            } else {
		                return 10000 - $voxlist->rewardsCount();
		            }
				}
			} else if($sort_name == 'reward') {

				if($sort_type == 'desc') {

		            if(!empty($voxlist->featured)) {
		                return 10000000000 + $voxlist->getRewardTotal();
		            } else {
		                return 10 + $voxlist->getRewardTotal();
		            }
				} else {
					if(!empty($voxlist->featured)) {
		                return 10000000000 - $voxlist->getRewardTotal();
		            } else {
		                return 10 - $voxlist->getRewardTotal();
		            }
				}
			} else if($sort_name == 'duration') {

				$duration = !empty($voxlist->manually_calc_reward) && !empty($voxlist->dcn_questions_count) ? ceil( $voxlist->dcn_questions_count/6) : ceil( $voxlist->questionsCount()/6);

				if($sort_type == 'desc') {

		            if(!empty($voxlist->featured)) {
		                return 100000 + $duration;
		            } else {
		                return 10000 + $duration;
		            }
				} else {
					if(!empty($voxlist->featured)) {
		                return 100000 - $duration;
		            } else {
		                return 10000 - $duration;
		            }
				}
			}
        });

        $get = request()->query();
        unset($get['page']);
        unset($get['submit']);

		if ($user) {
			$voxList = $user->notRestrictedVoxesList($voxList);
			$voxList = $this->paginate($voxList, 6, request('slice') ?? 1 )->appends($get);
		} else {
			$voxList = $this->paginate($voxList, 6, request('slice') ?? 1)->withPath(App::getLocale().'/paid-dental-surveys/')->appends($get);
		}

		return $voxList;
	}

    private function paginate($items, $perPage, $page, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $pageItems = $perPage;
        $user = Auth::guard('api')->user() ? Auth::guard('api')->user() : $this->user;
        if(!empty($user) && $user->is_dentist && $page == 1) {
        	$pageItems = $pageItems - 1;
        }
        return new LengthAwarePaginator($items->forPage($page, $pageItems), $items->count(), $pageItems, $page, $options);
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

		$taken = !empty($this->user) ? $this->user->filledVoxes() : null;

		$voxList = $this->getVoxList();

		$all_taken = false;
		if(!empty($this->user)) {

			if($this->user->id == 37530) {
				$all_taken = true;
			} else {

				$untaken_voxes = !empty($this->admin) ? User::getAllVoxes() : $this->user->voxesTargeting();
				$untaken_voxes = $untaken_voxes->where('type', 'normal')->count();
				if($untaken_voxes == count($taken)) {
					$all_taken = false;
				}
			}
		}

		$latest_blog_posts = null;
		if($all_taken){
			$latest_blog_posts = DB::connection('vox_wordpress_db')->table('posts')->where('post_type', 'post')->where('post_status','publish')->orderBy('id', 'desc')->take(10)->get();

			foreach($latest_blog_posts as $lbp) {
				$post_terms = DB::connection('vox_wordpress_db')->table('term_relationships')->where('object_id', $lbp->ID)->get()->pluck('term_taxonomy_id')->toArray();
				$category = DB::connection('vox_wordpress_db')->table('terms')->whereIn('term_id', $post_terms)->first();

				$lbp->cat_name = $category->name;

				$post_image_id = DB::connection('vox_wordpress_db')->table('postmeta')->where('post_id', $lbp->ID)->where('meta_key', '_thumbnail_id')->first()->meta_value;
				$post_image_link = DB::connection('vox_wordpress_db')->table('posts')->where('id', $post_image_id)->first();

				$lbp->img = $post_image_link->guid;

			}
		}

		$seos = PageSeo::find(2);
        $is_warning_message_shown = StopTransaction::find(1)->show_warning_text;

        $arr = array(
        	'all_taken' => $all_taken,
        	'latest_blog_posts' => $latest_blog_posts,
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
			'taken' => $taken,
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

		if($all_taken) {
			$arr['css'][] = 'flickity.min.css';
            $arr['js'][] = 'flickity.min.js';
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
	        	],
	        	'css' => [
	        		'vox-index.css',
	        		'vox-index-home.css',
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
					'welcome-vox.js',
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