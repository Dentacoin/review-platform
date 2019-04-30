<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Vox;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\VoxAnswer;
use App\Models\VoxReward;
use App\Models\VoxCategory;
use Carbon\Carbon;

use Mail;
use App;
use Cookie;
use Request;
use Response;

class IndexController extends FrontController
{

	public function home($locale=null) {

		$first = Vox::where('type', 'home')->first();
		if(!empty($this->user)) {
			$this->user->checkForWelcomeCompletion();			
		}

		if(!empty($this->user) && $this->user->madeTest($first->id)) {
			
	        if($this->user->is_dentist && $this->user->status != 'approved' && $this->user->status != 'test') {
	            return redirect(getLangUrl('welcome-to-dentavox'));
	        }

	        if($this->user->isBanned('vox')) {
	            return redirect(getLangUrl('profile'));
	        }

	        $sorts = [
				// 'featured' => trans('vox.page.home.sort-featured'),
				//'untaken' => trans('vox.page.home.sort-untaken'),
				// 'category' => trans('vox.page.home.sort-category'),
				'newest' => trans('vox.page.home.sort-newest'),
				'all' => trans('vox.page.home.sort-all'),
				'popular' => trans('vox.page.home.sort-popular'),
				'reward' => trans('vox.page.home.sort-reward'),
				'taken' => trans('vox.page.home.sort-taken'),
			];

	        // $featured_voxes_ids = Vox::where('type', 'normal')->where('featured', '1')->orderBy('created_at', 'DESC')->get()->pluck('id')->toArray();
	        // $not_taken_featured = array_diff($featured_voxes_ids, $this->user->filledFeaturedVoxes());

	        // // dd($not_taken_featured );

	        // if (empty($not_taken_featured)) {
	        // 	unset($sorts['featured']);
	        // } else {
	        // 	unset($sorts['untaken']);
	        // }

			return $this->ShowVoxView('home', array(
				'sorts' => $sorts,
				'taken' => $this->user->filledVoxes(),
	        	'voxes' => Vox::where('type', 'normal')->orderBy('created_at', 'DESC')->get(),
	        	'vox_categories' => VoxCategory::whereHas('voxes')->get()->pluck('name', 'id')->toArray(),
	        	'js' => [
	        		'home.js'
	        	]
			));

		} else {
			
			return $this->ShowVoxView('index', array(
				'users_count' => User::getCount('vox'),
	        	'voxes' => Vox::where('type', 'normal')->orderBy('sort_order', 'ASC')->take(9)->get(),
	        	'taken' => $this->user ? $this->user->filledVoxes() : [],
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