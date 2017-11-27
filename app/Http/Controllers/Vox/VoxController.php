<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Auth;
use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Vox;
use App\Models\VoxAnswer;
use App\Models\VoxReward;


class VoxController extends FrontController
{
	public function home($locale=null, $id) {
		$vox = Vox::find($id);
		if(empty($vox)) {
			return redirect( getLangUrl('/') );
		} else if( $this->user->madeTest($id) ) {
			return redirect( getLangUrl('stats/'.$vox->id) );			
		}

		$list = VoxAnswer::where('vox_id', $vox->id)
		->where('user_id', $this->user->id)
		->get();
		$answered = [];
		foreach ($list as $l) {
			$answered[$l->question_id] = $l;
		}

		$not_bot = session('not_not-'.$vox->id);

        if(Request::isMethod('post')) {
        	$ret = [
        		'success' => true,
        	];
        	if(Request::input('captcha')) {
	            $captcha = false;
	            $cpost = [
	                'secret' => '6LdmpjQUAAAAAF_3NBc2XtM_VdKp0g0BNsaeWFD3',
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
	            		'not_not-'.$vox->id => true
	            	]);
	            }

        	} else {

	        	$q = Request::input('question');
	        	$a = intval(Request::input('answer'));


	        	if(!isset( $answered[$q] ) && $not_bot) {
		        	$found = false;
		        	foreach ($vox->questions as $question) {
		        		if($question->id == $q) {
		        			$found = $question;
		        			break;
		        		}
		        	}

		        	if($found) {
		        		if( $a>=1 && $a<=count(json_decode($question->answers)) ) {
							$answer = new VoxAnswer;
					        $answer->user_id = $this->user->id;
					        $answer->vox_id = $vox->id;
					        $answer->question_id = $q;
					        $answer->answer = $a;
					        $answer->country_id = $this->user->country_id;
					        if($question->is_control) {
					        	$answer->is_scam = $question->is_control!=$a;
					        }
				        	if($answer->is_scam && $question->go_back) {
				        		$wrongs = intval(session('wrongs'));
				        		$wrongs++;
				            	session([
				            		'wrongs' => $wrongs
				            	]);

		        				$ret['wrong'] = true;
		        				$ret['go_back'] = $question->go_back;
		        				$counter = 0;
		        				foreach ($answered as $key => $value) {
		        					$counter++;
		        					if($counter>=$question->go_back) {
		        						$value->delete();
		        					}
		        				}
		        				if($wrongs>3) {
	            					$this->user->banUser('vox');
	            					$ret['ban'] = getLangUrl('banned');
		        				}
				        	} else {
					        	$answer->save();
						        $answered[$q] = $a;
					        }


	        				if( $answer->is_scam ) {
	        					if($this->user->vox_should_ban()) {
	            					$this->user->banUser('vox');
		        					$ret['ban'] = getLangUrl('banned');
		        				}
	        				}

					        if(count($answered) == count($vox->questions)) {
								$reward = new VoxReward;
						        $reward->user_id = $this->user->id;
						        $reward->vox_id = $vox->id;
						        $reward->reward = $vox->reward;
						        $start = $list->first()->created_at;
						        $diff = Carbon::now()->diffInSeconds( $start );
						        $normal = count($vox->questions)*5;
						        if($normal > $diff) {
						        	$reward->is_scam = true;
						        }

						        $reward->save();
		        				$ret['balance'] = $this->user->getVoxBalance();

		        				if( $reward->is_scam ) {
		        					if($this->user->vox_should_ban()) {
	            						$this->user->banUser('vox');
			        					$ret['ban'] = getLangUrl('banned');
			        				}
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

		return $this->ShowVoxView('vox', array(
			'not_bot' => $not_bot,
			'vox' => $vox,
			'answered' => $answered,
			'first_question' => $first_question,
			'first_question_num' => $first_question_num,
			'js' => [
				'vox.js'
			],
            'seo_title' => trans('vox.seo.questionnaire.title', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
            'seo_description' => trans('vox.seo.questionnaire.description', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
            'social_title' => trans('vox.social.questionnaire.title', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
            'social_description' => trans('vox.social.questionnaire.description', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
        ));
	}
}