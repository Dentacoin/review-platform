<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App\Models\PollsMonthlyDescription;
use App\Models\VoxCategory;
use App\Models\PollAnswer;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\Country;
use App\Models\PageSeo;
use App\Models\Reward;
use App\Models\Admin;
use App\Models\User;
use App\Models\Poll;
use App\Models\Dcn;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Session;
use Cookie;
use Route;
use Hash;
use Auth;
use App;
use Mail;
use Log;
use DB;

class DailyPollsController extends ApiController {
    
	public function getPolls($locale=null) {

		$user = Auth::guard('api')->user();

		$year = request('year') ?? date('Y');
		$month = request('month') ?? date('m');

		$all_daily_polls = Poll::where('launched_at', '>=', $year."-".$month."-01 00:00:00")
		->where('launched_at', '<', $year."-".$month."-31 23:59:59")->where('status', '!=', 'scheduled')->get();

		if ($all_daily_polls->isNotEmpty()) {
			foreach ($all_daily_polls as $poll) {
				
				if (!empty($user)) {
					$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first();
				} else {
					if (Cookie::get('daily_poll')) {
						$cv = json_decode(Cookie::get('daily_poll'), true);
						foreach ($cv as $pid => $aid) {
							if ($pid == $poll->id) {
								$taken_daily_poll = true;
								break;
							} else {
								$taken_daily_poll = false;
							}
						}
						
					} else {
						$taken_daily_poll = false;
					}
				}

				$to_take_poll = $poll->status=='open' && !$taken_daily_poll;
				// $to_take_poll = true;

				$daily_polls[] = [
					'title' => $poll->question,
					'category_image' => VoxCategory::find($poll->category)->getImageUrl(),
					'id' => $poll->id,
					'closed' => $poll->status == 'closed' ? true : false,
					'closed_image' => url('new-vox-img/stat-poll.png'),
					'taken' => !empty($taken_daily_poll) ? true : false,
					'taken_image' => url('new-vox-img/taken-poll.png'),
					'to_take' => $to_take_poll,
					'to_take_image' => url('new-vox-img/poll-to-take.png'),
					'date' => date('Y-m-d', $poll->launched_at->timestamp),
					'date_url' => date('d-m-Y', $poll->launched_at->timestamp),
					'day' => date('d', $poll->launched_at->timestamp),
					'day_word' => date('D', $poll->launched_at->timestamp),
					'custom_date' => date('F j, Y', $poll->launched_at->timestamp),
					'color' => VoxCategory::find($poll->category)->color,
				];
			}
		} else {
			$daily_polls = null;
		}

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

		$poll_id = request('id');
		$user = Auth::guard('api')->user();

		$ret = [
        	'success' => false,
        ];
		
		$poll = Poll::find($poll_id);

		if(!empty($poll)) {

			if (!empty($user)) {
				$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first();
			} else {
				$taken_daily_poll = null;
			}

			if (!empty($poll) && $poll->status == 'open' && empty($taken_daily_poll)) {

				$slist = VoxScale::get();
		        $poll_scales = [];
		        foreach ($slist as $sitem) {
		            $poll_scales[$sitem->id] = $sitem;
		        }

				if (!empty($poll->scale_id) && !empty($poll_scales[$poll->scale_id])) {
					$json_answers = explode(',', $poll_scales[$poll->scale_id]->answers);
				} else {
					$json_answers = json_decode($poll->answers, true);
				}

				$answers = [];
				foreach ($json_answers as $key => $answer) {
					$answers[] = [
						'id' => $key,
						'answer' => Poll::handleAnswerTooltip($answer),
					];
				}

				shuffle($answers);

				foreach ($answers as $key => $value) {
					if(mb_strpos($value['answer'], '#')!==false) {
						unset($answers[$key]);
						$answers[$key] = [
							'id' => $value['id'],
							'answer' => mb_substr($value['answer'], 1),
						];
					}
				}

				$answers = array_values($answers);

				$ret = [
		        	'success' => true,
		        	'id' => $poll->id,
		        	'scale_type' => $poll->type == 'scale' ? true : false,
		        	'title' => $poll->question,
		        	'url' => getLangUrl('poll/'.$poll->id),
		        	'answers' => $answers,
		        	'date_href' => date('d-m-Y',$poll->launched_at->timestamp),
		        	'show_poll' => true,
		        ];
			}
		}
		
        return Response::json( $ret );
	}

	public function getPollStats() {

		$poll_id = request('id');
		$user = Auth::guard('api')->user();

		$poll = Poll::find($poll_id);

		if (!empty($poll)) {

			if (!empty($user)) {
		        $taken_daily_polls = PollAnswer::where('user_id', $user->id)->pluck('poll_id')->toArray();
		        $more_polls_to_take = Poll::where('status', 'open')->whereNotIn('id', $taken_daily_polls)->first();
		    } else {
		    	$taken_daily_polls = null;
		    	$more_polls_to_take = null;
		    }

		    $next_stat = Poll::where('status', 'closed')->where('launched_at', '>', $poll->launched_at)->first();

		    if (empty($next_stat)) {
		    	$next_stat = Poll::where('status', 'closed')->orderBy('id', 'asc')->first();
		    }

		    $time = !empty($poll->launched_at) ? $poll->launched_at->timestamp : '';

			$ret = [
	        	'success' => true,
	        	'title' => $poll->question,
	        	'chart' => $this->chartData($poll),
		        'next_poll' => $more_polls_to_take ? $more_polls_to_take->id : false,
		        'closed' => $poll->status == 'closed' ? true : false,
		        'date' => !empty($time) ? date('d/m/Y',$time) : false,
		        'date_href' => !empty($time) ? date('d-m-Y',$time) : false,
		        'has_user' => !empty($user) ? true : false,
		        'next_stat' => $next_stat->id,
		        'show_stats' => true,
	        ];

		} else {
			$ret = [
	        	'success' => false,
	        ];
		}
		
        return Response::json( $ret );
	}

