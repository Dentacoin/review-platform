<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use App\Services\VoxService as ServicesVox;

use App\Models\StopTransaction;
use App\Models\VoxCategory;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Mail;
use Log;
use DB;

class IndexController extends FrontController {

	/**
     * Home page get voxes by filters
     */
	public function getVoxList() {
		$takenVoxesByUser = !empty($this->user) ? $this->user->filledVoxes() : null;
		return ServicesVox::getVoxList($this->user, $this->admin, $takenVoxesByUser);
	}

    /**
     * Home page view
     */
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

		$takenVoxesByUser = !empty($this->user) ? $this->user->filledVoxes() : null;
		$voxList = ServicesVox::getVoxList($this->user, $this->admin, $takenVoxesByUser);

		$allVoxesAreTaken = false;
		$latest_blog_posts = null;
		$vip_access_seconds = 0;
		$vip_access_text = '';

		if(!empty($this->user)) {
			$not_taken_voxes = !empty($this->admin) ? User::getAllVoxes() : $this->user->voxesTargeting();
			$not_taken_voxes = $not_taken_voxes->whereNotIn('id', $takenVoxesByUser)
			->where('type', 'normal')
			->get();

			//if there are no available surveys
			if(!$this->user->notRestrictedVoxesList($not_taken_voxes)->count()) {
				//get latest blog posts from VOX blog database
				$allVoxesAreTaken = true;
				$latest_blog_posts = DB::connection('vox_wordpress_db')
				->table('posts')
				->where('post_type', 'post')
				->where('post_status','publish')
				->orderBy('id', 'desc')
				->take(10)
				->get();

				foreach($latest_blog_posts as $lbp) {
					$post_terms = DB::connection('vox_wordpress_db')
					->table('term_relationships')
					->where('object_id', $lbp->ID)
					->get()
					->pluck('term_taxonomy_id')
					->toArray();

					$category = DB::connection('vox_wordpress_db')
					->table('terms')
					->whereIn('term_id', $post_terms)
					->first();

					$lbp->cat_name = $category->name;

					$post_image_obj = DB::connection('vox_wordpress_db')
					->table('postmeta')
					->where('post_id', $lbp->ID)
					->where('meta_key', '_thumbnail_id')
					->first();

					$post_image_id = $post_image_obj ? $post_image_obj->meta_value : null;
					$post_image_link = DB::connection('vox_wordpress_db')
					->table('posts')
					->where('id', $post_image_id)
					->first();

					$lbp->img = isset($post_image_link->guid) ? $post_image_link->guid : '';
				}
			}

			if($this->user->vip_access) {
				$days = Carbon::now()->diffInDays($this->user->vip_access_until);
				$hours = Carbon::now()->diffInHours($this->user->vip_access_until);
				$min = Carbon::now()->diffInMinutes($this->user->vip_access_until);
				$sec = Carbon::now()->diffInSeconds($this->user->vip_access_until);
				
				if($days) {
					$vip_access_text .= $days.' <span>DAY'.($days != 1 ? 'S' : '').'</span> ';
				}

				$vip_access_text .= ($hours%24).' <span>HOUR'.($hours != 1 ? 'S' : '').'</span> ';

				$vip_access_text .= ($min%60).' <span>MIN</span> '.
				($sec%60).' <span>SEC</span>';

				$vip_access_seconds = $sec;
			}
		}

		$seos = PageSeo::find(2);

        $arr = array(
			'vip_access_text' => $vip_access_text,
			'vip_access_seconds' => $vip_access_seconds,
        	'all_taken' => $allVoxesAreTaken,
        	'latest_blog_posts' => $latest_blog_posts,
            'is_warning_message_shown' => StopTransaction::find(1)->show_warning_text,
			'keywords' => 'paid surveys, online surveys, dentavox, dentavox surveys',
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'sorts' => $sorts,
			'filters' => $filters,
			'taken' => $takenVoxesByUser,
        	'voxes' => $voxList,
        	'vox_categories' => VoxCategory::with('translations')->whereHas('voxes')->get()->pluck('name', 'id')->toArray(),
        	'js' => [
        		'home.js',
        	],
        	'css' => [
        		'vox-home.css',
        	],
		);

