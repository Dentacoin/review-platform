<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Vox;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\VoxAnswer;
use App\Models\VoxReward;
use App\Models\VoxCategory;

use Mail;
use App;
use Cookie;
use Request;
use Response;

class IndexController extends FrontController
{

	public function home($locale=null) {

		if(!empty($this->user)) {
			$this->user->checkForWelcomeCompletion();

	        if($this->user->isBanned('vox')) {
	            return redirect(getLangUrl('profile'));
	        }

			return $this->ShowVoxView('home', array(
				'sorts' => [
					'featured' => trans('vox.page.home.sort-featured'),
					// 'category' => trans('vox.page.home.sort-category'),
					'newest' => trans('vox.page.home.sort-newest'),
					'popular' => trans('vox.page.home.sort-popular'),
					'reward' => trans('vox.page.home.sort-reward'),
					'duration' => trans('vox.page.home.sort-duration'),
					'taken' => trans('vox.page.home.sort-taken'),
				],
				'taken' => $this->user->filledVoxes(),
	        	'voxes' => Vox::where('type', 'normal')->with('stats_questions')->get(),
	        	'vox_categories' => VoxCategory::whereHas('voxes')->get()->pluck('name', 'id')->toArray(),
	        	'js' => [
	        		'home.js'
	        	]
			));

		} else {
			
			return $this->ShowVoxView('index', array(
				'users_count' => User::getCount('vox'),
				'js' => [
					'index.js'
				]
	        ));			
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
				return redirect(getLangUrl('registration'));
			}

			$details_test = Vox::find(34);

			$real_questions = $first->questions->count() + 3;
			$real_questions += Vox::find(34)->questions->count();
			
			return $this->ShowVoxView('welcome', array(
				'vox' => $first,
				'real_questions' => $real_questions,
				'details_test' => $details_test,
				'js' => [
					'index.js'
				]
	        ));
	    }
	}

}