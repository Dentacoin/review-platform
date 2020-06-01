<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

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
use DB;

class PollsController extends FrontController
{

	public function list($locale=null) {

		$seos = PageSeo::find(14);
		
		return $this->ShowVoxView('daily-polls', array(
			'js' => [
        		'polls.js'
        	],
			'css' => [
        		'vox-daily-polls.css'
        	],
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
        ));
	}

	public function show_popup_poll($locale=null, $date) {
		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);
		$month = date('m',$time);
		$year = date('Y',$time);

		$seos = PageSeo::find(14);

		$poll = Poll::where('launched_at', $newformat )->first();
		if (!empty($poll)) {
			$social_image = $poll->getSocialCover();
		} else {
			$social_image = $seos->getImageUrl();
		}
		
		return $this->ShowVoxView('daily-polls', array(
			'date_poll' => $newformat,
			'poll_month' => $month,
			'poll_year' => $year,
			'js' => [
        		'polls.js'
        	],
			'css' => [
        		'vox-daily-polls.css'
        	],
			'social_image' => $social_image,
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'canonical' => getLangUrl('daily-polls/'.$date),
			'noindex' => true,
        ));
		
	}

	public function show_popup_stats_poll($locale=null, $date) {

		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);
		$month = date('m',$time);
		$year = date('Y',$time);

		$seos = PageSeo::find(14);

		$poll = Poll::where('launched_at', $newformat )->first();
		if (!empty($poll)) {
			$social_image = $poll->getSocialCover();
		} else {
			$social_image = $seos->getImageUrl();
		}
		
		return $this->ShowVoxView('daily-polls', array(
			'date_poll' => $newformat,
			'poll_month' => $month,
			'poll_year' => $year,
			'poll_stats' => true,
			'js' => [
        		'polls.js'
        	],
			'css' => [
        		'vox-daily-polls.css'
        	],
			'social_image' => $social_image,
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
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

		$ret = [
        	'success' => false,
        ];

		if(!empty($poll_id)) {

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

				$randomize_answers = empty($poll->dont_randomize_answers) && $poll->type != 'scale' ? true : false;

				$ret = [
		        	'success' => true,
		        	'title' => $poll->question,
		        	'url' => getLangUrl('poll/'.$poll->id),
		        	'answers' => $answers,
		        	'randomize_answers' => $randomize_answers,
		        ];
			}
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

		    $time = !empty($poll->launched_at) ? $poll->launched_at->timestamp : '';

			$ret = [
	        	'success' => true,
	        	'title' => $poll->question,
	        	'chart' => $this->chartData($poll),
		        'next_poll' => $more_polls_to_take ? $more_polls_to_take->id : false,
		        'closed' => $poll->status == 'closed' ? true : false,
		        'date' => !empty($time) ? date('d/m/Y',$time) : false,
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

		if(Request::isMethod('post')) {
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
						$country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
						$country_db = Country::where('code', 'like', $country_code)->first();

						$answer = new PollAnswer;
				        $answer->user_id = 0;
				        $answer->country_id = !empty($country_db) ? $country_db->id : null;
				        $answer->poll_id = $poll->id;
				        $answer->answer = $a;
			        	$answer->save();

			        	$poll->recalculateUsersPercentage();

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

						$country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
						$country_db = Country::where('code', 'like', $country_code)->first();

						$answer = new PollAnswer;
				        $answer->user_id = $this->user->id;
				        $answer->country_id = !empty($this->user->country_id) ? $this->user->country_id : (!empty($country_db) ? $country_db->id : null);
				        $answer->poll_id = $poll->id;
				        $answer->answer = $a;
			        	$answer->save();

			        	$poll->recalculateUsersPercentage();

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
	                		$reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
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
		} else {
			return redirect(getLangUrl('/'));
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
            $answers[] = Poll::handleAnswerTooltip(mb_substr($ans, 0, 1)=='#' ? mb_substr($ans, 1) : $ans);
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


	public function hidePoll( $locale=null ) {

		// $sess = [
  //           'hide_poll' => true,
  //       ];
  //       session($sess);

	    return Response::json( [
	        'success' => true,
	    ] );
    }
}