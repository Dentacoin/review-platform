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

use Validator;
use Response;
use Request;
use Mail;
use DB;

class IndexController extends FrontController {

	/**
     * Home page get voxes by filters
     */
	public function getVoxList() {
		$taken = !empty($this->user) ? $this->user->filledVoxes() : null;
		return ServicesVox::getVoxList($this->user, $this->admin, $taken);
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

		$taken = !empty($this->user) ? $this->user->filledVoxes() : null;
		$voxList = ServicesVox::getVoxList($this->user, $this->admin, $taken);

		$all_taken = false;
		$latest_blog_posts = null;

		if(!empty($this->user)) {
			$untaken_voxes = !empty($this->admin) ? User::getAllVoxes() : $this->user->voxesTargeting();
			$untaken_voxes = $untaken_voxes->whereNotIn('id', $taken)->where('type', 'normal')->get();

			if(!$this->user->notRestrictedVoxesList($untaken_voxes)->count()) {
				$all_taken = true;
				$latest_blog_posts = DB::connection('vox_wordpress_db')->table('posts')->where('post_type', 'post')->where('post_status','publish')->orderBy('id', 'desc')->take(10)->get();

				if($this->user->id == 37530) {
					dd($latest_blog_posts);
				}

				foreach($latest_blog_posts as $lbp) {
					$post_terms = DB::connection('vox_wordpress_db')->table('term_relationships')->where('object_id', $lbp->ID)->get()->pluck('term_taxonomy_id')->toArray();
					$category = DB::connection('vox_wordpress_db')->table('terms')->whereIn('term_id', $post_terms)->first();

					$lbp->cat_name = $category->name;

					$post_image_obj = DB::connection('vox_wordpress_db')->table('postmeta')->where('post_id', $lbp->ID)->where('meta_key', '_thumbnail_id')->first();

					$post_image_id = $post_image_obj ? $post_image_obj->meta_value : null;
					$post_image_link = DB::connection('vox_wordpress_db')->table('posts')->where('id', $post_image_id)->first();

					$lbp->img = $post_image_link->guid;
				}
			}
		}

		$seos = PageSeo::find(2);
        $is_warning_message_shown = StopTransaction::find(1)->show_warning_text;

        $arr = array(
        	'all_taken' => $all_taken,
        	'latest_blog_posts' => $latest_blog_posts,
            'is_warning_message_shown' => $is_warning_message_shown,
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
        		'vox-home.css',
        	],
		);

		if (!empty($this->user)) {
			$arr['css'][] = 'select2.min.css';
			$arr['js'][] = '../js/select2.min.js';
		}

		if($all_taken) {
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
		$featured_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->where('featured', true)->orderBy('launched_at', 'desc')->take(9)->get();

		if( $featured_voxes->count() < 9 ) {

			$arr_v = [];
			foreach ($featured_voxes as $fv) {
				$arr_v[] = $fv->id;
			}

			$swiper_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->whereNotIn('id', $arr_v)->orderBy('launched_at', 'desc')->take( 9 - $featured_voxes->count() )->get();

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
            ] );

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
		if(request('id') == 'request-survey-popup' && !empty($this->user) && $this->user->is_dentist && in_array($this->user->status, config('dentist-statuses.approved_test'))) {

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