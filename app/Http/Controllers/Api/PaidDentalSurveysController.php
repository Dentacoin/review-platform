<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use App\Services\VoxService as ServicesVox;

use App\Models\StopTransaction;
use App\Models\VoxCategory;
use App\Models\Country;
use App\Models\User;

use Carbon\Carbon;

use Response;
use Auth;
use DB;

class PaidDentalSurveysController extends ApiController {
    

	public function allVoxes() {

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

        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

		$taken = !empty($user) ? $user->filledVoxes() : null;
		$voxList = ServicesVox::getVoxList($user, false, $taken);

		$voxes = [];
		foreach ($voxList as $fv) {
			$voxes[] = $fv->convertForResponse();
		}

		$all_taken = false;
		$latest_blog_posts = false;

		$vip_access_seconds = 0;
		$vip_access_text = '';

		if(!empty($user)) {

			$untaken_voxes = $user->voxesTargeting();
			$untaken_voxes = $untaken_voxes->whereNotIn('id', $taken)->where('type', 'normal')->get();

			if(!$user->notRestrictedVoxesList($untaken_voxes)->count()) {
				$all_taken = true;

				$latest_blog_posts = DB::connection('vox_wordpress_db')->table('posts')->where('post_type', 'post')->where('post_status','publish')->orderBy('id', 'desc')->take(10)->get();

				foreach($latest_blog_posts as $lbp) {
					$post_terms = DB::connection('vox_wordpress_db')->table('term_relationships')->where('object_id', $lbp->ID)->get()->pluck('term_taxonomy_id')->toArray();
					$category = DB::connection('vox_wordpress_db')->table('terms')->whereIn('term_id', $post_terms)->first();

					$lbp->cat_name = $category->name;

					$post_image_id = DB::connection('vox_wordpress_db')->table('postmeta')->where('post_id', $lbp->ID)->where('meta_key', '_thumbnail_id')->first()->meta_value;
					$post_image_link = DB::connection('vox_wordpress_db')->table('posts')->where('id', $post_image_id)->first();

					$lbp->img = isset($post_image_link->guid) ? $post_image_link->guid : '';
				}
			}

			if($user->vip_access) {
				$days = Carbon::now()->diffInDays($user->vip_access_until);
				$hours = Carbon::now()->diffInHours($user->vip_access_until);
				$min = Carbon::now()->diffInMinutes($user->vip_access_until);
				$sec = Carbon::now()->diffInSeconds($user->vip_access_until);
	
				if($days) {
					$vip_access_text .= $days;
	
					if($days == 1 ) {
						$vip_access_text .= ' <span>DAY</span> ';
					} else {
						$vip_access_text .= ' <span>DAYS</span> ';
					}
				}
	
				$vip_access_text .= ($hours%24);
	
				if($hours == 1 ) {
					$vip_access_text .= ' <span>HOUR</span> ';
				} else {
					$vip_access_text .= ' <span>HOURS</span> ';
				}
	
				$vip_access_text .= ($min%60).' <span>MIN</span> '.
				($sec%60).' <span>SEC</span>';
	
				$vip_access_seconds = $sec;
			}
		}

        $is_warning_message_shown = StopTransaction::find(1)->show_warning_text;

        if(!empty($user)) {
        	$vox_levels['level_name'] = trans('vox.page.home.levels.'.$user->getVoxLevelName());
        	$vox_levels['level_img'] = url('new-vox-img/vox-'.$user->getVoxLevelName().'-icon.svg');
        	$vox_levels['count_surveys'] = $user->countAllSurveysRewards();
        	$vox_levels['count_polls'] = count($user->filledDailyPolls());
        	$vox_levels['lifetime_rewards'] = number_format($user->all_rewards->sum('reward'));
        	$vox_levels['current_redeemable'] = number_format($user->getTotalBalance());
        } else {
        	$vox_levels = [];
        }

		$categories = VoxCategory::with('translations')
		->whereHas('voxes')
		->get()
		->pluck('name', 'id')
		->toArray();

        $arr = array(
        	'all_taken' => $all_taken,
			'vip_access_text' => $vip_access_text,
			'vip_access_seconds' => $vip_access_seconds,
        	'latest_blog_posts' => collect(),
            'is_warning_message_shown' => $is_warning_message_shown,
            'warning_message_shown' => trans('vox.page.home.high-gas-price'),
			'countries' => Country::with('translations')->get(),
			'sorts' => $sorts,
			'filters' => $filters,
			'taken' => $taken,
        	'voxes' => $voxes,
        	'categories' => $categories,
			'vox_levels' => $vox_levels,
			'user' => $user,
		);

        return Response::json( $arr );
    }

	public function getVoxes() {
		$user = Auth::guard('api')->user();
        // $user = User::find(37530);
		$taken = !empty($user) ? $user->filledVoxes() : null;
		$voxList = ServicesVox::getVoxList($user, false, $taken);

		if($voxList->count()) {

			$voxes = [];
			foreach ($voxList as $fv) {
				$voxes[] = $fv->convertForResponse();
			}

			return Response::json( array(
				'voxes' => $voxes,
			) );

		} else {
			return Response::json( array(
				'voxes' => [],
			) );
		}
	}
    
}