<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Illuminate\Support\Facades\Log;
use DeviceDetector\DeviceDetector;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Input;

use App\Models\UserSurveyWarning;
use App\Models\StopTransaction;
use App\Models\Recommendation;
use App\Models\VoxCrossCheck;
use App\Models\VoxCategory;
use App\Models\VoxQuestion;
use App\Models\UserDevice;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\VoxRelated;
use App\Models\PollAnswer;
use App\Models\VoxAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\Reward;
use App\Models\Admin;
use App\Models\Email;
use App\Models\User;
use App\Models\Poll;
use App\Models\Vox;
use App\Models\Dcn;


use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Session;
use Image;
use Route;
use Hash;
use Auth;
use Mail;
use Lang;
use App;
use DB;

class IndexController extends ApiController {

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        parent::__construct($request, $route, $locale);

    	$this->details_fields = config('vox.details_fields');
	}


	public function goBack($answered, $list, $vox) {

		$user = Auth::guard('api')->user();

		$lastkey = null;
		if(!empty($answered)) {
			foreach ($list as $aq) {
				if(!$aq->is_skipped) {
					$lastkey = $aq->question_id;
				}
			}

			$found = false;
			foreach ($vox->questions as $question) {
				if($question->id==$lastkey) {
					$found = true;
				}
				if($found) {
					VoxAnswer::where('vox_id', $vox->id)
					->where('user_id', $user->id)
					->where('question_id', $question->id)
					->delete();

					DcnReward::where('reference_id', $vox->id)
					->where('platform', 'vox')
					->where('type', 'survey')
					->where('user_id', $user->id)
					->delete();
				}
			}
		}

		$lastest_key = VoxAnswer::where('vox_id', $vox->id)
		->where('user_id', $user->id)
		->where('question_id', $lastkey)
		->first();

		if(!empty($lastest_key) && $lastest_key->answer == 0) {
			do {
				$this->goBack($answered, $list, $vox);
			} while ( $lastest_key->answer == 0);
		}

		return $lastkey;
	}
    
	public function headerStats() {

		$user = Auth::guard('api')->user();

		$todays_daily_poll = Poll::where('launched_at', date('Y-m-d') )->first();
		$poll_type = null;

		if(!empty($todays_daily_poll)) {

			if($todays_daily_poll->status == 'open') {

		        $restrictions = false;
		            
	            if(!empty($user) && !empty($user->country_id)) {
	                $restrictions = $todays_daily_poll->isPollRestricted($user->country_id);

	            } else {

	                $country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
	                $country_db = Country::where('code', 'like', $country_code)->first();

	                if (!empty($country_db)) {
	                    $restrictions = $todays_daily_poll->isPollRestricted($country_db->id);
	                }
	            }

	            if($restrictions) {
	                $poll_type = 'stats';
	            } else {

					$poll_type = 'current';

		            if(!empty($user)) {
		                $is_taken = PollAnswer::where('poll_id', $todays_daily_poll->id)->where('user_id', $user->id)->first();

		                if ($is_taken) {
		        			$poll_type = 'stats';
		                }
		            }
	            }

			} else if($todays_daily_poll->status == 'scheduled') {

			} else {
				$poll_type = 'stats';
			}
		}

		return Response::json( array(
			'translations_android' => Lang::get('vox', array(), 'en'),
			'translations_ios' => Lang::get('vox-ios', array(), 'en'),
			'users_count' => User::getCount('vox'),
			'answers_count' => VoxAnswer::getCount(),
			'dcn_price' => @file_get_contents('/tmp/dcn_price'),
			'daily_poll' => $todays_daily_poll ? $todays_daily_poll->id : null,
			'poll_type' => $poll_type,
			'user' => Auth::guard('api')->user(),
		) );
    }

    private function featuredVoxes() {
    	$featured_voxes = Vox::with('translations')->where('type', 'normal')->where('featured', true)->orderBy('sort_order', 'ASC')->take(9)->get();

		if( $featured_voxes->count() < 9 ) {

			$arr_v = [];
			foreach ($featured_voxes as $fv) {
				$arr_v[] = $fv->id;
			}

			$swiper_voxes = Vox::with('translations')->where('type', 'normal')->whereNotIn('id', $arr_v)->orderBy('sort_order', 'ASC')->take( 9 - $featured_voxes->count() )->get();

			$featured_voxes = $featured_voxes->concat($swiper_voxes);
		}

		$voxes = [];
		foreach ($featured_voxes as $fv) {
			$voxes[] = $fv->convertForResponse();
		}

		return $voxes;
    }

    private function relatedSuggestedVoxes($vox_id, $type) {
    	$related_voxes = [];
		$related_voxes_ids = [];

		$vox = Vox::find($vox_id);
    	$user = Auth::guard('api')->user();
		$filled_voxes = $user->filledVoxes();

		if ($vox->related->isNotEmpty()) {
			foreach ($vox->related as $r) {
				if (!in_array($r->related_vox_id, $filled_voxes)) {
					$related_voxes_ids[] = $r->related_vox_id;
				}
			}

			if (!empty($related_voxes_ids)) {
				$arr = Vox::whereIn('id', $related_voxes_ids)->get();

				foreach($arr as $rv) {
					$related_voxes[] = $rv->convertForResponse();
				}
			}
		}

		$s_voxes = Vox::where('type', 'normal')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $filled_voxes)->take(9)->get();
		$s_voxes = $user->notRestrictedVoxesList($s_voxes);

		$suggested_voxes = [];
		foreach ($s_voxes as $sv) {
			$suggested_voxes[] = $sv->convertForResponse();
		}

		if($type == 'related') {
			return $related_voxes;
		} else {
			return $suggested_voxes;
		}
    }

	public function indexVoxes() {

		return Response::json( array(
			'voxes' => $this->featuredVoxes(),
		) );
    }

	public function getCountryIdByIp() {
		$country = null;

		$location = \GeoIP::getLocation();
        if(!empty($location)) {
            if(!empty($location['iso_code'])) {
                $c = Country::where('code', 'LIKE', $location['iso_code'])->first();
                if(!empty($c)) {
                    $country = $c->id;
                }
            }
        }     
		return $country;
	}

	public function welcomeSurvey() {
		$first = Vox::with('questions.translations')->where('type', 'home')->first();

		$total_questions = $first->questions->count() + 3;

		$welcome = $first->convertForResponse();
		$welcome['questions'] = $first->questions->toArray();

		$birth_years = [];
		for($i=(date('Y')-18);$i>=(date('Y')-90);$i--){
            $birth_years[$i] = $i;
		}
		
		return Response::json( array(
			'vox' => $first->convertForResponse(),
			'total_questions' => $total_questions,
			'countries' => ['' => '-'] + Country::with('translations')->get()->pluck('name', 'id')->toArray(),
			'country_id' => $user->country_id ?? $this->getCountryIdByIp() ?? '',
			'birth_years' => $birth_years,
		) );
	}
