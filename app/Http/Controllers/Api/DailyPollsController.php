<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Services\VoxService as ServicesVox;

use App\Models\PollsMonthlyDescription;
use App\Models\PollAnswer;
use App\Models\DcnReward;
use App\Models\Country;
use App\Models\Reward;
use App\Models\User;
use App\Models\Poll;

use Carbon\Carbon;

use Response;
use Auth;

class DailyPollsController extends ApiController {
    
	public function getPolls($locale=null) {

		$user = Auth::guard('api')->user();

		$year = request('year') ?? date('Y');
		$month = request('month') ?? date('m');

		$daily_polls = ServicesVox::getDailyPollsByMonth($user, false, true);

		$n_month = $month+1%12;
		$next_month = str_pad($n_month==13?'1':$n_month, 2);
		$next_year = ($n_month==13?($year+1):$year);

		$last_month = $month-1%12;
		$prev_month = str_pad($last_month==0?'12':$last_month, 2);
		$prev_year = ($last_month==0?($year-1):$year);

		// $all_days_in_month = [];
		$current_month_name = date('F', mktime(12, 0, 0, $month, 1, $year));

		// for($i=1; $i<=31; $i++) {
		//     $time=mktime(12, 0, 0, $month, $i, $year);
		//     if (date('m', $time)==$month) {
		//         $all_days_in_month[date('d', $time)]=date('D', $time);
		//     }       
		// }

		// if(!empty($daily_polls)) {
		// 	foreach ($all_days_in_month as $key => $value) {
		// 		$found = false;
		// 		foreach ($daily_polls as $dp) {
		// 			if(intval($dp['day']) == $key) {
		// 				$found = true;
		// 				break;
		// 			}
		// 		}

		// 		if(!$found) {
		// 			$daily_polls[] = [
		// 				'day' => $key,
		// 				'day_word' => $value,
		// 			];
		// 		}
		// 	}
		// }

		$monthly_descr = PollsMonthlyDescription::where('month', $month)->where('year', $year)->first();

		$ret = [
        	'success' => true,
        	'daily_polls' => $daily_polls,
        	'monthly_descr' => $monthly_descr ? $monthly_descr->description : null,
        	'prev_month' => $prev_month,
        	'prev_year' => $prev_year,
        	'next_month' => $next_month,
        	'next_year' => $next_year,
        	'current_month_name' => $current_month_name,
        	'year' => $year,
        	'current_month' => date('m'),
        	'current_year' => date('Y'),
        	// 'all_days_in_month' => $all_days_in_month,
        ];
        return Response::json( $ret );
	}

	public function getPollContent() {
		
        // $user = User::find(37530);
		$user = Auth::guard('api')->user();
		$poll_id = request('id');

    	return ServicesVox::getPollContent($poll_id, $user, false, true);
	}

	public function getPollStats() {

		$poll_id = request('id');
		$user = Auth::guard('api')->user();

		return ServicesVox::getPollStats($poll_id, $user);
	}

	public function doPoll($id) {

		$user = Auth::guard('api')->user();

		return ServicesVox::doPoll($id, $user, false, true);
	}

	public function dailyPollReward() {

		$user = Auth::guard('api')->user();
		$answer_id = request('answer_id');
		$answer = PollAnswer::find($answer_id);

		$ret = [
        	'success' => false,
        ];

		if(!empty($answer) && !empty($user)) {

	        $taken_reward = DcnReward::where('user_id', $user->id)->where('reference_id', $answer->poll_id)->where('platform', 'vox')->where('type', 'daily_poll')->first();

	        if (empty($taken_reward)) {

	            $reward = new DcnReward;
	            $reward->user_id = $user->id;
	            $reward->reference_id = $answer->poll_id;
	            $reward->platform = 'vox';
	            $reward->type = 'daily_poll';
	            $reward->reward = Reward::getReward('daily_polls');

	            $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
	            $dd = new DeviceDetector($userAgent);
	            $dd->parse();

	            if ($dd->isBot()) {
	                // handle bots,spiders,crawlers,...
	                $reward->device = $dd->getBot();
	            } else {
	                $reward->device = $dd->getDeviceName();
	                $reward->brand = $dd->getBrandName();
	                $reward->model = $dd->getModel();
	                $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
	            }

	            $reward->save();

	            PollAnswer::where('id', $answer->id)->update([
	                'user_id' => $user->id
	            ]);

	            $ret = [
		        	'success' => true,
		        ];
	        }
		}

		return Response::json( $ret );
	}

	public function pollRewardPrice() {
		return Response::json( [
			'dcn' => Reward::getReward('daily_polls')
		] );
	}

	public function todaysPollAnswer() {

		$answer_id = request('answer_id');
		if($answer_id && PollAnswer::find($answer_id)) {

			$answer = PollAnswer::find($answer_id);
			if($answer->created_at >= new Carbon(date('Y-m-d').' 00:00:00')) {

				return Response::json( [
					'success' => true,
					'poll_id' => $answer->poll_id,
				] );
			} else {
				return Response::json( [
					'success' => false,
					'poll_id' => $answer->poll_id,
				] );
			}
		}

		return Response::json( [
			'success' => false,
		] );
	}

	public function getDailyPollByDate() {

		$date = request('date');

		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);
		// $month = date('m',$time);
		// $year = date('Y',$time);

		$poll = Poll::where('launched_at', $newformat )->first();

		if(!empty($poll)) {

			$user = Auth::guard('api')->user();
			$poll_type = null;

			if($poll->status == 'open') {

		        $restrictions = false;
		            
	            if(!empty($user) && !empty($user->country_id)) {
	                $restrictions = $poll->isPollRestricted($user->country_id);
	            } else {

	                $country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
	                $country_db = Country::where('code', 'like', $country_code)->first();

	                if (!empty($country_db)) {
	                    $restrictions = $poll->isPollRestricted($country_db->id);
	                }
	            }

	            if($restrictions) {
	                $poll_type = 'stats';
	            } else {

					$poll_type = 'current';

		            if(!empty($user)) {
		                $is_taken = PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first();

		                if ($is_taken) {
		        			$poll_type = 'stats';
		                }
		            }
	            }

			} else if($poll->status == 'scheduled') {

			} else {
				$poll_type = 'stats';
			}

			return Response::json( [
				'success' => true,
				'poll_id' => $poll->id,
				'poll_type' => $poll_type,
			] );
		}

		return Response::json( [
			'success' => false,
		] );
	}
    
}