		if (!empty($this->user)) {
			$arr['css'][] = 'select2.min.css';
			$arr['js'][] = '../js/select2.min.js';
		}

		if($allVoxesAreTaken) {
			$arr['css'][] = 'flickity.min.css';
            $arr['js'][] = '../js/flickity.min.js';
		}

		return $this->ShowVoxView('home', $arr);
	}

	/**
     * Home page load more voxes
     */
	public function getVoxes() {
		$taken = !empty($this->user) ? $this->user->filledVoxes() : null;
		$voxList = ServicesVox::getVoxList($this->user, $this->admin, $taken);

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
	
	/**
     * Index page for not logged users
     */
	public function home($locale=null) {
		
		if(!empty($this->user)) {
			$this->user->checkForWelcomeCompletion();			
			
	        if($this->user->is_dentist && !in_array($this->user->status, config('dentist-statuses.approved_test'))) {
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
	        		'index-new.js',
	        	],
	        	'css' => [
	        		'vox-index.css',
	        	],
	        ));
		}
	}

	/**
     * bottom content of the index page
     */
	public function index_down($locale=null) {
		$featured_voxes = Vox::with(['translations', 'categories.category', 'categories.category.translations'])
		->where('type', 'normal')
		->where('featured', true)
		->orderBy('launched_at', 'desc')
		->take(9)
		->get();

		if( $featured_voxes->count() < 9 ) {

			$featured_voxes_ids = [];
			foreach ($featured_voxes as $fv) {
				$featured_voxes_ids[] = $fv->id;
			}

			$swiper_voxes = Vox::with(['translations', 'categories.category', 'categories.category.translations'])
			->where('type', 'normal')
			->whereNotIn('id', $featured_voxes_ids)
			->orderBy('launched_at', 'desc')
			->take( 9 - $featured_voxes->count() )
			->get();

			$featured_voxes = $featured_voxes->concat($swiper_voxes);
		}
		return $this->ShowVoxView('index-down', array(
        	'voxes' => $featured_voxes,
        	'taken' => $this->user ? $this->user->filledVoxes() : [],
        ));	
	}
	
	/**
     * Home page for not logged users
     */
	public function surveys_public($locale=null) {

		if(empty($this->user) || (!empty($this->user) && !$this->user->madeTest(11) ) ) {
			return $this->survey_list($locale);
		} else {
			return redirect(getLangUrl('/'));
		}
	}

	/**
     * Welcome survey page
     */
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

			$total_questions = $first->questionsCount() + 3; //because of added demographic q's
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

	/**
     * Dentist request a survey
     */
	public function request_survey($locale=null) {
		return ServicesVox::requestSurvey($this->user, false);
	}

	/**
     * Patient request a survey
     */
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
            ]);
        }
	}

	/**
     * DV recommendation form
     */
	public function recommend($locale=null) {
		return ServicesVox::recommendDentavox($this->user, false);
	}

	/**
     * Session to remember the last sort surveys on home page
     */
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

	/**
     * Popups content
     */
	public function getPopup() {

		//dd(request('id'));
		if(
			request('id') == 'request-survey-popup' 
			&& !empty($this->user) 
			&& $this->user->is_dentist 
			&& in_array($this->user->status, config('dentist-statuses.approved_test'))
		) {

			return $this->ShowVoxView('popups/request-survey', [
				'countries' => Country::with('translations')->get(),
			]);

		} else if(request('id') == 'request-survey-patient-popup' && !empty($this->user) && !$this->user->is_dentist) {

			return $this->ShowVoxView('popups/request-survey-patients');

		} else if(request('id') == 'recommend-popup' && !empty($this->user)) {

			return $this->ShowVoxView('popups/recommend');

		} else if(request('id') == 'social-profile-popup' && !empty($this->user)) {

			return $this->ShowVoxView('popups/social-profile');

		} else if(request('id') == 'failed-popup' && empty($this->user)) {
			
			return $this->ShowVoxView('popups/failed-reg-login');

		} else if(request('id') == 'poll-popup') {

			return $this->ShowVoxView('popups/daily-poll-popup');
		}
	}
}