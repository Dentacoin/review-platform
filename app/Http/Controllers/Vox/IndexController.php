<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Vox;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\VoxAnswer;
use App\Models\VoxCategory;
use Carbon\Carbon;

use Mail;
use App;
use Cookie;
use Request;
use Response;

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

		$social_image = url('new-vox-img/dentavox-summer-rewards.jpg');

		return $this->ShowVoxView('home', array(
			'keywords' => 'paid surveys, online surveys, dentavox, dentavox surveys',
			'social_image' => $social_image,
			'sorts' => $sorts,
			'filters' => $filters,
			'taken' => !empty($this->user) ? $this->user->filledVoxes() : null,
        	'voxes' => Vox::where('type', 'normal')->orderBy('created_at', 'DESC')->get(),
        	'vox_categories' => VoxCategory::whereHas('voxes')->get()->pluck('name', 'id')->toArray(),
        	'js' => [
        		'home.js'
        	]
		));
	}
	
	public function home($locale=null) {

		$first = Vox::where('type', 'home')->first();
		if(!empty($this->user)) {
			$this->user->checkForWelcomeCompletion();			
		}

		if(!empty($this->user) && $this->user->madeTest($first->id)) {
			
	        if($this->user->is_dentist && $this->user->status != 'approved' && $this->user->status!='added_approved' && $this->user->status != 'test') {
	            return redirect(getLangUrl('welcome-to-dentavox'));
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
			
			$social_image = url('new-vox-img/dentavox-home.jpg');
			
			return $this->ShowVoxView('index', array(
				'subtitle' => $subtitle,
				'title' => $title,
				'users_count' => User::getCount('vox'),
	        	'voxes' => Vox::where('type', 'normal')->orderBy('sort_order', 'ASC')->take(9)->get(),
	        	'taken' => $this->user ? $this->user->filledVoxes() : [],
				'social_image' => $social_image,
	        	'js' => [
	        		'index.js'
	        	],
	            'jscdn' => [
	                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js',
	            ],
	            'csscdn' => [
	                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css',
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

	public function gdpr($locale=null) {

		$this->user->gdpr_privacy = true;
		$this->user->save();

		return redirect( getLangUrl('/') );
	}


	public function appeal($locale=null) {

		$user_login = UserLogin::where('user_id', $this->user->id )->first();

		$mtext = $this->user->getName().' wants to withdraw or do a survey, but is blocked due to bad IP.
                
        Link to CMS: '.url("/cms/users/edit/".$this->user->id).'
        IP address: '.$user_login->ip;

        Mail::raw($mtext, function ($message) {

            $sender = 'petar.stoykov@dentacoin.com';
            $sender_name = config('mail.from.name-vox');

            $message->from($sender, $sender_name);
            $message->to( $sender );
            $message->replyTo($sender, $sender_name);
            $message->subject('Scammer Appeal');
        });

        Request::session()->flash('success-message', trans('vox.common.appeal-sent'));
        return Response::json(['success' => true]);
	}


	public function welcome($locale=null) {
		$first = Vox::where('type', 'home')->first();

		if ($this->user && $this->user->madeTest($first->id)) {
			return redirect( getLangUrl('/') );
		} else {

			$has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;

			if($has_test) {
				if($this->user) {
					return redirect(getLangUrl('/'));
				} else {
					return redirect(getLangUrl('registration'));					
				}

			}

			$total_questions = $first->questions->count() + 3;
			
			return $this->ShowVoxView('welcome', array(
				'vox' => $first,
				'total_questions' => $total_questions,
				'js' => [
					'index.js'
				]
	        ));
	    }
	}

}