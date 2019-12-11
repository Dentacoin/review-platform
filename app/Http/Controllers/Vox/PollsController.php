<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;


use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use Validator;
use Response;
use Request;
use Session;
use Route;
use Hash;
use Auth;
use App;
use Mail;
use DB;
use Cookie;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\User;
use App\Models\Poll;
use App\Models\PollAnswer;
use App\Models\VoxCategory;
use App\Models\VoxScale;
use App\Models\DcnReward;
use App\Models\Dcn;
use App\Models\Reward;


class PollsController extends FrontController
{

	public function list($locale=null) {

		$social_image = url('new-vox-img/daily-polls-home.jpg');
		
		return $this->ShowVoxView('daily-polls', array(
			'js' => [
        		'polls.js'
        	],
			'social_image' => $social_image,
        ));
	}

	public function show_popup_poll($locale=null, $date) {
		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);

		$poll = Poll::where('launched_at', $newformat )->first();
		if (!empty($poll)) {
			$social_image = $poll->getSocialCover();
		} else {
			$social_image = url('new-vox-img/daily-polls-home.jpg');
		}
		
		return $this->ShowVoxView('daily-polls', array(
			'date_poll' => $newformat,
			'js' => [
        		'polls.js'
        	],
			'social_image' => $social_image,
			'canonical' => getLangUrl('daily-polls/'.$date),
			'noindex' => true,
        ));
		
	}

	public function show_popup_stats_poll($locale=null, $date) {

		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);

		$poll = Poll::where('launched_at', $newformat )->first();
		if (!empty($poll)) {
			$social_image = $poll->getSocialCover();
		} else {
			$social_image = url('new-vox-img/daily-polls-home.jpg');
		}
		
		return $this->ShowVoxView('daily-polls', array(
			'date_poll' => $newformat,
			'poll_stats' => true,
			'js' => [
        		'polls.js'
        	],
			'social_image' => $social_image,
			'canonical' => getLangUrl('daily-polls/'.$date.'/stats'),
			'noindex' => true,
        ));
		
	}

	public function get_polls($locale=null) {

		$all_daily_polls = Poll::where('launched_at', '>=', Request::input('year')."-".Request::input('month')."-01 00:00:00")
		->where('launched_at', '<', Request::input('year')."-".str_pad(Request::input('month'), 2)."-31 23:59:59");

		if( empty($this->admin)) {
			$all_daily_polls = $all_daily_polls->where('status', '!=', 'scheduled');
		}

		$all_daily_polls = $all_daily_polls->get();

		if ($all_daily_polls->isNotEmpty()) {
			foreach ($all_daily_polls as $poll) {
				
				if (!empty($this->user)) {
					$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $this->user->id)->first();
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

				// if(empty($this->admin)) {
					$to_take_poll = $poll->status=='open' && !$taken_daily_poll;
				// } else {
				// 	$to_take_poll = true;
				// }

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
					'day' => date('j', $poll->launched_at->timestamp),
					'day_word' => date('l', $poll->launched_at->timestamp),
					'day_mobile' => date('d', $poll->launched_at->timestamp),
					'day_word_mobile' => date('D', $poll->launched_at->timestamp),
					'custom_date' => date('F j, Y', $poll->launched_at->timestamp),
					'color' => VoxCategory::find($poll->category)->color,
					'scheduled' => $poll->status=='scheduled' && !empty($this->admin) ? true : false,
				];
			}
		} else {
			$daily_polls = null;
		}

		$ret = [
        	'success' => true,
        	'daily_polls' => $daily_polls,
        ];
        return Response::json( $ret );
	}

	public function get_poll_content($locale=null, $poll_id) {

		$poll = Poll::find($poll_id);

		if (!empty($this->user)) {
			$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $this->user->id)->first();
		} else {
			if (Cookie::get('daily_poll')) {
				$cv = json_decode(Cookie::get('daily_poll'), true);
				foreach ($cv as $pid => $aid) {
					if ($pid == $poll->id) {
						$taken_daily_poll = PollAnswer::find($aid);
					} else {
						$taken_daily_poll = null;
					}
				}				
			} else {
				$taken_daily_poll = null;
			}
		}

		if (!empty($poll) && $poll->status == 'open' && empty($taken_daily_poll) || !empty($this->admin)) {

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
				$answers[] = Poll::handleAnswerTooltip($answer);
			}

			$ret = [
	        	'success' => true,
	        	'title' => $poll->question,
	        	'url' => getLangUrl('poll/'.$poll->id),
	        	'answers' => $answers,
	        ];

		} else {
			$ret = [
	        	'success' => false,
	        ];
		}
		
        return Response::json( $ret );
	}

	public function get_poll_stats($locale=null, $poll_id) {

		$poll = Poll::find($poll_id);

		if (!empty($poll)) {

			if (!empty($this->user)) {
		        $taken_daily_polls = PollAnswer::where('user_id', $this->user->id)->pluck('poll_id')->toArray();
		        $more_polls_to_take = Poll::where('status', 'open')->whereNotIn('id', $taken_daily_polls)->first();
		    } else {
		    	$taken_daily_polls = null;
		    	$more_polls_to_take = null;
		    }

		    $next_stat = Poll::orderBy('id', 'desc')->where('id', '<', $poll_id)->first();

		    if (empty($next_stat)) {
		    	$next_stat = Poll::where('status', '!=', 'scheduled')->orderBy('id', 'desc')->first();
		    }

			$ret = [
	        	'success' => true,
	        	'title' => $poll->question,
	        	'chart' => $this->chartData($poll),
		        'next_poll' => $more_polls_to_take ? $more_polls_to_take->id : false,
		        'closed' => $poll->status == 'closed' ? true : false,
		        'has_user' => !empty($this->user) ? true : false,
		        'next_stat' => $next_stat->id,
	        ];

		} else {
			$ret = [
	        	'success' => false,
	        ];
		}
		
        return Response::json( $ret );
	}

	public function dopoll($locale=null, $id) {

		$poll = Poll::find($id);

		if (!empty($poll)) {

			if ($poll->respondentsCount() >= 100) {
				$ret = [
		        	'success' => false,
		        	'closed_poll' => $poll->id,
		        ];

		        return Response::json( $ret );
			}

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

			$a = intval(Request::input('answer'));

			if(!$this->user) {

				if(!Auth::guard('admin')->user()) {
					$answer = new PollAnswer;
			        $answer->user_id = 0;
			        $answer->poll_id = $poll->id;
			        $answer->answer = $a;
		        	$answer->save();

		        	$cv = Cookie::get('daily_poll');
		        	if(empty($cv)) {
		        		$cv = [];
		        	} else {
		        		$cv = json_decode($cv, true);
		        	}
					
					$cv[$poll->id] = $answer->id;
		        	Cookie::queue('daily_poll', json_encode($cv), 1440, null, '.dentacoin.com');
		        }

	        	$this->checkStatus($poll);

		        $ret = [
		        	'success' => true,
		        	'logged' => false,
		        	'chart' => $this->chartData($poll),
        			'respondents' => 'Respondents: '.$poll->respondentsCount().'/100 people',
        			'has_user' => false,
		        ];

				return Response::json( $ret );
			}


			$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $this->user->id)->first();

			if( empty($taken_daily_poll) ) {

				if(!Auth::guard('admin')->user()) {
					$answer = new PollAnswer;
			        $answer->user_id = $this->user->id;
			        $answer->poll_id = $poll->id;
			        $answer->answer = $a;
		        	$answer->save();

					$reward = new DcnReward;
			        $reward->user_id = $this->user->id;
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
	                    $reward->os = $dd->getOs()['name'];
	                }

			        $reward->save();
			    }

		        $this->checkStatus($poll);

		        $taken_daily_polls = PollAnswer::where('user_id', $this->user->id)->pluck('poll_id')->toArray();
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

	    	return Response::json( $ret );
	    }
	}

	private function checkStatus($poll) {

		$respondents = $poll->respondentsCount();

		if ($respondents >= 100) {
			$poll->status = 'closed';
			$poll->hasimage_social = false;
			$poll->save();
		}
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
            $answers[] = Poll::handleAnswerTooltip($ans);
        }

        foreach ($answers as $key => $value) {
            $chart[$value] = 0;
        }

        foreach ($results as $res) {
            if(!isset( $answers[ $res->answer-1 ] )) {
                continue;
            }
            $chart[ $answers[ $res->answer-1 ] ] = $res->cnt;
        }

        return $chart;
	}

	
}