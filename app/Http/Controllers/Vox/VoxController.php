<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Auth;
use App;
use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Vox;
use App\Models\VoxAnswer;
use App\Models\VoxReward;
use App\Models\VoxQuestion;
use App\Models\VoxScale;
use App\Models\UserInvite;
use App\Models\Dcn;
use App\Models\Reward;


class VoxController extends FrontController
{
	public function home($locale=null, $id) {
		$vox = Vox::find($id);
		return $this->dovox($locale, $vox);
	}
	public function home_slug($locale=null, $slug) {
		$vox = Vox::whereTranslationLike('slug', $slug)->first();
		return $this->dovox($locale, $vox);
	}
	public function dovox($locale=null, $vox) {
		$this->current_page = 'questionnaire';
		$doing_details = false;
		$doing_asl = false;

		if(empty($vox) || (!$this->user->is_verified || !$this->user->email) ) {
			return redirect( getLangUrl('/') );
		} else if( $this->user->madeTest($vox->id) ) {
		    $qtype = Request::input('type');
		    if(
		    	$qtype=='gender-question' ||
		    	$qtype=='birthyear-question' ||
		    	$qtype=='location-question'
			) {
		    	//I'm doing ASL questions!
				$doing_asl = true;
			} else {

				$q = Request::input('question');
				$qobj = VoxQuestion::find($q);
				if($qobj && $qobj->vox_id==34) {
					$vox = Vox::find(34);
					//I'm doing demographics
					$doing_details = true;
				} else {
					return redirect( getLangUrl('stats/'.$vox->id) );					
				}
			}
		}

        if($this->user->isBanned('vox')) {
            return redirect(getLangUrl('profile/bans'));
        }


		$list = VoxAnswer::where('vox_id', $vox->id)
		->where('user_id', $this->user->id)
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

		$not_bot = session('not_not-'.$vox->id);


		if(Request::input('goback') && !empty($this->admin)) {
			if(!empty($answered)) {
				$lastkey = null;
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
						->where('user_id', $this->user->id)
						->where('question_id', $question->id)
						->delete();
					}
				}
			}

