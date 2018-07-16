<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Vox;
use App\Models\User;
use App\Models\VoxAnswer;
use App\Models\VoxReward;
use App\Models\VoxCategory;

use App;
use Cookie;
use Request;

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
				if(!$this->user->madeTest($first->id)) {
					foreach ($has_test as $q_id => $a_id) {
						$answer = new VoxAnswer;
				        $answer->user_id = $this->user->id;
				        $answer->vox_id = $first->id;
				        $answer->question_id = $q_id;
				        $answer->answer = $a_id;
				        $answer->country_id = $this->user->country_id;
				        $answer->save();
					}
					$reward = new VoxReward;
			        $reward->user_id = $this->user->id;
			        $reward->vox_id = $first->id;
			        $reward->reward = $first->getRewardTotal();
			        $reward->save();
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

			
			return $this->ShowVoxView('index', array(
				'vox' => $first,
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

}