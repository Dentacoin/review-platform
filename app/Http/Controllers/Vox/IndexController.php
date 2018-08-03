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
		$has_test = !empty($_COOKIE['first_test']) ? json_decode($_COOKIE['first_test'], true) : null;

		if(!empty($this->user)) {

	        if($this->user->isBanned('vox')) {
	            return redirect(getLangUrl('profile/bans'));
	        }

			$rewarded_for_first = false;
			if( $has_test ) {


				$first = Vox::where('type', 'home')->first();
				$first_question_ids = $first->questions->pluck('id')->toArray();
				$details = Vox::where('type', 'user_details')->first();
				$details_question_ids = $details->questions->pluck('id')->toArray();


				if(!$this->user->madeTest($first->id)) {
					foreach ($has_test as $q_id => $a_id) {

						if($q_id == 'location') {
							list($country_id, $city_id) = explode(',', $a_id);
		        			$this->user->city_id = $city_id;
		        			$this->user->country_id = $country_id;
		        			$this->user->save();
						} else if($q_id == 'birthyear') {
							$this->user->birthyear = $a_id;
		        			$this->user->save();
						} else if($q_id == 'gender') {
							$this->user->gender = $a_id;
		        			$this->user->save();
						} else {
							$vox_id = null;
							if( in_array($q_id, $first_question_ids) ) {
								$vox_id = $first->id;
							} else if( in_array($q_id, $details_question_ids) ) {
								$vox_id = $details->id;
							}

							if($vox_id) {
								$answer = new VoxAnswer;
						        $answer->user_id = $this->user->id;
						        $answer->vox_id = $vox_id;
						        $answer->question_id = $q_id;
						        $answer->answer = $a_id;
						        $answer->country_id = $this->user->country_id;
						        $answer->save();
						    }							
						}
					}
					$reward = new VoxReward;
			        $reward->user_id = $this->user->id;
			        $reward->vox_id = $first->id;
			        $reward->reward = $first->getRewardTotal();
			        $reward->save();

			        if(!$this->user->madeTest($details->id)) {
						$reward = new VoxReward;
				        $reward->user_id = $this->user->id;
				        $reward->vox_id = $details->id;
				        $reward->reward = $details->getRewardTotal();
				        $reward->save();			        	
			        }


			        $rewarded_for_first = true;
				}
			    setcookie('first_test', null, time()-600);

			}

			$sorts = [
				'category' => trans('vox.page.home.sort-category'),
				'newest' => trans('vox.page.home.sort-newest'),
				'reward' => trans('vox.page.home.sort-reward'),
				'duration' => trans('vox.page.home.sort-duration'),
				'popular' => trans('vox.page.home.sort-popular'),
			];
			$sort = Request::input('sort');
			if( !isset( $sorts[Request::input('sort')] ) ) {
				$sort = 'category';
			}

			$viewparams = array(
				'rewarded_for_first' => $rewarded_for_first,
				'sort' => $sort,
				'sorts' => $sorts,
				'taken' => $this->user->filledVoxes()
	        );
	        
			if($sort=='category') {
				$viewparams['cats'] = VoxCategory::whereHas('voxes')->get();
				foreach ($viewparams['cats'] as $key => $cat) {
					if( $cat->voxesWithoutAnswer($this->user)->isEmpty() ) {
						unset($viewparams['cats'][$key]);
					}
				}
			} else {

				if($sort=='popular') {
					$voxes = Vox::where('type', 'normal')
					->whereNotIn('id', $this->user->vox_rewards->pluck('vox_id'))
					->withCount('rewards')->orderBy('rewards_count', 'desc')->get();
				} else {
					$voxes = Vox::where('type', 'normal')
					->whereNotIn('id', $this->user->vox_rewards->pluck('vox_id'))
					->get();
		    		
					if($sort=='newest') {
						$voxes = $voxes->sortBy(function($reward) {
						    return $reward->id;
						}, SORT_REGULAR, true);
					} else if($sort=='reward') {
						$voxes = $voxes->sortBy(function($reward) {
						    return $reward->getRewardTotal();
						}, SORT_REGULAR, true);
					} else if($sort=='duration') {
						$voxes = $voxes->sortBy(function($reward) {
						    return $reward->duration;
						}, SORT_REGULAR, true);
					} 
				}
				$viewparams['voxes'] = $voxes;
			}

			return $this->ShowVoxView('home', $viewparams);

		} else {

			$first = Vox::where('type', 'home')->first();

			$details_test = Vox::find(34);

			$real_questions = $first->questions->count() + 3;
			$real_questions += Vox::find(34)->questions->count();
			
			return $this->ShowVoxView('index', array(
				'vox' => $first,
				'real_questions' => $real_questions,
				'details_test' => $details_test,
				'has_test' => $has_test,
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

}