	public function doPoll($id) {

		$user = Auth::guard('api')->user();
		$poll = Poll::find($id);

		$ret = [
        	'success' => false,
        ];

		if (!empty($poll)) {
			$taken_daily_polls = !empty($user) ? (PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first() ? PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first() : [] ) : [];
		    $more_polls_to_take = Poll::where('status', 'open')->whereNotIn('id', $taken_daily_polls)->first();

			if ($poll->respondentsCount() >= 100) {
				$ret = [
		        	'success' => false,
		        	'closed_poll' => $poll->id,
		        	'next_poll' => $more_polls_to_take ? $more_polls_to_take->id : false,
		        ];

		        return Response::json( $ret );
			}

			$a = intval(request('answer'));

			if(!$user && $poll->status == 'open') {

				$country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
				$country_db = Country::where('code', 'like', $country_code)->first();

				$answer = new PollAnswer;
		        $answer->user_id = 0;
		        $answer->country_id = !empty($country_db) ? $country_db->id : null;
		        $answer->poll_id = $poll->id;
		        $answer->answer = $a;
	        	$answer->save();

	        	$poll->recalculateUsersPercentage();

	        	$this->checkStatus($poll);

		        $ret = [
		        	'success' => true,
		        	'logged' => false,
		        	'answer_id' => $answer->id,
		        	'chart' => $this->chartData($poll),
        			'respondents' => 'Respondents: '.$poll->respondentsCount().'/100 people',
        			'has_user' => false,
		        ];

				return Response::json( $ret );
			}

			if( empty($taken_daily_poll) && $poll->status == 'open' ) {

				$country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
				$country_db = Country::where('code', 'like', $country_code)->first();

				$answer = new PollAnswer;
		        $answer->user_id = $user->id;
		        $answer->country_id = !empty($user->country_id) ? $user->country_id : (!empty($country_db) ? $country_db->id : null);
		        $answer->poll_id = $poll->id;
		        $answer->answer = $a;
	        	$answer->save();

	        	$poll->recalculateUsersPercentage();

				$reward = new DcnReward;
		        $reward->user_id = $user->id;
		        $reward->reference_id = $poll->id;
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

		        $this->checkStatus($poll);

		        $taken_daily_polls = PollAnswer::where('user_id', $user->id)->pluck('poll_id')->toArray();
	        	$more_polls_to_take = Poll::where('status', 'open')->whereNotIn('id', $taken_daily_polls)->first();

	        	$ret = [
		        	'success' => true,
		        	'chart' => $this->chartData($poll),
		        	'next_poll' => $more_polls_to_take ? $more_polls_to_take->id : false,
	        		'respondents' => 'Respondents: '.$poll->respondentsCount().'/100 people',
	        		'has_user' => true,
		        ];

	    	} else {
	    		$ret = [
		        	'success' => false,
		        ];
	    	}

	    }
	    return Response::json( $ret );
	}

	private function chartData($poll) {

		$results = PollAnswer::where('poll_id', $poll->id)
		->groupBy('answer')
		->selectRaw('answer, COUNT(*) as cnt')
		->get();

		$chart = [];

		$slist = VoxScale::get();
        $poll_scales = [];
        foreach ($slist as $sitem) {
            $poll_scales[$sitem->id] = $sitem;
        }

		if (!empty($poll->scale_id) && !empty($poll_scales[$poll->scale_id])) {
			$ans_array = explode(',', $poll_scales[$poll->scale_id]->answers);
		} else {
			$ans_array = json_decode($poll->answers);
		}

        foreach ($ans_array as $ans) {
            $answers[] = Poll::handleAnswerTooltip(mb_substr($ans, 0, 1)=='#' ? mb_substr($ans, 1) : $ans);
        }

        foreach ($answers as $key => $value) {
            $chart[$value] = 0;
        }

        $total_count = 0;
        foreach ($results as $res) {
            if(!isset( $answers[ $res->answer-1 ] )) {
                continue;
            }
            $chart[ $answers[ $res->answer-1 ] ] = $res->cnt;
            $total_count+=$res->cnt;
        }

        foreach ($chart as $key => $value) {
        	$chart[$key] = $total_count == 0 ? 0 : number_format($value/$total_count*100);
        }

        arsort($chart);

        return $chart;
	}

	private function checkStatus($poll) {

		$respondents = $poll->respondentsCount();

		if ($respondents >= 100) {
			$poll->status = 'closed';
			$poll->hasimage_social = false;
			$poll->save();
		}
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
		$month = date('m',$time);
		$year = date('Y',$time);

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