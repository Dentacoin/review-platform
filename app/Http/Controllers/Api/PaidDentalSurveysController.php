<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Input;

use App\Models\StopTransaction;
use App\Models\VoxCategory;
use App\Models\VoxAnswer;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;

use Validator;
use Response;
use Request;
use Cookie;
use Auth;
use Mail;
use App;

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

		$taken = !empty($user) ? $user->filledVoxes() : null;

		$voxList = app('App\Http\Controllers\Vox\IndexController')->getVoxList();

		$voxes = [];
		foreach ($voxList as $fv) {
			$voxes[] = $fv->convertForResponse();
		}

		$all_taken = false;
		if(!empty($user)) {

			$untaken_voxes = $user->voxesTargeting();
			$untaken_voxes = $untaken_voxes->where('type', 'normal')->count();
			if($untaken_voxes == count($taken)) {
				$all_taken = true;
			}
		}

        $is_warning_message_shown = StopTransaction::find(1)->show_warning_text;

        if(!empty($user)) {
        	$vox_levels['level_name'] = $user->getVoxLevelName();
        	$vox_levels['level_img'] = url('new-vox-img/vox-'.$user->getVoxLevelName().'-icon.svg');
        	$vox_levels['count_surveys'] = $user->countAllSurveysRewards();
        	$vox_levels['count_polls'] = count($user->filledDailyPolls());
        	$vox_levels['lifetime_rewards'] = number_format($user->all_rewards->sum('reward'));
        	$vox_levels['current_redeemable'] = number_format($user->getTotalBalance());
        } else {
        	$vox_levels = [];
        }

        $arr = array(
        	'all_taken' => $all_taken,
            'is_warning_message_shown' => $is_warning_message_shown,
            'warning_message_shown' => trans('vox.page.home.high-gas-price'),
			'countries' => Country::with('translations')->get(),
			'sorts' => $sorts,
			'filters' => $filters,
			'taken' => $taken,
        	'voxes' => $voxes,
        	'categories' => VoxCategory::with('translations')->whereHas('voxes')->get()->pluck('name', 'id')->toArray(),
			'vox_levels' => $vox_levels,
			'user' => Auth::guard('api')->user(),
		);

        return Response::json( $arr );
    }

	public function getVoxes() {
		$voxList = app('App\Http\Controllers\Vox\IndexController')->getVoxList((request('slice') * 6) );

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