//
//        $headers =  getallheaders();
//        foreach($headers as $key=>$val){
//            Log::info('headers: '.$key . ': ' . $val);
//        }
//
//        Log::info('userrr vox: '.json_encode($user));


    public function doVox($slug) {
		$vox = Vox::whereTranslationLike('slug', $slug)->first();
		$user = Auth::guard('api')->user();

		if (!empty($vox) && $vox->type != 'hidden') {

			ini_set('max_execution_time', 0);
	        set_time_limit(0);
	        ini_set('memory_limit','1024M');

			if(!$user) {

				return Response::json( array(
					'vox' => $vox->convertForResponse(),
					'voxes' => $this->featuredVoxes(),
					'vox_type' => 'public',
				) );
			}

	        if($user->isBanned('vox')) {
	            return Response::json( array(
					'vox' => $vox->convertForResponse(),
					'related_voxes' => $this->relatedSuggestedVoxes($vox->id,'related'),
					'suggested_voxes' => $this->relatedSuggestedVoxes($vox->id,'suggested'),
					'restricted_description' => 'The target group of this survey consists of respondents with different demographics. No worries: We have plenty of other opportunities for you!',
					'vox_type' => 'restricted',
				) );
	        }

			if(($user->loggedFromBadIp() && !$user->is_dentist && $user->platform != 'external')) {

				$ul = new UserLogin;
	            $ul->user_id = $user->id;
	            $ul->ip = User::getRealIp();
	            $ul->platform = 'vox';
	            $ul->country = \GeoIP::getLocation()->country;

	            $userAgent = $_SERVER['HTTP_USER_AGENT'];
	            $dd = new DeviceDetector($userAgent);
	            $dd->parse();

	            if ($dd->isBot()) {
	                $ul->device = $dd->getBot();
	            } else {
	                $ul->device = $dd->getDeviceName();
	                $ul->brand = $dd->getBrandName();
	                $ul->model = $dd->getModel();
	                $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
	            }
	            
	            $ul->save();
	           
	            $action = new UserAction;
	            $action->user_id = $user->id;
	            $action->action = 'bad_ip';
	            $action->reason = 'Automatically - Bad IP ( vox questionnaire )';
	            $action->actioned_at = Carbon::now();
	            $action->save();

	            $user->logoutActions();
	            $user->removeTokens();
	            Auth::guard('api')->logout();

	            return Response::json( array(
					'logout' => true,
				) );
			}

			if( $user->madeTest($vox->id) ) {

				return Response::json( array(
					'vox' => $vox->convertForResponse(),
					'related_voxes' => $this->relatedSuggestedVoxes($vox->id,'related'),
					'suggested_voxes' => $this->relatedSuggestedVoxes($vox->id,'suggested'),
					'vox_type' => 'taken',
				) );
			}

			//restricted survey
			if ($user->isVoxRestricted($vox) || $vox->voxCountryRestricted($this->user)) {
				if ($user->isVoxRestricted($vox)) {
					$res_desc = 'The target group of this survey consists of respondents with different demographics. No worries: We have plenty of other opportunities for you!';
				} else {
					$res_desc = 'This survey reached the limit for users with your demographics. Check again later. No worries: We have plenty of other opportunities for you!';
				}

				return Response::json( array(
					'vox' => $vox->convertForResponse(),
					'related_voxes' => $this->relatedSuggestedVoxes($vox->id,'related'),
					'suggested_voxes' => $this->relatedSuggestedVoxes($vox->id,'suggested'),
					'restricted_description' => $res_desc,
					'vox_type' => 'restricted',
				) );
			}

			$daily_voxes = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->count();

			//daily limit reached
			if($daily_voxes >= 10) {
				$last_vox = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->orderBy('id', 'desc')->first();

				$now = Carbon::now()->subDays(1);
	            $time_left = $last_vox->created_at->diffInHours($now).':'.
	            str_pad($last_vox->created_at->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
	            str_pad($last_vox->created_at->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);

				return Response::json( array(
					'vox' => $vox->convertForResponse(),
					'voxes' => $this->featuredVoxes(),
					'vox_type' => 'limit',
					'time_left' => $time_left,	
				) );
			}

			$first = Vox::where('type', 'home')->first();
			$welcome_vox = '';
			if (!$user->madeTest($first->id)) {
				$welcome_vox = $first;
			}

	    	$cross_checks = [];
	    	$cross_checks_references = [];

	    	foreach ($vox->questions as $vq) {
		    	if (!empty($vq->cross_check)) {

		    		if (is_numeric($vq->cross_check)) {
		    			$va = VoxAnswer::where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
		    			$cross_checks[$vq->id] = $va ? $va->answer : null;
		    			$cross_checks_references[$vq->id] = $vq->cross_check;
		    		} else if($vq->cross_check == 'gender') {
		    			$cc = $vq->cross_check;
		    			$cross_checks[$vq->id] = $user->$cc == 'm' ? 1 : 2;
		    			$cross_checks_references[$vq->id] = 'gender';
		    		} else if($vq->cross_check == 'birthyear') {
		    			$cc = $vq->cross_check;
		    			$cross_checks[$vq->id] = $user->$cc;
		    			$cross_checks_references[$vq->id] = 'birthyear';
		    		} else {
		    			$cc = $vq->cross_check;
		    			$i=1;
		    			foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
		    				if($key==$user->$cc) {
		    					$cross_checks[$vq->id] = $i;
		    					$cross_checks_references[$vq->id] = $cc;
		    					break;
		    				}
		    				$i++;
		    			}
		    		}
		    	}
	    	}

			$list = VoxAnswer::where('vox_id', $vox->id)
			->with('question')
			->where('user_id', $user->id)
			->orderBy('id', 'ASC')
			->get();

			$answered = [];
			foreach ($list as $l) {
				if(!isset( $answered[$l->question_id] )) {
					$answered[$l->question_id] = $l->answer; //3
				} else {
					if(!is_array($answered[$l->question_id])) {
						$answered[$l->question_id] = [ $answered[$l->question_id] ]; // [3]
					}
					$answered[$l->question_id][] = $l->answer; // [3,5,7]
				}
			}

			$answered_without_skip = [];
			foreach ($list as $l) {
				if(!isset( $answered_without_skip[$l->question_id] )) {
					$answered_without_skip[$l->question_id] = ($l->question->type == 'number' && $l->answer == 0) || $l->question->cross_check == 'birthyear' ? 1 : $l->answer; //3
				} else {
					if(!is_array($answered_without_skip[$l->question_id])) {
						$answered_without_skip[$l->question_id] = [ $answered_without_skip[$l->question_id] ]; // [3]
					}
					$answered_without_skip[$l->question_id][] = ($l->question->type == 'number' && $l->answer == 0) || $l->question->cross_check == 'birthyear' ? 1 : $l->answer; // [3,5,7]
				}
			}

			$answered_without_skip_count = 0;
			foreach ($answered_without_skip as $key => $value) {
				if($value != 0) {
					$answered_without_skip_count++;
				}
			}

	        $first_question = null;
	        $first_question_num = 0;

        	if (!empty($welcome_vox)) {
	        	foreach ($welcome_vox->questions as $question) {
		    		$first_question_num++;
		    		if(!isset($answered[$question->id])) {
		    			$first_question = $question->id;
		    			break;
		    		}
		    	}
        	} else {

        		foreach ($vox->questions as $question) {
		    		$first_question_num++;
		    		if(!isset($answered[$question->id])) {
		    			$first_question = $question->id;
		    			break;
		    		}
		    	}
    		}


	        $total_questions = $vox->questions->count();

	        if (!$user->birthyear) {
	        	$total_questions++;
	        }
	        if (!$user->country_id) {
	        	$total_questions++;
	        }
	        if (!$user->gender) {
	        	$total_questions++;
	        }

	        foreach ($this->details_fields as $key => $value) {
	        	if($user->$key==null) {
	        		$total_questions++;		
	        	}
	        }

	        if (!empty($welcome_vox)) {
		        foreach ($welcome_vox->questions as $key => $value) {
		        	$total_questions++;		
		        }
	        }

	        $filled_voxes = $user->filledVoxes();
			$related_voxes = [];
			$related_voxes_ids = [];
			if ($vox->related->isNotEmpty()) {
				foreach ($vox->related as $r) {
					if (!in_array($r->related_vox_id, $filled_voxes)) {
						$related_voxes_ids[] = $r->related_vox_id;
					}
				}

				if (!empty($related_voxes_ids)) {
					foreach(Vox::whereIn('id', $related_voxes_ids)->get() as $rv) {
						$related_voxes[] = $rv;
					}
				}
			}

			$suggested_voxes = Vox::where('type', 'normal')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $filled_voxes)->take(9)->get();
			$suggested_voxes = $user->notRestrictedVoxesList($suggested_voxes);

			$birth_years = [];
			for($i=(date('Y')-18);$i>=(date('Y')-90);$i--){
	            $birth_years[$i] = $i;
			}

			return Response::json( array(
				'welcome_vox' => $welcome_vox,
				'related_voxes' => $related_voxes,
	            'suggested_voxes' => $suggested_voxes,
				'cross_checks' => $cross_checks,
				'cross_checks_references' => $cross_checks_references,
				'vox' => $vox->convertForResponse(),
				'vox_url' => $vox->getLink(),
				'voxes' => $this->featuredVoxes(),
				'vox_type' => 'to-take',
				'answered' => $answered,
				'real_questions' => $vox->questions->count(),
				'total_questions' => $total_questions,
				'first_question' => $first_question,
				'first_question_num' => $first_question_num,
				'answered_without_skip_count' => $answered_without_skip_count,
				'birthyear_options' => Vox::getBirthyearOptions(),
				'countries' => ['' => '-'] + Country::with('translations')->get()->pluck('name', 'id')->toArray(),
				'country_id' => $user->country_id ?? $this->getCountryIdByIp() ?? '',
				'birth_years' => $birth_years,
			) );
		}
	}

    public function getNextQuestion() {

	    return App\Http\Controllers\Vox\VoxController::getNextQuestionFunction(false, Auth::guard('api')->user(), true, false);
    }


	private function getAgeGroup($by) {

		$years = date('Y') - intval($by);
		$agegroup = 'more';
		if($years<=24) {
			$agegroup = '24';
		} else if($years<=34) {
			$agegroup = '34';
		} else if($years<=44) {
			$agegroup = '44';
		} else if($years<=54) {
			$agegroup = '54';
		} else if($years<=64) {
			$agegroup = '64';
		} else if($years<=74) {
			$agegroup = '74';
		}

		return $agegroup;

	}


	private function setupAnswerStats(&$answer) {
		$user = Auth::guard('api')->user();

        foreach (config('vox.stats_scales') as $df => $dv) {
        	if($df=='age') {
				$agegroup = $this->getAgeGroup($user->birthyear);
				$answer->$df = $agegroup;
        	} else {
        		if($user->$df!==null) {
	        		$answer->$df = $user->$df;
	        	}
        	}
        }
	}


    public function surveyAnswer() {

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

        $is_admin = $user->is_admin;

        if(!empty($user) && !empty(request('vox_id')) && !empty(Vox::find(request('vox_id'))) && !empty(request('type'))) {

	    	$ret = [
	    		'success' => true,
	    	];

	    	$q = request('question');
	    	$vox = Vox::find(request('vox_id'));
	    	$type = request('type');
	    	$answ = request('answer');

	    	$list = VoxAnswer::where('vox_id', $vox->id)
			->with('question')
			->where('user_id', $user->id)
			->orderBy('id', 'ASC')
			->get();

	    	$answered = [];
			foreach ($list as $l) {
				if(!isset( $answered[$l->question_id] )) {
					$answered[$l->question_id] = $l->answer; //3
				} else {
					if(!is_array($answered[$l->question_id])) {
						$answered[$l->question_id] = [ $answered[$l->question_id] ]; // [3]
					}
					$answered[$l->question_id][] = $l->answer; // [3,5,7]
				}
			}

            $cross_checks = [];
            $cross_checks_references = [];

            foreach ($vox->questions as $vq) {
                if (!empty($vq->cross_check)) {

                    if (is_numeric($vq->cross_check)) {
                        $va = VoxAnswer::where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
                        $cross_checks[$vq->id] = $va ? $va->answer : null;
                        $cross_checks_references[$vq->id] = $vq->cross_check;
                    } else if($vq->cross_check == 'gender') {
                        $cc = $vq->cross_check;
                        $cross_checks[$vq->id] = $user->$cc == 'm' ? 1 : 2;
                        $cross_checks_references[$vq->id] = 'gender';
                    } else if($vq->cross_check == 'birthyear') {
                        $cc = $vq->cross_check;
                        $cross_checks[$vq->id] = $user->$cc;
                        $cross_checks_references[$vq->id] = 'birthyear';
                    } else {
                        $cc = $vq->cross_check;
                        $i=0;
                        foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
                            if($key==$user->$cc) {
                                $cross_checks[$vq->id] = $i;
                                $cross_checks_references[$vq->id] = $cc;
                                break;
                            }
                            $i++;
                        }
                    }
                }
            }

	    	if( !isset( $answered[$q] ) ) {
	    		$doing_asl = false;
	    		if( 
			    	isset( $this->details_fields[$type] ) ||
			    	$type=='gender-question' ||
			    	$type=='birthyear-question' ||
			    	$type=='location-question'
				) {
			    	//I'm doing ASL questions!
					$doing_asl = true;
				}

	        	$found = $doing_asl ? true : false;
	        	foreach ($vox->questions as $question) {
	        		if($question->id == $q) {
	        			$found = $question;
	        			break;
	        		}
	        	}

	        	$first = Vox::where('type', 'home')->first();
				$welcome_vox = '';
				$welcome_vox_question_ids = [];
				if (!$user->madeTest($first->id)) {
					$welcome_vox = $first;
					$welcome_vox_question_ids = $welcome_vox->questions->pluck('id')->toArray();
				}

	        	if (!empty($welcome_vox)) {
		        	foreach ($welcome_vox->questions as $question) {
		        		if($question->id == $q) {
		        			$found = $question;
		        			break;
		        		}
		        	}
	        	}

	        	if($found) {
	        		$slist = VoxScale::get();
					$scales = [];
					foreach ($slist as $sitem) {
						$scales[$sitem->id] = $sitem;
					}

	        		$valid = false;
	        		$type = Request::input('type');
	        		$answer_count = $type == 'multiple' || $type == 'rank' || $type == 'scale' || $type == 'single' ? count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true) ) : 0;

	        		if ($type == 'skip') {
	        			$valid = true;
	        			$a = 0;

	        		} else if($type == 'previous') {
	        			$valid = true;
	        			$a = $answ;
	        		} else if ( isset( $this->details_fields[$type] ) ) {

	        			$should_reward = false;
	        			if($user->$type===null) {
	        				$should_reward = true;
	        			}

	        			if($answ === 0) {
	        				$user->$type = $answ.'';
	        			} else {
	        				$user->$type = $answ;
	        			}
	        			$user->save();

	        			if( isset( config('vox.stats_scales')[$type] ) ) {
	        				VoxAnswer::where('user_id', $user->id)->update([
		        				$type => $answ
		        			]);
	        			}
	        			$valid = true;
	        			$a = $answ;

	        			if( $should_reward ) {

		        			DcnReward::where('user_id', $user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
		        				array(
		        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
		        				))
		        			);
	        			}

	        		} else if ($type == 'location-question') {

	        			if($user->country_id===null) {
		        			DcnReward::where('user_id', $user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
		        				array(
		        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
		        				))
		        			);
	        			}
	        			//answer = 71,2312
	        			$country_id = $answ;
	        			$user->country_id = $country_id;
	        			VoxAnswer::where('user_id', $user->id)->update([
	        				'country_id' => $country_id
	        			]);
	        			$user->save();

	        			$a = $country_id;
	        			$valid = true;
	        		
	        		} else if ($type == 'birthyear-question') {

	        			if($user->birthyear===null || $user->birthyear===0) {
		        			DcnReward::where('user_id', $user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
		        				array(
		        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
		        				))
		        			);

		        			$user->birthyear = $answ;
		        			$user->save();
	        			}

	        			$agegroup = $this->getAgeGroup($answ);

	        			VoxAnswer::where('user_id', $user->id)->update([
	        				'age' => $agegroup
	        			]);

	        			$valid = true;
	        			$a = $answ;

	        		} else if ($type == 'gender-question') {

	        			if($user->gender===null) {
		        			DcnReward::where('user_id', $user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
		        				array(
		        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
		        				))
		        			);
	        			}
	        			$user->gender = $answ;
	        			$user->save();
	        			VoxAnswer::where('user_id', $user->id)->update([
	        				'gender' => $answ
	        			]);
	        			$valid = true;
	        			$a = $answ;

	        		} else if ($type == 'multiple' || $type == 'scale' || $type == 'rank') {


	        			$valid = true;
	        			$a = $answ;

	        			if(is_string($a)) {
	        				$a = explode(',', $a);
	        			}

	        			foreach ($a as $value) {
	        				if (!($value>=1 && $value<=$answer_count)) {
	        					$valid = false; 
	        					break;
	        				}
	        			}
	        			
	        		} else if ($type == 'single') {
	    				$a = intval($answ);
	    				$valid = $a>=1 && $a<=$answer_count;

	        		} else if ($type == 'number') {

	        			$cur_question = VoxQuestion::find($q);
	        			$min_num = intval(explode(':',$cur_question->number_limit)[0]);
	        			$max_num = intval(explode(':',$cur_question->number_limit)[1]);
	    				$a = intval($answ);
	    				$valid = $a>=$min_num && $a<=$max_num;
	        		}

	        		if( $valid ) {

	        			VoxAnswer::where('user_id', $user->id )->where('vox_id',$vox->id )->where('question_id', $q)->delete();

	        			$is_scam = false;

				        if($question->is_control) {

				        	if ($question->is_control == '-1') {
		        				if($type == 'single') {
					        		$is_scam = end($answered) != $a;
					        	} else if($type == 'multiple') {
					        		$end_answered = [];

					        		if (!is_array(end($answered))) {
					        			$end_answered[] = end($answered);
					        		} else {
					        			$end_answered = end($answered);
					        		}
					        		$is_scam = !empty(array_diff( $end_answered, $a ));
					        	}
				        	} else {
		        				if($type == 'single') {
				        			$is_scam = $question->is_control!=$a;
					        	} else if($type == 'multiple') {
					        		$is_scam = !empty(array_diff( explode(',', $question->is_control), $a ));
					        	}
				        	}
				        }

			        	if($is_scam && !$user->is_partner && !$is_admin) {
			        	// if(false) {
			        		
			        		$wrongs = UserSurveyWarning::where('user_id', $user->id)->where('action', 'wrong')->where('created_at', '>', Carbon::now()->addHours(-3)->toDateTimeString() )->count();
			        		$wrongs++;

			            	$new_wrong = new UserSurveyWarning;
			            	$new_wrong->user_id = $user->id;
			            	$new_wrong->action = 'wrong';
			            	$new_wrong->save();

	        				$ret['wrong'] = true;
	        				$prev_bans = $user->getPrevBansCount('vox', 'mistakes');

	        				if($wrongs==1 || ($wrongs==2 && !$prev_bans) ) {
	        					$ret['warning'] = true;
	        					$ret['img'] = url('new-vox-img/mistakes'.($prev_bans+1).'.png');
	        					$titles = [
	        						trans('vox.page.bans.warning-mistakes-title-1'),
	        						trans('vox.page.bans.warning-mistakes-title-2'),
	        						trans('vox.page.bans.warning-mistakes-title-3'),
	        						trans('vox.page.bans.warning-mistakes-title-4'),
		        				];
	        					$contents = [
	        						trans('vox.page.bans.warning-mistakes-content-1'),
	        						trans('vox.page.bans.warning-mistakes-content-2'),
	        						trans('vox.page.bans.warning-mistakes-content-3'),
	        						trans('vox.page.bans.warning-mistakes-content-4'),
	        					];
	        					if( $wrongs==2 && !$prev_bans ) {
	        						$ret['zman'] = url('new-vox-img/mistake2.png');
	        						$ret['title'] = trans('vox.page.bans.warning-mistakes-title-1-second');
	        						$ret['content'] = trans('vox.page.bans.warning-mistakes-content-1-second');
	        					} else {
	        						$ret['zman'] = url('new-vox-img/mistake1.png');
	        						$ret['title'] = $titles[$prev_bans];
		        					$ret['content'] = $contents[$prev_bans];
	        					}

	        					if( $wrongs==1 && !$prev_bans ) {
	        						$ret['action'] = 'roll-back';
	        						$ret['go_back'] = $this->goBack($answered, $list, $vox);
	        					} else {
	        						$ret['action'] = 'start-over';
	        						$ret['go_back'] = $vox->questions->first()->id;
									VoxAnswer::where('vox_id', $vox->id)
									->where('user_id', $user->id)
									->delete();
	        					}
	        				} else {
				            	UserSurveyWarning::where('user_id', $user->id)->where('action', 'wrong')->delete();

	        					$ban = $user->banUser('vox', 'mistakes', $vox->id);
	        					$ret['ban'] = true;
	        					$ret['ban_duration'] = $ban['days'];
	        					$ret['ban_times'] = $ban['times'];
	        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
	        					$titles = [
	        						trans('vox.page.bans.ban-mistakes-title-1'),
	        						trans('vox.page.bans.ban-mistakes-title-2'),
	        						trans('vox.page.bans.ban-mistakes-title-3'),
	        						trans('vox.page.bans.ban-mistakes-title-4', [
	        							'name' => $user->getNames()
	        						]),
	        					];
	        					$ret['title'] = $titles[$prev_bans];
	        					$contents = [
	        						trans('vox.page.bans.ban-mistakes-content-1'),
	        						trans('vox.page.bans.ban-mistakes-content-2'),
	        						trans('vox.page.bans.ban-mistakes-content-3'),
	        						trans('vox.page.bans.ban-mistakes-content-4'),
	        					];
	        					$ret['content'] = $contents[$prev_bans];

	        					//Delete all answers
								VoxAnswer::where('vox_id', $vox->id)
								->where('user_id', $user->id)
								->delete();
	        				}
			        	} else {

		        			if($type == 'skip') {
		        				$answer = new VoxAnswer;
						        $answer->user_id = $user->id;
						        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
						        $answer->question_id = $q;
						        $answer->answer = 0;
						        $answer->is_skipped = true;
						        $answer->country_id = $user->country_id;
						        $answer->save();
						        $answered[$q] = 0;
						        
		        			} else if($type == 'previous') {
		        				$answer = new VoxAnswer;
						        $answer->user_id = $user->id;
						        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
						        $answer->question_id = $q;
					        	$answer->answer = $a;
					        	$this->setupAnswerStats($answer);
						        $answer->country_id = $user->country_id;
						        $answer->save();
						        $answered[$q] = 0;
						        
		        			} else if($type == 'single') {

								$answer = new VoxAnswer;
						        $answer->user_id = $user->id;
						        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
						        if (in_array($q, $welcome_vox_question_ids)===true) {
						        	$answer->is_completed = 1;
						        	$answer->is_skipped = 0;
						        }
						        $answer->question_id = $q;
						        $answer->answer = $a;
						        $answer->country_id = $user->country_id;
						        $this->setupAnswerStats($answer);
						        $answer->save();
						        $answered[$q] = $a;

						        if( $found->cross_check ) {
						    		if (is_numeric($found->cross_check)) {
						    			$v_quest = VoxQuestion::where('id', $q )->first();

						    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
							    			$vcc = new VoxCrossCheck;
							    			$vcc->user_id = $user->id;
							    			$vcc->question_id = $found->cross_check;
							    			$vcc->old_answer = $cross_checks[$q];
							    			$vcc->save();
							    		}

						    			VoxAnswer::where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $found->cross_check )->update([
						    				'answer' => $a,
						    			]);

						    		} else if($found->cross_check == 'gender') {
					    				if (!empty($cross_checks) && $cross_checks[$q] != $a) {
					    					$vcc = new VoxCrossCheck;
							    			$vcc->user_id = $user->id;
							    			$vcc->question_id = $found->cross_check;
							    			$vcc->old_answer = $cross_checks[$q];
							    			$vcc->save();
							    		}
							    		// $user->gender = $a == 1 ? 'm' : 'f';
						    			// $user->save();

						    		} else {
						    			$cc = $found->cross_check;

						    			$i=0;
						    			foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
						    				if($i==($a-1)) {
								    			if (!empty($cross_checks) && $cross_checks[$q] != ($a-1)) {
									    			$vcc = new VoxCrossCheck;
									    			$vcc->user_id = $user->id;
									    			$vcc->question_id = $found->cross_check;
									    			$vcc->old_answer = $cross_checks[$q];
									    			$vcc->save();
									    		}
						    					$user->$cc = $key;
						    					$user->save();
						    					break;
						    				}
						    				$i++;
						    			}
						    		}
						        }

		        			} else if(isset( $this->details_fields[$type] ) || $type == 'location-question' || $type == 'birthyear-question' || $type == 'gender-question' ) {
		        				$answered[$q] = 1;
		        				$answer = null;

		        				if( !empty($found->cross_check) ) {
		        					if($found->cross_check == 'birthyear') {

						    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
					    					$vcc = new VoxCrossCheck;
							    			$vcc->user_id = $user->id;
							    			$vcc->question_id = $found->cross_check;
							    			$vcc->old_answer = $cross_checks[$q];
							    			$vcc->save();
							    		}
							    		// $user->birthyear = $a;
						    			// $user->save();

				        				$answer = new VoxAnswer;
								        $answer->user_id = $user->id;
								        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
								        if (in_array($q, $welcome_vox_question_ids)===true) {
								        	$answer->is_completed = 1;
						        			$answer->is_skipped = 0;
								        }
								        $answer->question_id = $q;
								        $answer->answer = 0;
								        $answer->country_id = $user->country_id;
						        		$this->setupAnswerStats($answer);
								        $answer->save();
								        $answered[$q] = 0;
						    		}
		        				}

		        			} else if($type == 'number') {
	        					$answer = new VoxAnswer;
						        $answer->user_id = $user->id;
						        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
						        if (in_array($q, $welcome_vox_question_ids)===true) {
						        	$answer->is_completed = 1;
					        		$answer->is_skipped = 0;
						        }
						        $answer->question_id = $q;
						        $answer->answer = $a;
						        $answer->country_id = $user->country_id;
					        	$this->setupAnswerStats($answer);
						        $answer->save();

							    $answered[$q] = $a;

		        			} else if($type == 'multiple') {
		        				foreach ($a as $value) {
		        					$answer = new VoxAnswer;
							        $answer->user_id = $user->id;
							        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
							        if (in_array($q, $welcome_vox_question_ids)===true) {
							        	$answer->is_completed = 1;
						        		$answer->is_skipped = 0;
							        }
							        $answer->question_id = $q;
							        $answer->answer = $value;
							        $answer->country_id = $user->country_id;
						        	$this->setupAnswerStats($answer);
							        $answer->save();
		        				}
							    $answered[$q] = $a;

		        			} else if($type == 'scale' || $type == 'rank') {
		        				$num = 0;
		        				foreach ($a as $value) {
		        					$answer = new VoxAnswer;
							        $answer->user_id = $user->id;
							        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
							        if (in_array($q, $welcome_vox_question_ids)===true) {
							        	$answer->is_completed = 1;
						        		$answer->is_skipped = 0;
							        }
							        $answer->question_id = $q;
							        $answer->answer = $num+1;
							        $answer->scale = $value;
							        $answer->country_id = $user->country_id;
						        	$this->setupAnswerStats($answer);
							        $answer->save();

							        $num++;
		        				}
							    $answered[$q] = $a;
		        			}

		        		}

	    				$reallist = $list->filter(function ($value, $key) {
						    return !$value->is_skipped;
						});

	    				$ppp = 10;
	        			if( $reallist->count() && $reallist->count()%$ppp==0 && !$user->is_partner && !$is_admin) {
	        			// if(false) {

	        				$pagenum = $reallist->count()/$ppp;
	        				$start = $reallist->forPage($pagenum, $ppp)->first();
	        				
					        $diff = Carbon::now()->diffInSeconds( $start->created_at );
					        $normal = $ppp*2;
					        if($normal > $diff) {

					        	$warned_before = UserSurveyWarning::where('user_id', $user->id)->where('action', 'too_fast')->where('created_at', '>', Carbon::now()->addHours(-3)->toDateTimeString() )->count();
					        	if(!$warned_before) {
					        		$new_too_fast = new UserSurveyWarning;
					            	$new_too_fast->user_id = $user->id;
					            	$new_too_fast->action = 'too_fast';
					            	$new_too_fast->save();
					        	} else {
					        		UserSurveyWarning::where('user_id', $user->id)->where('action', 'too_fast')->delete();
					        	}

	        					$prev_bans = $user->getPrevBansCount('vox', 'too-fast');
		        				$ret['toofast'] = true;
		        				if(!$warned_before) {
		        					$ret['warning'] = true;
		        					$ret['img'] = url('new-vox-img/ban-warning-fast-'.($prev_bans+1).'.png');
		        					$titles = [
	        							trans('vox.page.bans.warning-too-fast-title-1'),
	        							trans('vox.page.bans.warning-too-fast-title-2'),
	        							trans('vox.page.bans.warning-too-fast-title-3'),
	        							trans('vox.page.bans.warning-too-fast-title-4'),
		        					];
		        					$ret['title'] = $titles[$prev_bans];
		        					$contents = [
	        							trans('vox.page.bans.warning-too-fast-content-1'),
	        							trans('vox.page.bans.warning-too-fast-content-2'),
	        							trans('vox.page.bans.warning-too-fast-content-3'),
	        							trans('vox.page.bans.warning-too-fast-content-4'),
		        					];
		        					$ret['content'] = $contents[$prev_bans];

		        				} else {
	            					$ban = $user->banUser('vox', 'too-fast', $vox->id);
	            					$ret['ban'] = true;
	            					$ret['ban_duration'] = $ban['days'];
	            					$ret['ban_times'] = $ban['times'];
		        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
		        					$titles = [
	        							trans('vox.page.bans.ban-too-fast-title-1'),
	        							trans('vox.page.bans.ban-too-fast-title-2'),
	        							trans('vox.page.bans.ban-too-fast-title-3'),
	        							trans('vox.page.bans.ban-too-fast-title-4',[
	        								'name' => $user->getNames()
	        							]),
		        					];
		        					$ret['title'] = $titles[$prev_bans];
		        					$contents = [
	        							trans('vox.page.bans.ban-too-fast-content-1'),
	        							trans('vox.page.bans.ban-too-fast-content-2'),
	        							trans('vox.page.bans.ban-too-fast-content-3'),
	        							trans('vox.page.bans.ban-too-fast-content-4'),
		        					];
		        					$ret['content'] = $contents[$prev_bans];

		        					//Delete all answers
									VoxAnswer::where('vox_id', $vox->id)
									->where('user_id', $user->id)
									->delete();
		        				}
					        }
	        			}

	    				// dd($answered, count($vox->questions));

	    				if (!empty($welcome_vox_question_ids) && $q==end($welcome_vox_question_ids)) {
							$reward = new DcnReward;
					        $reward->user_id = $user->id;
					        $reward->reference_id = 11;
					        $reward->type = 'survey';
					        $reward->platform = 'vox';
					        $reward->reward = 100;

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

				        if(count($answered) == count($vox->questions)) {
				        	$reward = DcnReward::where('user_id', $user->id)->where('reference_id', $vox->id)->where('platform', 'vox')->where('type', 'survey')->first();

				        	if (empty($reward)) {
								$reward = new DcnReward;
						        $reward->user_id = $user->id;
						        $reward->reference_id = $vox->id;
						        $reward->platform = 'vox';
						        $reward->type = 'survey';
						    }
					        $reward->reward = $vox->getRewardForUser($user->id);
					        $start = $list->first()->created_at;
					        $diff = Carbon::now()->diffInSeconds( $start );
					        $normal = count($vox->questions)*2;
					        $reward->seconds = $diff;

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
	        				$ret['balance'] = $user->getTotalBalance('vox');

	        				//---

	        				VoxAnswer::where('user_id', $user->id)->where('vox_id', $vox->id)->update(['is_completed' => 1]);

	        				$vox->recalculateUsersPercentage($user);

	                        //----

	                        if($user->invited_by && !empty($user->invitor)) {

	                        	$inv = UserInvite::where('user_id', $user->invited_by)
					            ->where(function ($query) {
					                $query->where('platform', '!=', 'trp')
					                ->orWhere('platform', null);
					            })
					            ->where('invited_id', $user->id)
					            ->whereNull('rewarded')
					            ->first();

	                            if(!empty($inv) && !$inv->dont_rewarded) {

	                            	$reward = new DcnReward;
							        $reward->user_id = $user->invited_by;
							        $reward->reference_id = $user->id;
							        $reward->type = 'invitation';
							        $reward->platform = 'vox';
							        $reward->reward = Reward::getReward('reward_invite');

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

	                                $inv->rewarded = true;
	                                $inv->save();
	                                
	                                if($user->invitor->is_dentist) {
	                                    $user->invitor->sendGridTemplate( 82, [
	                                        'who_joined_name' => $user->getNames()
	                                    ], 'vox' );
	                                } else {
	                                	$user->invitor->sendGridTemplate( 113, [
	                                        'who_joined_name' => $user->getNames()
	                                    ], 'vox' );
	                                }
	                            }
	                        }

	                        if ($user->platform == 'external') {
	                            $curl = curl_init();
								curl_setopt_array($curl, array(
									CURLOPT_RETURNTRANSFER => 1,
									CURLOPT_POST => 1,
									CURLOPT_URL => 'https://hub-app-api.dentacoin.com/internal-api/push-notification/',
									CURLOPT_SSL_VERIFYPEER => 0,
								    CURLOPT_POSTFIELDS => array(
								        'data' => User::encrypt(json_encode(array('type' => 'reward-won', 'id' => $user->id, 'value' => Reward::getReward('reward_invite'))))
								    )
								));
								 
								$resp = json_decode(curl_exec($curl));
								curl_close($curl);

	                        } else if(!empty($user->patient_of)) {

	                        	$curl = curl_init();
								curl_setopt_array($curl, array(
									CURLOPT_RETURNTRANSFER => 1,
									CURLOPT_POST => 1,
									CURLOPT_URL => 'https://dcn-hub-app-api.dentacoin.com/manage-push-notifications',
									CURLOPT_SSL_VERIFYPEER => 0,
								    CURLOPT_POSTFIELDS => array(
								        'data' => User::encrypt(json_encode(array('type' => 'reward-won', 'id' => $user->id, 'value' => Reward::getReward('reward_invite'))))
								    )
								));
								 
								$resp = json_decode(curl_exec($curl));
								curl_close($curl);
	                        }

				        }
	        		} else {
	        			$ret['success'] = false;
	        		}
	        	}
	    	}

			if($user->isVoxRestricted($vox)) {
				$ret['success'] = false;
				$ret['restricted'] = true;
			}

			if( $ret['success'] ) {
				$open_recommend = false;
				$filled_voxes = $user->filledVoxes();
				if ((count($filled_voxes) == 5 || count($filled_voxes) == 10 || count($filled_voxes) == 20 || count($filled_voxes) == 50) && empty($user->fb_recommendation)) {
					$open_recommend = true;
				}

				$ret['recommend'] = $open_recommend;
				$ret['vox_id'] = $vox->id;
				$ret['question_id'] = !empty($q) ? $q : null;

				$ret['related_voxes'] = $this->relatedSuggestedVoxes($vox->id,'related');
				$ret['suggested_voxes'] = $this->relatedSuggestedVoxes($vox->id,'suggested');
			}
        } else {
        	$ret = [
	    		'success' => false,
	    	];
        }

    	return Response::json( $ret );
    }


	public function startOver() {

		$ret = [
			'success' => false,
		];

		$user = Auth::guard('api')->user();

		if(!empty($user) && !empty(request('vox_id')) && !empty(Vox::find(request('vox_id')))) {

			$vox = Vox::find(request('vox_id'));

	        VoxAnswer::where('vox_id', request('vox_id'))
			->where('user_id', $user->id)
			->delete();

			$ret = [
				'success' => true,
				// 'first_q' => $vox->questions->first()->id
			];
		}

		return Response::json( $ret );
	}


	public function welcomeSurveyReward() {

		$first = Vox::where('type', 'home')->first();
        $has_test = request('answers');
        $user = Auth::guard('api')->user();

        $ret = [
			'success' => false,
		];

        if( $has_test ) {

            $first_question_ids = $first->questions->pluck('id')->toArray();

            if(!$user->madeTest($first->id)) {

                foreach (json_decode($has_test, true) as $q_id => $a_id) {
                    if($q_id == 'birthyear') {
                        $user->birthyear = $a_id;
                        $user->save();
                    } else if($q_id == 'gender') {
                        $user->gender = $a_id;
                        $user->save();
                    } else if($q_id == 'location') {
                        // $user->gender = $a_id;
                        // $user->save();
                    } else {
                        $answer = new VoxAnswer;
                        $answer->user_id = $user->id;
                        $answer->vox_id = $first->id;
                        $answer->question_id = $q_id;
                        $answer->answer = $a_id;
                        $answer->country_id = $user->country_id;
                        $answer->is_completed = 1;
                        $answer->is_skipped = 0;
                        $answer->save();
                    }
                }
                $reward = new DcnReward;
                $reward->user_id = $user->id;
                $reward->reference_id = $first->id;
                $reward->type = 'survey';
                $reward->reward = $first->getRewardTotal();
                $reward->platform = 'vox';

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

                $ret = [
					'success' => true,
					'balance' => $user->getTotalBalance('vox'),
				];
            }
        }

        return Response::json( $ret );
	}


	public function dailyLimitReached() {

        $user = Auth::guard('api')->user();
        $ret = [
			'success' => false,
		];

        if($user) {
			$daily_voxes = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->count();

			if($daily_voxes >= 10) {
				$ret = [
					'success' => false,
				];
			} else {

				$all_taken = false;
				$taken = $user->filledVoxes();
				$untaken_voxes = $user->voxesTargeting();
				$untaken_voxes = $untaken_voxes->where('type', 'normal')->count();
				if($untaken_voxes == count($taken)) {
					$all_taken = true;
				}

				if($all_taken) {
					$ret = [
						'success' => false,
					];
				} else {
					$ret = [
						'success' => true,
					];
				}
			}
        }

        return Response::json( $ret );
	}

	public function getBanTimeLeft() {

        $user = Auth::guard('api')->user();

		$current_ban = $user->isBanned('vox');

		$time_left = null;

		if($current_ban && $current_ban->expires ) {
            $now = Carbon::now();
            $time_left = $current_ban->expires->diffInHours($now).':'.
            str_pad($current_ban->expires->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
            str_pad($current_ban->expires->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);
		}

        return Response::json( [
        	'time_left' => $time_left,
        ] );
	}


	public function getBanInfo() {

        $user = Auth::guard('api')->user();

        $ret = [
    		'ban' => false,
    	];

        if(!empty($user)) {
        	
	        $current_ban = $user->isBanned('vox');

	        if($current_ban) {

	        	if($current_ban->type == 'mistakes') {

					$prev_bans = $user->getPrevBansCount('vox', 'mistakes');

					$days = 0;
			        if($prev_bans==1) {
			            $days = 1;
			        } else if($prev_bans==2) {
			            $days = 3;
			        } else if($prev_bans==3) {
			            $days = 7;
			        }

					$ret['ban'] = true;
					$ret['ban_duration'] = $days;
					$ret['ban_times'] = $prev_bans;
					$ret['img'] = url('new-vox-img/ban'.($prev_bans).'.png');
					$titles = [
						trans('vox.page.bans.ban-mistakes-title-1'),
						trans('vox.page.bans.ban-mistakes-title-2'),
						trans('vox.page.bans.ban-mistakes-title-3'),
						trans('vox.page.bans.ban-mistakes-title-4', [
							'name' => $user->getNames()
						]),
					];
					$ret['title'] = $titles[$prev_bans - 1];
					$contents = [
						trans('vox.page.bans.ban-mistakes-content-1'),
						trans('vox.page.bans.ban-mistakes-content-2'),
						trans('vox.page.bans.ban-mistakes-content-3'),
						trans('vox.page.bans.ban-mistakes-content-4'),
					];
					$ret['content'] = $contents[$prev_bans - 1];

	        	} else if($current_ban->type == 'too-fast') {

					$prev_bans = $user->getPrevBansCount('vox', 'too-fast');

					$days = 0;
			        if($prev_bans==1) {
			            $days = 1;
			        } else if($prev_bans==2) {
			            $days = 3;
			        } else if($prev_bans==3) {
			            $days = 7;
			        }

					$ret['ban'] = true;
					$ret['ban_duration'] = $days;
					$ret['ban_times'] = $prev_bans;
					$ret['img'] = url('new-vox-img/ban'.($prev_bans).'.png');
					$titles = [
						trans('vox.page.bans.ban-too-fast-title-1'),
						trans('vox.page.bans.ban-too-fast-title-2'),
						trans('vox.page.bans.ban-too-fast-title-3'),
						trans('vox.page.bans.ban-too-fast-title-4',[
							'name' => $user->getNames()
						]),
					];
					$ret['title'] = $titles[$prev_bans - 1];
					$contents = [
						trans('vox.page.bans.ban-too-fast-content-1'),
						trans('vox.page.bans.ban-too-fast-content-2'),
						trans('vox.page.bans.ban-too-fast-content-3'),
						trans('vox.page.bans.ban-too-fast-content-4'),
					];
					$ret['content'] = $contents[$prev_bans - 1];
	        	}
	        }
        }

        return Response::json( $ret );
    }

    public function dentistRequestSurvey() {
        $user = Auth::guard('api')->user();

        if(!empty($user) && $user->is_dentist) {

			$validator = Validator::make(Request::all(), [
                'title' => array('required', 'min:6'),
                'target' => array('required', 'in:worldwide,specific'),
                'target_countries' => array('required_if:target,==,specific'),
                'other_specifics' => array('required'),
                'topics' => array('required'),
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }

                return Response::json( $ret );
            } else {
            	$target_countries = [];
				foreach (request('target_countries') as $v) {
					$target_countries[] = Country::find($v['id'])->name;
				}
      
            	$mtext = 'New survey request from '.$user->getNames().'
	                
		        Link to CMS: '.url("/cms/users/edit/".$user->id).'
		        Survey title: '.request('title').'
		        Survey target group location/s: '.request('target');

		        if (request('target') == 'specific') {
		        	$mtext .= '
		        Survey target group countries: '.implode(',', $target_countries);
		        }
		        
		        $mtext .= '
		        Other specifics of survey target group: '.request('other_specifics').'
		        Survey topics and the questions: '.request('topics');

		        Mail::raw($mtext, function ($message) use ($user) {

		            $sender = config('mail.from.address-vox');
		            $sender_name = config('mail.from.name-vox');

		            $message->from($sender, $sender_name);
		            $message->to( 'gergana@youpluswe.com' );
		            $message->to( 'dentavox@dentacoin.com' );
		            $message->to( 'donika.kraeva@dentacoin.com' );
		            $message->replyTo($user->email, $user->getNames());
		            $message->subject('Survey Request');
		        });

                return Response::json( [
                    'success' => true,
                ] );
            }
		}
    }

    public function recommendDentavox() {

    	$user = Auth::guard('api')->user();

    	if(!empty($user)) {
    		
			$validator = Validator::make(Request::all(), [
                'scale' => array('required'),
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                foreach ($msg as $field => $errors) {
                    $ret['messages'][$field] = implode(', ', $errors);
                }

                return Response::json( $ret );
            } else {

            	if(request('recommend_id')) {
            		$new_recommendation = Recommendation::find(request('recommend_id'));

                } else {
                	$new_recommendation = new Recommendation;
                	$new_recommendation->save();
                }
        		
        		$new_recommendation->user_id = $user->id;
        		$new_recommendation->scale = request('scale');
        		$new_recommendation->save();

            	if (intval(request('scale')) > 3) {
            		$user->fb_recommendation = false;
            		$user->save();

            		return Response::json( [
                		'recommend_id' => $new_recommendation->id,
	                    'success' => true,
	                    'recommend' => true,
	                    'description' => false,
	                ] );
            	}

            	if (intval(request('scale')) <= 3) {
            		$user->fb_recommendation = true;
            		$user->save();
            	}

            	if (!empty(request('description'))) {
            		$new_recommendation->description = request('description');
            		$new_recommendation->save();

            		return Response::json( [
	                    'success' => true,
		                'recommend' => false,
		                'description' => true,
	                ] );
            	}

                return Response::json( [
                	'recommend_id' => $new_recommendation->id,
                    'success' => true,
	                'recommend' => false,
		            'description' => false,
                ] );
            }
		}
    }

    public function encryptUserToken() {

    	if(!empty(request('token'))) {
    		return Response::json( [
    			'token' => User::encrypt(request('token')),
	        	'success' => true,
	        ] );
    	}

        return Response::json( [
        	'success' => false,
        ] );
    }

    public function isDentacoinDown() {

        $host = 'dentacoin.com';

        if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
            fclose($socket);

            return Response::json( [
                'success' => false,
            ] );
        } else {
            return Response::json( [
                'success' => true,
            ] );
        }
    }

    public function isOnline() {

        return Response::json( [
            'success' => true,
        ] );
    }

    public function saveUserDevice() {

    	if(request('token')) {

    		$token = request('token');
    		$existing_device = UserDevice::where('device_token', $token)->first();

    		if(!empty($existing_device)) {
    			$existing_device->delete();
    		}

			$new_device = new UserDevice;
			$new_device->user_id = Auth::guard('api')->user() ? Auth::guard('api')->user()->id : null;
			$new_device->device_token = $token;
			$new_device->save();

	    	return Response::json( [
	            'success' => true,
	        ] );
    	}

    	return Response::json( [
            'success' => false,
        ] );

    }

    public function socialProfile() {

    	$user = Auth::guard('api')->user();

    	if(!empty($user)) {

    		Log::info(request()->file('avatar'));
    		Log::info('avatar');
			$user->addImage(Image::make( request()->file('avatar') )->orientate());

	    	return Response::json( [
	            'success' => true,
	        ] );
	    }

	    return Response::json( [
            'success' => false,
        ] );
    }

}