            return redirect( $vox->getLink() );
		}

		$slist = VoxScale::get();
		$scales = [];
		foreach ($slist as $sitem) {
			$scales[$sitem->id] = $sitem;
		}

        if(Request::isMethod('post')) {
        	$ret = [
        		'success' => true,
        	];
        	if(Request::input('captcha')) {
	            $captcha = false;
	            $cpost = [
	                'secret' => env('CAPTCHA_SECRET'),
	                'response' => Request::input('captcha'),
	                'remoteip' => Request::ip()
	            ];
	            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
	            curl_setopt($ch, CURLOPT_HEADER, 0);
	            curl_setopt ($ch, CURLOPT_POST, 1);
	            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
	            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
	            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	            $response = curl_exec($ch);
	            curl_close($ch);
	            if($response) {
	                $api_response = json_decode($response, true);
	                if(!empty($api_response['success'])) {
	                    $captcha = true;
	                }
	            }

	            if(!$captcha) {
	            	$ret['success'] = false;
	            } else {
	            	session([
	            		'not_not-'.$vox->id => true,
	            		'reward-for-'.$vox->id => $vox->getRewardTotal()
	            	]);
	            }

        	} else {

	        	$q = Request::input('question');


	        	if($doing_details || (!isset( $answered[$q] ) && $not_bot)) {

		        	$found = $doing_asl ? true : false;
		        	foreach ($vox->questions as $question) {
		        		if($question->id == $q) {
		        			$found = $question;
		        			break;
		        		}
		        	}

		        	if($found) {
		        		$valid = false;
		        		$type = Request::input('type');

		        		$answer_count = count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true) );

		        		if ($type == 'skip') {
		        			$valid = true;
		        			$a = 0;

		        		} else if ($type == 'location-question') {
		        			//answer = 71,2312
		        			list($country_id, $city_id) = explode(',', Request::input('answer'));
		        			$this->user->city_id = $city_id;
		        			$this->user->country_id = $country_id;
		        			$this->user->save();
		        			$a = $city_id;
		        			$valid = true;
		        		
		        		} else if ($type == 'birthyear-question') {

		        			$this->user->birthyear = Request::input('answer');
		        			$this->user->save();
		        			$valid = true;
		        			$a = Request::input('answer');

		        		} else if ($type == 'gender-question') {
		        			$this->user->gender = Request::input('answer');
		        			$this->user->save();
		        			$valid = true;
		        			$a = Request::input('answer');

		        		} else if ($type == 'multiple') {

		        			$valid = true;
		        			$a = Request::input('answer');
		        			foreach ($a as $value) {
		        				if (!($value>=1 && $value<=$answer_count)) {
		        					$valid = false; 
		        					break;
		        				}
		        			}
		        			
		        		} else if($type == 'scale') {
	        				
		        			$valid = true;
		        			$a = Request::input('answer');
		        			foreach ($a as $k => $value) {
		        				if (!($value>=1 && $value<=$answer_count)) {
		        					$valid = false; 
		        					break;
		        				}
		        			}

		        		} else if ($type == 'single') {
	        				$a = intval(Request::input('answer'));
	        				$valid = $a>=1 && $a<=$answer_count;
		        		}



		        		if( $valid ) {
		        			VoxAnswer::where('user_id', $this->user->id )->where('vox_id',$vox->id )->where('question_id', $q)->delete();

		        			if($type == 'single') {

								$answer = new VoxAnswer;
						        $answer->user_id = $this->user->id;
						        $answer->vox_id = $vox->id;
						        $answer->question_id = $q;
						        $answer->answer = $a;
						        $answer->country_id = $this->user->country_id;
						        if($question->is_control) {

						        	if ($question->is_control == '-1') {
						        		$answer->is_scam = end($answered) != $a;
						        	} else {
						        		$answer->is_scam = $question->is_control!=$a;
						        	}
						        }
					        	if($answer->is_scam && $question->go_back) {
					        		$wrongs = intval(session('wrongs'));
					        		$wrongs++;
					            	session([
					            		'wrongs' => $wrongs
					            	]);

					        		$wrongs_test = intval(session('wrongs-'.$vox->id));
					        		$wrongs_test++;
					            	session([
					            		'wrongs-'.$vox->id => $wrongs_test
					            	]);

			        				$ret['wrong'] = true;
			        				$ret['go_back'] = $question->go_back;
			        				$ret['mistake_count'] = $wrongs_test;
			        				$ret['mistakes_left'] = 10-$wrongs_test;
			        				$counter = 0;
			        				foreach ($answered as $key => $value) {
			        					$counter++;
			        					// if($counter>=$question->go_back) {
			        					// 	$value->delete();
			        					// }
			        				}
			        				if($wrongs>10) {
		            					$ret['ban_type'] = $this->user->banUser('vox', 'mistakes');
		            					$ret['ban'] = getLangUrl('profile/bans');
			        				}
					        	} else {
						        	$answer->save();
							        $answered[$q] = $a;
						        }

		        			} else if($type == 'location-question' || $type == 'birthyear-question' || $type == 'gender-question' ) {
		        				$answered[$q] = 1;
		        				$answer = null;
		        			} else if($type == 'skip') {
		        				$answer = new VoxAnswer;
						        $answer->user_id = $this->user->id;
						        $answer->vox_id = $vox->id;
						        $answer->question_id = $q;
						        $answer->answer = 0;
						        $answer->is_skipped = true;
						        $answer->country_id = $this->user->country_id;
						        $answer->save();
						        $answered[$q] = 0;
		        			} else if($type == 'multiple') {
		        				foreach ($a as $value) {
		        					$answer = new VoxAnswer;
							        $answer->user_id = $this->user->id;
							        $answer->vox_id = $vox->id;
							        $answer->question_id = $q;
							        $answer->answer = $value;
							        $answer->country_id = $this->user->country_id;
							        $answer->save();
		        				}
							    $answered[$q] = $a;
		        			} else if($type == 'scale') {
		        				foreach ($a as $k => $value) {
		        					$answer = new VoxAnswer;
							        $answer->user_id = $this->user->id;
							        $answer->vox_id = $vox->id;
							        $answer->question_id = $q;
							        $answer->answer = $k+1;
							        $answer->scale = $value;
							        $answer->country_id = $this->user->country_id;
							        $answer->save();
		        				}
							    $answered[$q] = $a;
		        			}


	        				if( $answer && $answer->is_scam ) {
	        					if($this->user->vox_should_ban()) {
	            					$ret['ban_type'] = $this->user->banUser('vox', 'mistakes');
	            					$ret['ban'] = getLangUrl('profile/bans');
		        				}
	        				}


	        				// dd($answered, count($vox->questions));

					        if(count($answered) == count($vox->questions)) {
								$reward = new VoxReward;
						        $reward->user_id = $this->user->id;
						        $reward->vox_id = $vox->id;
						        $reward->reward = $vox->getRewardForUser($this->user->id);
						        $reward->mistakes = intval(session('wrongs-'.$vox->id));
						        $start = $list->first()->created_at;
						        $diff = Carbon::now()->diffInSeconds( $start );
						        $normal = count($vox->questions)*5;
						        if($normal > $diff) {
						        	$reward->is_scam = true;
						        }
						        $reward->seconds = $diff;

						        $reward->save();
		        				$ret['balance'] = $this->user->getVoxBalance();

		        				if( $reward->is_scam ) {
		        					if($this->user->vox_should_ban()) {
	            						$ret['ban_type'] = $this->user->banUser('vox', 'too-fast');
	            						$ret['ban'] = getLangUrl('profile/bans');
			        				}
		        				} else {

		                            if($this->user->invited_by) {
		                                $inv = UserInvite::where('user_id', $this->user->invited_by)->where('invited_id', $this->user->id)->first();
		                                if(!empty($inv) && !$inv->rewarded) {
		                                    $tmp = Dcn::send($this->user->invitor, $this->user->invitor->my_address(), Reward::getReward('reward_invite'), 'invite-reward', $inv->id, true);
		                                    $inv->rewarded = true;
		                                    $inv->save();

		                                    $this->user->invitor->sendTemplate( 22, [
		                                        'who_joined_name' => $this->user->getName()
		                                    ] );
		                                }
		                            }


									VoxAnswer::where('vox_id', $vox->id)
									->where('user_id', $this->user->id)
									->update(['is_completed', true]);

		        				}
					        }
		        		} else {
		        			$ret['success'] = false;
		        		}
		        	}
	        		
	        	}
        	}

        	return Response::json( $ret );
        }

        $first_question = null;
        $first_question_num = 0;
        if($not_bot) {
        	foreach ($vox->questions as $question) {
	    		$first_question_num++;
	    		if(!isset($answered[$question->id])) {
	    			$first_question = $question->id;
	    			break;
	    		}
	    	}
        } else {
	    	$first_question_num++;
        }


        $details_test = null;

        if(!$this->user->madeTest(34)) {        	
        	$details_test = Vox::find(34);
        }


        $real_questions = $vox->questions->count();

        if (!$this->user->birthyear) {
        	$real_questions++;
        }
        if (!$this->user->city_id && !$this->user->country_id) {
        	$real_questions++;
        }
        if (!$this->user->gender) {
        	$real_questions++;
        }
        if(!$this->user->madeTest(34)) {        	
        	$real_questions += Vox::find(34)->questions->count();
        }

		return $this->ShowVoxView('vox', array(
			'not_bot' => $not_bot,
			'vox' => $vox,
			'scales' => $scales,
			'answered' => $answered,
			'details_test' => $details_test,
			'real_questions' => $real_questions,
			'first_question' => $first_question,
			'first_question_num' => $first_question_num,
			'js' => [
				'vox.js'
			],
            'seo_title' => trans('vox.seo.questionnaire.title', [
                'title' => $vox->translate(App::getLocale())->seo_title,
                'description' => $vox->translate(App::getLocale())->seo_description
            ]),
            'seo_description' => trans('vox.seo.questionnaire.description', [
                'title' => $vox->translate(App::getLocale())->seo_title,
                'description' => $vox->translate(App::getLocale())->seo_description
            ]),
            'social_title' => trans('vox.social.questionnaire.title', [
                'title' => $vox->translate(App::getLocale())->seo_title,
                'description' => $vox->translate(App::getLocale())->seo_description
            ]),
            'social_description' => trans('vox.social.questionnaire.description', [
                'title' => $vox->translate(App::getLocale())->seo_title,
                'description' => $vox->translate(App::getLocale())->seo_description
            ]),
        ));
	}
}