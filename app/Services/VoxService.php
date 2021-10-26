<?php

namespace App\Services;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Models\UserSurveyWarning;
use App\Models\Recommendation;
use App\Models\VoxCrossCheck;
use App\Models\VoxCategory;
use App\Models\VoxQuestion;
use App\Models\UserAction;
use App\Models\PollAnswer;
use App\Models\VoxAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\Reward;
use App\Models\VpnIp;
use App\Models\Admin;
use App\Models\User;
use App\Models\Poll;
use App\Models\Vox;

use App\Helpers\GeneralHelper;
use App\Helpers\VoxHelper;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Cookie;
use Auth;
use Mail;
use App;
use DB;

class VoxService {

    public static function getNextQuestionFunction($admin, $user, $for_app, $country_id) {

        if(!empty($user)) {
            $vox_id = request('vox_id');
            $question_id = request('question_id');
            
            if(!empty($question_id)) {
                $cur_question = VoxQuestion::find($question_id);
            }

            $welcome_vox_id = 11;

            $admin_ids = Admin::getAdminProfileIds();
            $isAdmin = $for_app ? ( $user->is_admin ? true : false) : ($admin || in_array($user->id, $admin_ids));
            $testmode = session('testmode') && $isAdmin;

            if(!empty($vox_id)) {
                $vox = Vox::select('id')->where('id', $vox_id);

                if(empty($testmode)) {
                    $vox = $vox->where('type', 'normal');
                }
                $vox = $vox->first();
            }

            if(!empty($vox_id) && (!empty($vox) || !empty($admin) )) {

                $array = [];

                if($for_app) {
                    $madeWelcomeTest = $user->madeTest($welcome_vox_id);
                } else {
                    if(!session('made-welcome-test')) {
                        session([
                            'made-welcome-test' => $user->madeTest($welcome_vox_id),
                        ]);
                    }

                    $madeWelcomeTest = session('made-welcome-test');
                }

                if (!$madeWelcomeTest) {
                    // welcome qs
                    $array['welcome_vox'] = true;

                    if(!empty($question_id)) {
                        //question order
                        $next_question = VoxQuestion::where('vox_id', $cur_question->vox_id)->orderBy('order', 'asc')->where('order', '>', $cur_question->order)->first();
                        $array['question'] = $next_question;
                    } else {
                        //first question
                        $question = VoxQuestion::where('vox_id', $welcome_vox_id)->orderBy('order', 'ASC')->first();
                        $array['question'] = $question;
                    }
                } else if(empty($user->birthyear)) {
                    //demographic qs
                    $array['birthyear_q'] = true;
                } else if(empty($user->gender)) {
                    //demographic qs
                    $array['gender_q'] = true;
                } else if(empty($user->country_id)) {
                    //demographic qs
                    $array['country_id_q'] = true;
                }

                if(empty($array)) {
                    foreach (config('vox.details_fields') as $key => $info) {
                        if($user->$key==null) {
                            $array['details_question'] = $info;
                            $array['details_question_id'] = $key;
                            break;
                        }
                    }
                }
                if(empty($array)) {

                    if(!empty($question_id) && is_numeric($question_id) && $cur_question->vox_id == 11) {
                        $question_id=null;
                    }

                    if(!empty($question_id) && is_numeric($question_id)) {
                        $next_question = VoxQuestion::where('vox_id', $cur_question->vox_id)->orderBy('order', 'asc')->where('order', '>', $cur_question->order)->first();

                        // if($next_question) {
                            $checkQuestion = self::checkQuestion($next_question, $vox_id, $vox, $user, $array);
                            if(str_contains($checkQuestion, 'skip')) {
                                return $checkQuestion;
                            }
                            $array['question'] = $next_question;
                        // } else {
                        //     Log::error('No question!!! Cur question id: '.$question_id.' .User ID: '.$user->id);
                        //     return '';
                        // }
                        
                    } else {
                        $list = VoxAnswer::select('answer', 'question_id')->where('vox_id', $vox_id)->where('user_id', $user->id)->get();
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

                        $questions_list = VoxQuestion::where('vox_id', $vox_id)->orderBy('order', 'ASC');

                        $question = $questions_list->first();
                        
                        if(!isset($answered[$question->id])) {
                            //first question
                            $array['question'] = $question;
                        } else {
                            //first unanswered question
                            $array['question'] = $questions_list->where('order','>', VoxQuestion::find(array_key_last($answered))->order)->first();
                        }

                        $checkQuestion = self::checkQuestion($array['question'], $vox_id, $vox, $user, $array);

                        if(str_contains($checkQuestion, 'skip')) {
                            return $checkQuestion;
                        }
                    }
                }

                if($for_app) {
                    $cross_check = false;
                    $cross_check_answer = null;
                    $cross_check_birthyear = false;

                    if(isset($array['question'])) {

                        $vq = $array['question'];
                        if (!empty($vq) && !empty($vq->cross_check)) {
                            $cross_check = true;

                            if (is_numeric($vq->cross_check)) {
                                $va = VoxAnswer::where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
                                $cross_check_answer = $va ? $va->answer : null;
                            } else if($vq->cross_check == 'gender') {
                                $cross_check_answer = $user->gender == 'm' ? 1 : 2;
                            } else if($vq->cross_check == 'birthyear') {
                                $cross_check_birthyear = true;
                                $cross_check_answer = $user->birthyear;
                            } else {
                                $cc = $vq->cross_check;
                                $i=1;
                                foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
                                    if($key==$user->$cc) {
                                        if($key == 'household_children') {
                                            $cross_checks[$vq->id] = $i + 1;
                                        } else {                                
                                            $cross_checks[$vq->id] = $i;
                                        }
                                        break;
                                    }
                                    $i++;
                                }
                            }
                        }

                        $array['question'] = $vq->convertForResponse();
                    }
                } else {

                    $crossCheckParams = self::getCrossChecks($user, $vox->questions);
                    $cross_checks = $crossCheckParams['cross_checks'];
                    $cross_checks_references = $crossCheckParams['cross_checks_references'];
                }

                if($for_app) {

                    $slist = VoxScale::get();
                    $scales = [];
                    foreach ($slist as $sitem) {
                        $scales[$sitem->id] = $sitem;
                    }

                    $excluded_answers = [];
                    if(isset($array['question']) && !empty($array['question']['excluded_answers'])) {
                        foreach($array['question']['excluded_answers'] as $k => $excluded_answers_array) {
                            foreach($excluded_answers_array as $excluded_answ) {
                                $excluded_answers[$excluded_answ] = $k+1;
                            }
                        }
                    }

                    $array['excluded_answers'] = $excluded_answers;
                    $array['cross_check'] = $cross_check;
                    $array['cross_check_answer'] = $cross_check_answer;
                    $array['cross_check_birthyear'] = $cross_check_birthyear;
                    $array['scales'] = $scales;
                    $array['user'] = $user;
                    $array['country_id'] = $user->country_id ?? self::getCountryIdByIp() ?? '';

                    return Response::json( $array );

                } else {

                    if(!session('scales')) {
                        $slist = VoxScale::get();
                        $scales = [];
                        foreach ($slist as $sitem) {
                            $scales[$sitem->id] = $sitem;
                        }

                        session([
                            'scales' => $scales,
                        ]);
                    }

                    $excluded_answers = [];
                    if(isset($array['question']) && !empty($array['question']->excluded_answers)) {
                        foreach($array['question']->excluded_answers as $k => $excluded_answers_array) {
                            foreach($excluded_answers_array as $excluded_answ) {
                                $excluded_answers[$excluded_answ] = $k+1;
                            }
                        }
                    }
                    
                    $array['excluded_answers'] = $excluded_answers;
                    $array['cross_checks'] = $cross_checks;
                    $array['cross_checks_references'] = $cross_checks_references;
                    $array['scales'] = session('scales');
                    $array['user'] = $user;
                    $array['country_id'] = $country_id;
                    $array['isAdmin'] = $isAdmin;

                    //don't randomize answers here// only in js

                    return response()->view('vox.template-parts.vox-question', $array, 200)->header('X-Frame-Options', 'DENY');
                }
            }
        }

        return '';
    }

    public static function checkQuestion($next_question, $vox_id, $vox, $user, &$array) {
        if(!empty($next_question->prev_q_id_answers)) {
            $prev_q = VoxQuestion::find($next_question->prev_q_id_answers);

            $prev_answers = VoxAnswer::where('vox_id', $vox_id)->where('question_id', $prev_q->id)->where('user_id', $user->id)->get();
            if($prev_answers->count() == 1) {

                if($prev_answers->first()->answer != 0) {
                    $prev_q_answers_text = $prev_q->vox_scale_id && !empty(session('scales')[$prev_q->vox_scale_id]) ? explode(',', session('scales')[$prev_q->vox_scale_id]->answers) :  json_decode($prev_q->answers, true);

                    if(mb_strpos($prev_q_answers_text[$prev_answers->pluck('answer')->toArray()[0] - 1], '!') !== false) {
                        return 'skip-dvq:'.$next_question->id;
                    } else {
                        return 'skip-dvq:'.$next_question->id.';answer:'.$prev_answers->pluck('answer')->toArray()[0];
                    }
                } else {
                    return 'skip-dvq:'.$next_question->id;
                }
            } else {
                $answers_prev_q = json_decode($prev_q->answers, true);
                $answers_next_q = json_decode($next_question->answers, true);

                if($next_question->remove_answers_with_diez) {
                    
                    $answers_to_be_shown = $prev_answers->pluck('answer')->toArray();
                    foreach($answers_prev_q as $ans_key => $answer_prev_q) {
                        if(in_array($ans_key+1, $answers_to_be_shown)) {
                            if(str_contains($answer_prev_q, '#')) {
                                $key_with_diez = array_search($ans_key+1, $answers_to_be_shown);
                                unset($answers_to_be_shown[$key_with_diez]);
                                // dd('1', $answers_to_be_shown, $answers_prev_q, $ans_key+1, array_search($ans_key+1, $answers_to_be_shown));
                            }
                        }
                    }
                    // dd('2', $answers_to_be_shown, $answers_prev_q);

                    if(count($answers_to_be_shown) <= 1) {
                        return 'skip-dvq:'.$next_question->id;
                    } else {
                        $array['answers_shown'] = $answers_to_be_shown;

                        if(count($answers_prev_q) != count($answers_next_q)) {
                            $diffs = array_diff($answers_next_q,$answers_prev_q);

                            if(!empty($diffs)) {
                                foreach($diffs as $key_diff => $diff) {
                                    if(!str_contains($diff, '#')) {
                                        $array['answers_shown'][] = $key_diff+1;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $array['answers_shown'] = $prev_answers->pluck('answer')->toArray();
                    if(count($answers_prev_q) != count($answers_next_q)) {
                        $diffs = array_diff($answers_next_q,$answers_prev_q);

                        if(!empty($diffs)) {
                            foreach($diffs as $key_diff => $diff) {
                                $array['answers_shown'][] = $key_diff+1;
                            }
                        }
                    }
                }
            }
        }

        if(!empty($next_question->question_trigger)) {

            if($next_question->question_trigger=='-1') {
                foreach ($vox->questions as $originalTrigger) {
                    if($originalTrigger->id == $next_question->id) {
                        break;
                    }

                    if( $originalTrigger->question_trigger && $originalTrigger->question_trigger!='-1' ) {
                       $triggers = $originalTrigger->question_trigger;
                    }
                }
            } else {
                $triggers = $next_question->question_trigger;
            }

            if(!empty($triggers)) {

                $triggers = explode(';', $triggers);
                $triggerSuccess = [];

                foreach ($triggers as $trigger) {

                    list($triggerId, $triggerAnswers) = explode(':', $trigger);
                    if(is_numeric($triggerId)) {
                        $trigger_question = VoxQuestion::find($triggerId);
                    } else {
                        //demographic
                        $trigger_question = $triggerId;
                    }

                    // if($next_question->id == 19370) {
                    //     echo '<br/>Q id: '.$triggerId;
                    // }

                    if(mb_strpos($triggerAnswers, '!')!==false) {
                        $invert_trigger_logic = true;
                        $triggerAnswers = substr($triggerAnswers, 1);
                    } else {
                        $invert_trigger_logic = false;
                    }

                    if(mb_strpos($triggerAnswers, '-')!==false) {

                        if(mb_strpos($triggerAnswers, ',')!==false) {

                            $allowedAnswers = [];

                            $answersArr = explode(',', $triggerAnswers);

                            foreach ($answersArr as $ar) {
                                if(mb_strpos($ar, '-')!==false) {
                                    list($from, $to) = explode('-', $ar);

                                    for ($i=$from; $i <= $to ; $i++) {
                                        $allowedAnswers[] = $i;
                                    }
                                } else {
                                    $allowedAnswers[] = intval($ar);
                                }
                            }
                        } else {
                            list($from, $to) = explode('-', $triggerAnswers);

                            $allowedAnswers = [];
                            for ($i=$from; $i <= $to ; $i++) {
                                $allowedAnswers[] = $i;
                            }
                        }

                    } else {
                        $allowedAnswers = explode(',', $triggerAnswers);

                        // foreach($allowedAnswers as $kk => $vv) {
                        //  $allowedAnswers[$kk] = intval($vv);
                        // }
                    }


                    if(!empty($allowedAnswers)) {
                        $givenAnswers = [];
                        if(is_object($trigger_question)) {
                            $user_answers = VoxAnswer::where('user_id', $user->id)->where('question_id', $trigger_question->id)->get();
                            foreach ($user_answers as $ua) {
                                $givenAnswers[] = $ua->answer;
                            }
                        } else {
                            //demographic
                            $givenAnswers[] = $user->$trigger_question;
                        }

                        // if($next_question->id == 19370) {
                        //     echo '<br/>allowedAnswers: '.json_encode($allowedAnswers);
                        //     echo '<br/>givenAnswers: '.json_encode($givenAnswers);
                        // }

                        // echo 'Trigger for: '.$triggerId.' / Valid answers '.var_export($allowedAnswers, true).' / Answer: '.var_export($givenAnswers, true).' / Inverted logic: '.($invert_trigger_logic ? 'da' : 'ne').'<br/>';

                        foreach ($givenAnswers as $ga) {
                            $int = intval($ga) + rand(74575,998858);
                            
                            if(str_contains($ga,',') !== false) {
                                $given_answers_array = explode(',', $ga);

                                $found = false;
                                foreach ($given_answers_array as $key => $value) {
                                    if(in_array($value, $allowedAnswers)) {
                                        $found = true;
                                        break;
                                    }
                                }

                                if($invert_trigger_logic) {
                                    if(!$found) {
                                        $triggerSuccess[$int] = true;
                                    } else {
                                        $triggerSuccess[$int] = false;
                                    }
                                } else {

                                    if($found) {
                                        $triggerSuccess[$int] = true;
                                    } else {
                                        $triggerSuccess[$int] = false;
                                    }
                                }
                            } else {
                                if(strpos($allowedAnswers[0], '>') !== false) {
                                    $trg_ans = substr($allowedAnswers[0], 1);

                                    if($ga > intval($trg_ans)) {
                                        $triggerSuccess[$int] = true;
                                    } else {
                                        $triggerSuccess[$int] = false;
                                    }
                                } else if(strpos($allowedAnswers[0], '<') !== false) {
                                    $trg_ans = substr($allowedAnswers[0], 1);

                                    if(intval($ga) < intval($trg_ans)) {
                                        $triggerSuccess[$int] = true;
                                    } else {
                                        $triggerSuccess[$int] = false;
                                    }
                                } else {
                                    if($invert_trigger_logic) {
                                        if( !empty($ga) && !in_array($ga, $allowedAnswers) ) {
                                            $triggerSuccess[$int] = true;
                                        } else {
                                            $triggerSuccess[$int] = false;
                                        }
                                    } else {
                                        // echo in_array($ga, $allowedAnswers) ? '<br/>'.$ga.' in _array' : '<br/>'.$ga.' not_in array';

                                        if( !empty($ga) && in_array($ga, $allowedAnswers) ) {
                                            $triggerSuccess[$int] = true;
                                        } else {
                                            $triggerSuccess[$int] = false;
                                        }
                                    }
                                }
                            }
                        }

                        // if($next_question->id == 19370) {
                        //     echo '<br/>Q id - trugger success: '.json_encode($triggerSuccess);
                        // }
                    }
                }



                // if($next_question->id == 19370) {
                //  dd($triggerSuccess);
                // }

                if( $next_question->trigger_type == 'or' ) { // ANY of the conditions should be met (A or B or C)
                    if( !in_array(true, $triggerSuccess) ) {
                        return 'skip-dvq:'.$next_question->id;
                    }
                }  else { //ALL the conditions should be met (A and B and C)
                    if( in_array(false, $triggerSuccess) ) {
                        return 'skip-dvq:'.$next_question->id;
                    }
                }

            }
        }
    }

    public static function getAgeGroup($by) {

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

    public static function testAnswers($user_id, $answered, $q_id, $vox) {

        // if(!empty($answered)) {

        //     foreach ($vox->questions as $question) {
        //         if($question->id==$q_id) {
        //             foreach ($vox->questions as $vq) {
        //                 if($vq->order >= $question->order) {
        //                     VoxAnswer::where('vox_id', $vox->id)
        //                     ->where('user_id', $user_id)
        //                     ->where('question_id', $vq->id)
        //                     ->delete();

        //                     DcnReward::where('reference_id', $vox->id)
        //                     ->where('platform', 'vox')
        //                     ->where('type', 'survey')
        //                     ->where('user_id', $user_id)
        //                     ->delete();
        //                 }
        //             }
        //             break;
        //         }
        //     }
        // }

        VoxAnswer::where('vox_id', $vox->id)
        ->where('user_id', $user_id)
        ->delete();

        DcnReward::where('reference_id', $vox->id)
        ->where('platform', 'vox')
        ->where('type', 'survey')
        ->where('user_id', $user_id)
        ->delete();

        return $q_id;
    }

    public static function setupAnswerStats($user, &$answer) {

        foreach (config('vox.stats_scales') as $df => $dv) {
            if($df=='age') {
                $agegroup = self::getAgeGroup($user->birthyear);
                $answer->$df = $agegroup;
            } else {
                if($user->$df!==null) {
                    $answer->$df = $user->$df;
                }
            }
        }
    }

    public static function getCrossChecks($user, $vox_questions) {

        $cross_checks = [];
        $cross_checks_references = [];

        foreach ($vox_questions as $vq) {
            if (!empty($vq->cross_check)) {

                if (is_numeric($vq->cross_check)) {
                    $va = VoxAnswer::select('answer')->where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
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
                            if($cc == 'household_children') {
                                $cross_checks[$vq->id] = $i + 1;
                            } else {                                
                                $cross_checks[$vq->id] = $i;
                            }
                            $cross_checks_references[$vq->id] = $cc;
                            break;
                        }
                        $i++;
                    }
                }
            }
        }

        return [
            'cross_checks' => $cross_checks,
            'cross_checks_references' => $cross_checks_references,
        ];
    }

    public static function goBack($user_id, $answered, $list, $vox) {
        // var_dump('$list');
        // dd($list, $answered);
        $lastkey = null;
        if(!empty($answered)) {
            foreach ($list as $aq) {
                if(!$aq->is_skipped) {
                    $lastkey = $aq;

                    VoxAnswer::where('vox_id', $vox->id)
                    ->where('user_id', $user_id)
                    ->where('question_id', $lastkey->question_id)
                    ->delete();

                    DcnReward::where('reference_id', $vox->id)
                    ->where('platform', 'vox')
                    ->where('type', 'survey')
                    ->where('user_id', $user_id)
                    ->delete();

                    
                    // var_dump('$ll');
                    // dd($list, $answered, $aq);
                    break;
                }

                // if(!empty($lastkey)) {
                //     foreach($list as $k => $l) {
                //         if($l->question_id == $lastkey->question_id) {
                //             unset($list[$k]);
                //         }
                //     }
                // }
            }
        }

        // dd($lastkey->id);

        if(!empty($lastkey) && $lastkey->is_skipped) {
            do {
                self::goBack($user_id, $answered, $list, $vox);
            } while ( $lastkey->is_skipped);
        }

        return $lastkey ? $lastkey->question_id : VoxQuestion::where('vox_id', $vox->id)->where('order', 1)->first()->id;
    }

    public static function featuredVoxes() {
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

    public static function relatedSuggestedVoxes($user, $vox_id, $type) {
        $related_voxes = [];
        $related_voxes_ids = [];

        $vox = Vox::find($vox_id);
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

    public static function getCountryIdByIp() {
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

    public static function doVox($vox, $user, $for_app) {

        if (empty($vox) || $vox->id == 11) {
            if($for_app) {
                return Response::json( array(
                    'success' => false
                ) );
            } else {
                return [
                    'url' => getLangUrl('page-not-found')
                ];
            }
        }

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        if(!$for_app) {
            $admin = Auth::guard('admin')->user();
            //to log Dobrina, because of problems
            if(empty($user) && !empty($admin) && ($admin->id) == 11 && !empty($admin->user_id)) {
                $adm = User::find($admin->user_id);

                if(!empty($adm)) {
                    Auth::login($adm, true);
                }
                return [
                    'url' => url()->current().'?testmode=1'
                ];
            }
        }

        //vox for not logged users
        if(!$user) {

            if($for_app) {
                return Response::json( array(
                    'vox' => $vox->convertForResponse(),
                    'voxes' => self::featuredVoxes(),
                    'vox_type' => 'public',
                ) );
            } else {
                $seos = PageSeo::find(16);
                $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
                $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
                $social_title = str_replace(':title', $vox->title, $seos->social_title);
                $social_description = str_replace(':description', $vox->description, $seos->social_description);

                return [
                    'view' => 'vox-public',
                    'params' => array(
                        'vox' => $vox,
                        'custom_body_class' => 'vox-public',
                        'js' => [
                            'vox-public.js',
                        ],
                        'css' => [
                            'vox-public-vox.css',
                        ],
                        'canonical' => $vox->getLink(),
                        'social_image' => $vox->getSocialImageUrl('survey'),
                        'seo_title' => $seo_title,
                        'seo_description' => $seo_description,
                        'social_title' => $social_title,
                        'social_description' => $social_description,
                    ),
                ];
            }
        }

        //when the user is banned
        if($user->isBanned('vox')) {
            if($for_app) {
                return Response::json( array(
                    'vox' => $vox->convertForResponse(),
                    'related_voxes' => self::relatedSuggestedVoxes($user, $vox->id,'related'),
                    'suggested_voxes' => self::relatedSuggestedVoxes($user, $vox->id,'suggested'),
                    'restricted_description' => 'The target group of this survey consists of respondents with different demographics. No worries: We have plenty of other opportunities for you!',
                    'vox_type' => 'restricted',
                ) );
            } else {
                return [
                    'url' => 'https://account.dentacoin.com/dentavox?platform=dentavox'
                ];
            }
        }

        //when the user is logged from bad IP
        if(!$user->is_dentist && $user->platform != 'external' && $user->loggedFromBadIp()) {
            $ul = new UserLogin;
            $ul->user_id = $user->id;
            $ul->ip = User::getRealIp();
            $ul->platform = 'vox';
            $ul->country = \GeoIP::getLocation()->country;

            $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
            $dd = new DeviceDetector($userAgent);
            $dd->parse();

            if ($dd->isBot()) {
                // handle bots,spiders,crawlers,...
                $ul->device = $dd->getBot();
            } else {
                $ul->device = $dd->getDeviceName();
                $ul->brand = $dd->getBrandName();
                $ul->model = $dd->getModel();
                $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
            }
            
            $ul->save();
            
            $u_id = $user->id;

            $action = new UserAction;
            $action->user_id = $u_id;
            $action->action = 'bad_ip';
            $action->reason = 'Automatically - Bad IP ( vox questionnaire )';
            $action->actioned_at = Carbon::now();
            $action->save();

            if($for_app) {
                $user->logoutActions();
                $user->removeTokens();
                Auth::guard('api')->logout();

                return Response::json( array(
                    'logout' => true,
                ) );
            } else {
                Auth::guard('web')->user()->logoutActions();
                Auth::guard('web')->user()->removeTokens();
                Auth::guard('web')->logout();
                
                return [
                    'url' => 'https://account.dentacoin.com/account-on-hold?platform=dentavox&on-hold-type=bad_ip&key='.urlencode(GeneralHelper::encrypt($u_id))
                ];
            }
        }

        if($for_app) {
            $isAdmin = false;
            $testmode = false;
        } else {
            $admin_ids = Admin::getAdminProfileIds(); //Dobrina
            $isAdmin = Auth::guard('admin')->user() || in_array($user->id, $admin_ids);

            if (request()->has('testmode')) {
                if(request('testmode')) {
                    $ses = [
                        'testmode' => true
                    ];
                } else {
                    $ses = [
                        'testmode' => false
                    ];
                }
                session($ses);
            }
            $testmode = session('testmode') && $isAdmin;
        }

        $taken = $user->filledVoxes();

        if((!$isAdmin && $vox->type=='hidden') || !in_array($user->status, config('dentist-statuses.approved')) ) {

            if($for_app) {
                return Response::json( array(
                    'success' => false
                ) );
            } else {
                return [
                    'url' => getLangUrl('page-not-found')
                ];
            }

        } else if( $user->madeTest($vox->id) && !(Request::input('goback') && $testmode) ) { //because of GoBack

            if($for_app) {
                return Response::json( array(
                    'vox' => $vox->convertForResponse(),
                    'related_voxes' => self::relatedSuggestedVoxes($user, $vox->id,'related'),
                    'suggested_voxes' => self::relatedSuggestedVoxes($user, $vox->id,'suggested'),
                    'vox_type' => 'taken',
                ) );
            } else {

                $related_voxes = [];
                $related_voxes_ids = [];
                if ($vox->related->isNotEmpty()) {
                    foreach ($vox->related as $r) {
                        if (!in_array($r->related_vox_id, $taken)) {
                            $related_voxes[] = Vox::find($r->related_vox_id);
                            $related_voxes_ids[] = $r->related_vox_id;
                        }
                    }
                }

                $suggested_voxes = $user->voxesTargeting()->where('type', 'normal')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

                $suggested_voxes = $user->notRestrictedVoxesList($suggested_voxes);

                $seos = PageSeo::find(17);

                $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
                $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
                $social_title = str_replace(':title', $vox->title, $seos->social_title);
                $social_description = str_replace(':title', $vox->description, $seos->social_description);

                return [
                    'view' => 'vox-taken',
                    'params' => array(
                        'vox' => $vox,
                        'related_voxes' => $related_voxes,
                        'suggested_voxes' => $suggested_voxes,
                        'seo_title' => $seo_title,
                        'seo_description' => $seo_description,
                        'social_title' => $social_title,
                        'social_description' => $social_description,
                        'canonical' => $vox->getLink(),
                        'social_image' => $vox->getSocialImageUrl('survey'),
                        'js' => [
                            'taken-vox.js',
                            '../js/swiper.min.js'
                        ],
                        'css' => [
                            'swiper.min.css',
                            'vox-taken-survey-wrapper.css',
                        ],
                    ),
                ];
            }
        }

        if (!$testmode) {

            $restrictedVox = $user->isVoxRestricted($vox);
            $restrictedVoxByCountry = $vox->voxCountryRestricted($user);

            if ($restrictedVox || $restrictedVoxByCountry) {

                if ($restrictedVox) {
                    $res_desc = trans('vox.page.restricted-questionnaire.description-target');
                } else {
                    $res_desc = trans('vox.page.restricted-questionnaire.description-limit');
                }

                if($for_app) {

                    return Response::json( array(
                        'vox' => $vox->convertForResponse(),
                        'related_voxes' => self::relatedSuggestedVoxes($user, $vox->id,'related'),
                        'suggested_voxes' => self::relatedSuggestedVoxes($user, $vox->id,'suggested'),
                        'restricted_description' => $res_desc,
                        'vox_type' => 'restricted',
                    ) );

                } else {

                    $related_voxes = [];
                    $related_voxes_ids = [];
                    if ($vox->related->isNotEmpty()) {
                        foreach ($vox->related as $r) {
                            if (!in_array($r->related_vox_id, $taken)) {
                                $related_voxes[] = Vox::find($r->related_vox_id);
                                $related_voxes_ids[] = $r->related_vox_id;
                            }
                        }
                    }

                    $suggested_voxes = $user->voxesTargeting()->where('type', 'normal')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

                    $suggested_voxes = $user->notRestrictedVoxesList($suggested_voxes);

                    $seos = PageSeo::find(18);

                    $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
                    $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
                    $social_title = str_replace(':title', $vox->title, $seos->social_title);
                    $social_description = str_replace(':title', $vox->description, $seos->social_description);

                    return [
                        'view' => 'vox-restricted',
                        'params' => array(
                            'res_desc' => $res_desc,
                            'vox' => $vox,
                            'related_voxes' => $related_voxes,
                            'suggested_voxes' => $suggested_voxes,
                            'seo_title' => $seo_title,
                            'seo_description' => $seo_description,
                            'social_title' => $social_title,
                            'social_description' => $social_description,
                            'canonical' => $vox->getLink(),
                            'social_image' => $vox->getSocialImageUrl('survey'),
                            'js' => [
                                'taken-vox.js',
                                '../js/swiper.min.js'
                            ],
                            'css' => [
                                'swiper.min.css',
                                'vox-taken-survey-wrapper.css',
                            ],
                        ),
                    ];
                }
            }

            $daily_voxes = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->count();

            if($daily_voxes >= 10) {

                $last_vox = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->orderBy('id', 'desc')->first();

                $now = Carbon::now()->subDays(1);
                $time_left = $last_vox->created_at->diffInHours($now).':'.
                str_pad($last_vox->created_at->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
                str_pad($last_vox->created_at->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);

                if($for_app) {
                    return Response::json( array(
                        'vox' => $vox->convertForResponse(),
                        'voxes' => self::featuredVoxes(),
                        'vox_type' => 'limit',
                        'time_left' => $time_left,  
                    ) );
                } else {
                    $seos = PageSeo::find(19);

                    $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
                    $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
                    $social_title = str_replace(':title', $vox->title, $seos->social_title);
                    $social_description = str_replace(':description', $vox->description, $seos->social_description);

                    return [
                        'view' => 'vox-limit-reached',
                        'params' => array(
                            'vox' => $vox,
                            'time_left' => $time_left,                  
                            'canonical' => $vox->getLink(),
                            'social_image' => $vox->getSocialImageUrl('survey'),
                            'seo_title' => $seo_title,
                            'seo_description' => $seo_description,
                            'social_title' => $social_title,
                            'social_description' => $social_description,
                            'css' => [
                                'vox-limit-reached.css'
                            ],
                        ),
                    ];
                }
            }
        }

        
        if($for_app) {
            $madeWelcomeTest = $user->madeTest(11);
        } else {
            if(!session('made-welcome-test')) {
                session([
                    'made-welcome-test' => $user->madeTest(11),
                ]);
            }
            
            $madeWelcomeTest = session('made-welcome-test');
        }
        
        $welcome_vox = '';
        if (!$madeWelcomeTest) {
            $welcome_vox = Vox::with('questions')->find(11);
        }

        $vox_questions = $vox->questions;
        $crossCheckParams = self::getCrossChecks($user, $vox_questions);
        $cross_checks = $crossCheckParams['cross_checks'];
        $cross_checks_references = $crossCheckParams['cross_checks_references'];

        $list = VoxAnswer::select('vox_id', 'question_id', 'user_id', 'answer', 'is_skipped', 'created_at')
        ->where('vox_id', $vox->id)
        ->with('question')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

        // dd($list);

        $answered = 0;
        $answered_without_skip_count = 0;
        $answered_without_skip = [];
        foreach ($list as $l) {
            if(!isset( $answered_without_skip[$l->question_id] )) {
                $answered++;
                if($l->question) {
                    if($l->question && $l->answer > 0 || $l->question->type == 'number' || $l->question->cross_check == 'birthyear' || $l->question->cross_check == 'household_children') {
                        $answered_without_skip[$l->question_id] = ['1']; //3
                        $answered_without_skip_count++;
                    }
                }
            } 
        }

        if($testmode) {
            if(Request::input('goback')) {
                $q_id = self::goBack($user->id, $answered, $list, $vox);
                // var_dump($q_id);
                if(!empty(VoxQuestion::find($q_id))) {

                    $vq = VoxQuestion::where('vox_id', $vox->id)->where('order', VoxQuestion::find($q_id)->order-1)->first();
                    if(!empty($vq)) {
                        $quest_id = $vq->id;
                    } else {
                        $quest_id = $q_id;
                    }
                } else {
                    $quest_id = $q_id;
                }

                // dd($quest_id, VoxQuestion::where('vox_id', $vox->id)->where('order', '1')->first()->id);

                if($quest_id == VoxQuestion::where('vox_id', $vox->id)->where('order', '1')->first()->id) {
                    return [
                        'url' => $vox->getLink().'?testmode=1'
                    ];
                } else {
                    return [
                        'url' => $vox->getLink().'?testmode=1&q-id='.$quest_id
                    ];
                }
            }

            if(Request::input('start-from')) {
                $q_id = self::testAnswers($user->id, $answered, Request::input('start-from'), $vox);

                if(!empty(VoxQuestion::find($q_id))) {

                    $vq = VoxQuestion::where('vox_id', $vox->id)->where('order', VoxQuestion::find($q_id)->order-1)->first();
                    if(!empty($vq)) {
                        $quest_id = $vq->id;
                    } else {
                        $quest_id = $q_id;
                    }
                } else {
                    $quest_id = $q_id;
                }

                return [
                    'url' => $vox->getLink().'?testmode=1&q-id='.$quest_id
                ];
            }
        }

        if($for_app) {
            $slist = VoxScale::get();
            $scales = [];
            foreach ($slist as $sitem) {
                $scales[$sitem->id] = $sitem;
            }
        } else {
            if(!session('scales')) {
                $slist = VoxScale::get();
                $scales = [];
                foreach ($slist as $sitem) {
                    $scales[$sitem->id] = $sitem;
                }

                session([
                    'scales' => $scales,
                ]);
            }
        }

        $first_question_num = 0;
        if (!empty($welcome_vox)) {
            $first_question_num++;
        } else {
            $first_question_num = $answered + 1;
        }

        $total_questions = $vox_questions->count();

        if (!$user->birthyear) {
            $total_questions++;
        }
        if (!$user->country_id) {
            $total_questions++;
        }
        if (!$user->gender) {
            $total_questions++;
        }

        foreach (config('vox.details_fields') as $key => $value) {
            if($user->$key==null) {
                $total_questions++;     
            }
        }

        if (!empty($welcome_vox)) {
            foreach ($welcome_vox->questions as $key => $value) {
                $total_questions++;     
            }
        }

        if(!$for_app) {

            $welcomerules = !session('vox-welcome');
            if($welcomerules) {
                session([
                    'vox-welcome' => true
                ]);
            }
        }

        $related_voxes = [];
        $related_voxes_ids = [];
        if ($vox->related->isNotEmpty()) {
            foreach ($vox->related as $r) {
                if (!in_array($r->related_vox_id, $taken)) {
                    $related_voxes_ids[] = $r->related_vox_id;
                }
            }

            if (!empty($related_voxes_ids)) {
                foreach(Vox::with('translations')->with('categories.category')->with('categories.category.translations')->whereIn('id', $related_voxes_ids)->get() as $rv) {
                    $related_voxes[] = $rv;
                }
            }
        }
        $suggested_voxes = Vox::where('type', 'normal')->with('translations')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

        $suggested_voxes = $user->notRestrictedVoxesList($suggested_voxes);

        if($for_app) {
            $birth_years = [];
            for($i=(date('Y')-18);$i>=(date('Y')-90);$i--){
                $birth_years[$i] = $i;
            }

            $answered_arr = []; // because in the old app versions can't convert to int

            for($i=0; $i < $answered; $i++) {
                $answered_arr[] = $i;
            }

            return Response::json( array(
                'welcome_vox' => $welcome_vox,
                'related_voxes' => $related_voxes,
                'suggested_voxes' => $suggested_voxes,
                'cross_checks' => $cross_checks,
                'cross_checks_references' => $cross_checks_references,
                'vox' => $vox->convertForResponse(),
                'vox_url' => $vox->getLink(),
                'voxes' => self::featuredVoxes(),
                'vox_type' => 'to-take',
                'answered' => $answered_arr,
                'real_questions' => $vox->questions->count(),
                'total_questions' => $total_questions,
                'first_question_num' => $first_question_num,
                'answered_without_skip_count' => $answered_without_skip_count,
                'birthyear_options' => VoxHelper::getBirthyearOptions(),
                'countries' => ['' => '-'] + Country::with('translations')->get()->pluck('name', 'id')->toArray(),
                'country_id' => $user->country_id ?? self::getCountryIdByIp() ?? '',
                'birth_years' => $birth_years,
            ) );
        } else {
            $seos = PageSeo::find(15);
            $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
            $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
            $social_title = str_replace(':title', $vox->title, $seos->social_title);
            $social_description = str_replace(':description', $vox->description, $seos->social_description);

            return [
                'view' => 'vox',
                'params' => array(
                    'welcome_vox' => $welcome_vox,
                    'related_voxes' => $related_voxes,
                    'suggested_voxes' => $suggested_voxes,
                    'cross_checks' => $cross_checks,
                    'cross_checks_references' => $cross_checks_references,
                    'welcomerules' => $welcomerules,
                    'not_bot' => $testmode || session('not_not-'.$vox->id),
                    'details_fields' => config('vox.details_fields'),
                    'vox' => $vox,
                    'scales' => session('scales'),
                    'answered' => $answered,
                    'real_questions' => $vox_questions->count(),
                    'total_questions' => $total_questions,
                    'first_question_num' => $first_question_num,
                    'answered_without_skip_count' => $answered_without_skip_count,
                    'js' => [
                        'vox-new.js',
                        '../js/lightbox.js',
                        '../js/jquery-ui.min.js',
                        '../js/jquery-ui-touch.min.js',
                        '../js/flickity.pkgd.min.js',
                        '../js/swiper.min.js'
                    ],
                    'css' => [
                        'vox-questionnaries.css',
                        'lightbox.css',
                        'flickity.min.css',
                        'swiper.min.css',
                        'vox-taken-survey-wrapper.css',
                    ],
                    'canonical' => $vox->getLink(),
                    'social_image' => $vox->getSocialImageUrl('survey'),
                    'seo_title' => $seo_title,
                    'seo_description' => $seo_description,
                    'social_title' => $social_title,
                    'social_description' => $social_description,
                    'testmode' => $testmode,
                    'isAdmin' => $isAdmin,
                ),
            ];
        }
    }

    public static function surveyAnswer($vox, $user, $for_app) {

        $ret['success'] = false;

        if(empty($user) || empty($vox) || !in_array($user->status, config('dentist-statuses.approved'))) {
            return Response::json( $ret );
        }

        if($for_app) {
            $testmode = $user->is_admin;
        } else {
            $admin_ids = Admin::getAdminProfileIds();
            $isAdmin = Auth::guard('admin')->user() || in_array($user->id, $admin_ids);
            $testmode = session('testmode') && $isAdmin;
        }

        if(!$testmode) {
            if($vox->type=='hidden' ) {
                return Response::json( $ret );
            }

            if(!$user->is_dentist && !empty(VpnIp::where('ip', User::getRealIp())->first())) {
                $ret['is_vpn'] = true;
                return Response::json( $ret );
            }
        }

        $welcome_vox = '';
        $welcome_vox_question_ids = [];

        if($for_app) {
            $madeWelcomeTest = $user->madeTest(11);
        } else {
            if(!session('made-welcome-test')) {
                session([
                    'made-welcome-test' => $user->madeTest(11),
                ]);
            }

            $madeWelcomeTest = session('made-welcome-test');
        }

        if (!$madeWelcomeTest) {
            $welcome_vox = Vox::with('questions')->find(11);
            $welcome_vox_question_ids = $welcome_vox->questions->pluck('id')->toArray();
        }

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        $crossCheckParams = self::getCrossChecks($user, $vox->questions);
        $cross_checks = $crossCheckParams['cross_checks'];
        $cross_checks_references = $crossCheckParams['cross_checks_references'];

        $list = VoxAnswer::select('vox_id', 'question_id', 'user_id', 'answer', 'is_skipped', 'created_at')
        ->where('vox_id', $vox->id)
        // ->with('question')
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

        if($for_app) {
            $not_bot = true;

            $slist = VoxScale::get();
            $scales = [];
            foreach ($slist as $sitem) {
                $scales[$sitem->id] = $sitem;
            }

        } else {
            $not_bot = $testmode || session('not_not-'.$vox->id);

            if(!session('scales')) {
                $slist = VoxScale::get();
                $scales = [];
                foreach ($slist as $sitem) {
                    $scales[$sitem->id] = $sitem;
                }

                session([
                    'scales' => $scales,
                ]);
            }
            if(Request::input('captcha')) {
                $captcha = false;
                $cpost = [
                    'secret' => env('CAPTCHA_SECRET'),
                    'response' => Request::input('captcha'),
                    'remoteip' => User::getRealIp()
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
                    $ret['captcha_error'] = true;

                    return Response::json( $ret );
                } else {
                    session([
                        'not_not-'.$vox->id => true,
                        'reward-for-'.$vox->id => $vox->getRewardTotal()
                    ]);
                    $ret['vox_id'] = $vox->id;
                }
            }
        }

        $ret = [
            'success' => true,
        ];

        $q = Request::input('question');

        if( !isset( $answered[$q] ) && $not_bot ) {

            $type = Request::input('type');
            $answ = Request::input('answer');

            $found = isset( config('vox.details_fields')[$type] ) || in_array($type, ['gender-question', 'birthyear-question', 'location-question']) ? true : false;

            foreach ($vox->questions as $question) {
                if($question->id == $q) {
                    $found = $question;
                    break;
                }
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
                $valid = false;
                $answer_count = in_array($type, ['multiple', 'rank', 'scale', 'single']) ? count($question->vox_scale_id && !empty(session('scales')[$question->vox_scale_id]) ? explode(',', session('scales')[$question->vox_scale_id]->answers) : json_decode($question->answers, true) ) : 0;

                if ($type == 'skip') {
                    $valid = true;
                    $a = 0;

                } else if($type == 'previous') {
                    $valid = true;
                    $a = $answ;
                } else if ( isset( config('vox.details_fields')[$type] ) ) {

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

                    $agegroup = self::getAgeGroup($answ);

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

                    if($for_app) {
                        if(is_string($a)) {
                            $a = explode(',', $a);
                        }
                    }

                    foreach ($a as $k => $value) {
                        if (!($value>=1 && $value<=$answer_count)) {
                            $valid = false; 
                            break;
                        }
                    }
                    
                    if(!empty($question->excluded_answers)) {

                        $excluded_answers = [];
                        foreach($question->excluded_answers as $k => $excluded_answers_array) {
                            foreach($excluded_answers_array as $excluded_answ) {
                                $excluded_answers[$excluded_answ] = $k+1;
                            }
                        }
                        $group = null;

                        foreach ($a as $k => $value) {
                            if(isset($excluded_answers[$value])) {
                                if($group == $excluded_answers[$value]) {
                                    $valid = false;
                                    break;
                                }
                                if(empty($group)) {
                                    $group = $excluded_answers[$value];

                                    // echo 'group: '.$group;
                                }

                                // echo 'group: '.$group.' == '.$excluded_answers[$value];
                            }
                        }
                    }
                    
                } else if ($type == 'single') {
                    $a = intval($answ);
                    $valid = $a>=1 && $a<=$answer_count;

                } else if ($type == 'number') {
                    $min_num = intval(explode(':', $question->number_limit)[0]);
                    $max_num = intval(explode(':', $question->number_limit)[1]);
                    $a = intval($answ);
                    $valid = $a>=$min_num && $a<=$max_num;
                }

                if( $valid ) {
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

                    if($is_scam && !$testmode && !$user->is_partner) {
                    
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
                                $ret['go_back'] = self::goBack($user->id, $answered, $list, $vox);
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
                        
                        return Response::json( $ret );
                    } else {

                        if($type == 'skip') {
                            $answer = new VoxAnswer;
                            $answer->user_id = $user->id;
                            $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
                            $answer->question_id = $q;
                            $answer->answer = 0;
                            $answer->is_skipped = true;
                            $answer->country_id = $user->country_id;
                            
                            if($testmode) {
                                $answer->is_admin = true;
                            }
                            $answer->save();
                            $answered[$q] = 0;
                            
                        } else if($type == 'previous') {
                            $answer = new VoxAnswer;
                            $answer->user_id = $user->id;
                            $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
                            $answer->question_id = $q;
                            $answer->answer = $a;
                            self::setupAnswerStats($user, $answer);
                            $answer->country_id = $user->country_id;
                            
                            if($testmode) {
                                $answer->is_admin = true;
                            }
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

                            self::setupAnswerStats($user, $answer);
                            
                            if($testmode) {
                                $answer->is_admin = true;
                            }
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
                                        if($i==$a) {
                                            if (!empty($cross_checks) && $cross_checks[$q] != $a) {
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

                        } else if(isset( config('vox.details_fields')[$type] ) || $type == 'location-question' || $type == 'birthyear-question' || $type == 'gender-question' ) {
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
                                    self::setupAnswerStats($user, $answer);

                                    if($testmode) {
                                        $answer->is_admin = true;
                                    }
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
                            self::setupAnswerStats($user, $answer);
                        
                            if($testmode) {
                                $answer->is_admin = true;
                            }
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
                                self::setupAnswerStats($user, $answer);
                            
                                if($testmode) {
                                    $answer->is_admin = true;
                                }
                                $answer->save();
                            }
                            $answered[$q] = $a;

                        } else if($type == 'scale' || $type == 'rank') {
                            foreach ($a as $k => $value) {
                                $answer = new VoxAnswer;
                                $answer->user_id = $user->id;
                                $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
                                if (in_array($q, $welcome_vox_question_ids)===true) {
                                    $answer->is_completed = 1;
                                    $answer->is_skipped = 0;
                                }
                                $answer->question_id = $q;
                                $answer->answer = $k+1;
                                $answer->scale = $value;
                                $answer->country_id = $user->country_id;
                                self::setupAnswerStats($user, $answer);
                                
                                if($testmode) {
                                    $answer->is_admin = true;
                                }
                                $answer->save();
                            }
                            $answered[$q] = $a;
                        }
                    }

                    $reallist = $list->filter(function ($value, $key) {
                        return !$value->is_skipped;
                    });

                    $ppp = 10;
                    if( $reallist->count() && $reallist->count()%$ppp==0 && !$testmode && !$user->is_partner ) {

                        $pagenum = $reallist->count()/$ppp;
                        $start = $reallist->forPage($pagenum, $ppp)->first();
                        
                        $diff = Carbon::now()->diffInSeconds( $start->created_at );
                        $normal = $ppp*2;
                        
                        if($normal > $diff && count($answered) != count($vox->questions)) {

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

                        if(!$for_app) {
                            session([
                                'scales' => null,
                            ]);
                        }

                        if( $user->madeTest($vox->id) && !(Request::input('goback') && $testmode) ) {
                            return Response::json( [
                                'success' => false
                            ] );
                        }

                        if($user->isVoxRestricted($vox)) {

                            return Response::json( [
                                'success' => false,
                                'restricted' => true,
                            ] );
                        }

                        $answered_without_skip_count = 0;
                        $answered_without_skip = [];
                        foreach ($list as $l) {
                            if(!isset( $answered_without_skip[$l->question_id] ) && $l->question && $l->answer > 0 || $l->question->type == 'number' || $l->question->cross_check == 'birthyear' || $l->question->cross_check == 'household_children') {
                                $answered_without_skip[$l->question_id] = ['1']; //3
                                $answered_without_skip_count++;
                            }
                        }

                        $rewardForCurVox = $vox->getRewardForUser($user, $answered_without_skip_count);

                        $reward = new DcnReward;
                        $reward->user_id = $user->id;
                        $reward->reference_id = $vox->id;
                        $reward->platform = 'vox';
                        $reward->type = 'survey';

                        $reward->reward = $rewardForCurVox;
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
                        $ret['balance'] = $user->getTotalBalance();

                        $open_recommend = false;
                        $social_profile = false;
                        $filled_voxes = $user->filledVoxesCount();

                        if(!$user->is_dentist && $filled_voxes == 1) {
                            $social_profile = true;
                        } else if (($filled_voxes == 5 || $filled_voxes == 10 || $filled_voxes == 20 || $filled_voxes == 50) && empty($user->fb_recommendation)) {
                            $open_recommend = true;
                        }

                        $ret['reward_for_cur_vox'] = $rewardForCurVox;
                        $ret['recommend'] = $open_recommend;
                        $ret['social_profile'] = $social_profile;

                        VoxAnswer::where('user_id', $user->id)->where('vox_id', $vox->id)->update(['is_completed' => 1]);

                        $vox->recalculateUsersPercentage($user);

                        $user->giveInvitationReward('vox');

                        if ($user->platform == 'external') {
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_POST => 1,
                                CURLOPT_URL => 'https://hub-app-api.dentacoin.com/internal-api/push-notification/',
                                CURLOPT_SSL_VERIFYPEER => 0,
                                CURLOPT_POSTFIELDS => array(
                                    'data' => GeneralHelper::encrypt(json_encode(array('type' => 'reward-won', 'id' => $user->id, 'value' => Reward::getReward('reward_invite'))))
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
                                    'data' => GeneralHelper::encrypt(json_encode(array('type' => 'reward-won', 'id' => $user->id, 'value' => Reward::getReward('reward_invite'))))
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

        if( $ret['success'] ) {
            if($for_app) {
                $ret['related_voxes'] = self::relatedSuggestedVoxes($user, $vox->id, 'related');
                $ret['suggested_voxes'] = self::relatedSuggestedVoxes($user, $vox->id, 'suggested');
            } else {
                request()->session()->regenerateToken();
                $ret['token'] = request()->session()->token();
            }
            $ret['vox_id'] = $vox->id;
            $ret['question_id'] = !empty($q) ? $q : null;
        }

        return Response::json( $ret );
    }

    public static function startOver($user_id) {

        $vox = Vox::with('questions')->find(Request::input('vox_id'));

        if (!empty($vox) && !empty($user_id)) {
            VoxAnswer::where('vox_id', Request::input('vox_id'))
            ->where('user_id', $user_id)
            ->delete();

            $ret = [
                'success' => true,
                'first_q' => $vox->questions->first()->id
            ];
        } else {
            $ret = [
                'success' => false,
            ];
        }

        return Response::json( $ret );
    }

    public static function requestSurvey($user, $for_app) {

        if(!empty($user) && $user->is_dentist) {

            $validator = Validator::make(Request::all(), [
                'title' => array('required', 'min:6'),
                'target' => array('required', 'in:worldwide,specific'),
                'target-countries' => array('required_if:target,==,specific'),
                'other-specifics' => array('required'),
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
                if($for_app) {
                    $target_countries = [];
                    foreach (request('target_countries') as $v) {
                        $target_countries[] = Country::find($v['id'])->name;
                    }
                } else {
                    $target_countries = [];
                    foreach (request('target-countries') as $v) {
                        $target_countries[] = Country::find($v)->name;
                    }
                }
      
                $mtext = 'New survey request from '.$user->getNames().'
                    
                Link to CMS: '.url("/cms/users/users/edit/".$user->id).'
                Survey title: '.request('title').'
                Survey target group location/s: '.request('target');

                if (request('target') == 'specific') {
                    $mtext .= '
                Survey target group countries: '.implode(',', $target_countries);
                }
                
                $mtext .= '
                Other specifics of survey target group: '.request('other-specifics').'
                Survey topics and the questions: '.request('topics');

                Mail::raw($mtext, function ($message) use ($user) {

                    $sender = config('mail.from.address-vox');
                    $sender_name = config('mail.from.name-vox');

                    $message->from($sender, $sender_name);
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

    public static function recommendDentavox($user, $for_app) {

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

                if($for_app) {
                    if(request('recommend_id')) {
                        $new_recommendation = Recommendation::find(request('recommend_id'));

                    } else {
                        $new_recommendation = new Recommendation;
                        $new_recommendation->save();
                    }

                } else {
                    if (session('recommendation')) {
                        $new_recommendation = Recommendation::find(session('recommendation'));
                    } else {
                        $new_recommendation = new Recommendation;
                        $new_recommendation->save();
                        session([
                            'recommendation' => $new_recommendation->id
                        ]);
                    }
                }
                
                $new_recommendation->user_id = $user->id;
                $new_recommendation->scale = Request::input('scale');
                $new_recommendation->save();

                if (intval(Request::input('scale')) > 3) {
                    $user->fb_recommendation = false;
                    $user->save();

                    return Response::json( [
                        'recommend_id' => $new_recommendation->id,
                        'success' => true,
                        'recommend' => true,
                        'description' => false,
                    ] );
                }

                if (intval(Request::input('scale')) <= 3) {
                    $user->fb_recommendation = true;
                    $user->save();
                }

                if (!empty(Request::input('description'))) {
                    $new_recommendation->description = Request::input('description');
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

        return Response::json( [
            'success' => false,
        ] );
    }

    public static function getVoxList($user, $admin, $taken) {

        if( $user ) {

            $voxes = !empty($admin) ? User::getAllVoxes() : $user->voxesTargeting();
            if(request('filter_item')) {
                if(request('filter_item') == 'taken') {
                    $voxes = $voxes->whereIn('id', $taken);
                } else if(request('filter_item') == 'untaken') {
                    $voxes = $voxes->whereNotIn('id', $taken);
                } else if(request('filter_item') == 'all') {

                }
            } else {
                if($taken) {
                    $voxes = $voxes->whereNotIn('id', $taken);
                }
            }
        } else {
            $voxes = User::getAllVoxes();
        }

        $voxes = $voxes->where('type', 'normal');

        if(request('category') && request('category') != 'all') {
            $cat = request('category');
            $voxes->whereHas('categories', function($query) use ($cat) {
                $query->whereHas('category', function($q) use ($cat) {
                    $q->where('id', $cat);
                });
            });
        }

        if(request('survey_search')) {

            $searchTitle = trim(Request::input('survey_search'));
            $titles = preg_split('/\s+/', $searchTitle, -1, PREG_SPLIT_NO_EMPTY);

            $voxes->whereHas('translations', function ($query) use ($titles) {
                foreach ($titles as $title) {
                    $query->where('title', 'LIKE', '%'.$title.'%')->where('locale', 'LIKE', 'en');
                }
            });
        }

        $voxList = $voxes->get();

        $sort = request('sortable_items') ?? 'newest-desc';
        $voxList = $voxList->sortByDesc(function ($voxlist) use ($sort) {
            $sort_name = explode('-', $sort)[0];
            $sort_type = explode('-', $sort)[1];

            if($sort_name == 'newest') {

                if($sort_type == 'desc') {

                    if(!empty($voxlist->featured)) {
                        return 100000 - $voxlist->sort_order;
                    } else {
                        return 10000 - $voxlist->sort_order;
                    }
                } else {
                    if(!empty($voxlist->featured)) {
                        return 100000 + $voxlist->sort_order;
                    } else {
                        return 10000 + $voxlist->sort_order;
                    }
                }
            } else if($sort_name == 'popular') {

                if($sort_type == 'desc') {

                    if(!empty($voxlist->featured)) {
                        return 100000 + $voxlist->rewardsCount();
                    } else {
                        return 10000 + $voxlist->rewardsCount();
                    }
                } else {
                    if(!empty($voxlist->featured)) {
                        return 100000 - $voxlist->rewardsCount();
                    } else {
                        return 10000 - $voxlist->rewardsCount();
                    }
                }
            } else if($sort_name == 'reward') {

                if($sort_type == 'desc') {

                    if(!empty($voxlist->featured)) {
                        return 10000000000 + $voxlist->getRewardTotal();
                    } else {
                        return 10 + $voxlist->getRewardTotal();
                    }
                } else {
                    if(!empty($voxlist->featured)) {
                        return 10000000000 - $voxlist->getRewardTotal();
                    } else {
                        return 10 - $voxlist->getRewardTotal();
                    }
                }
            } else if($sort_name == 'duration') {

                $duration = !empty($voxlist->manually_calc_reward) && !empty($voxlist->dcn_questions_count) ? ceil( $voxlist->dcn_questions_count/6) : ceil( $voxlist->questionsCount()/6);

                if($sort_type == 'desc') {

                    if(!empty($voxlist->featured)) {
                        return 100000 + $duration;
                    } else {
                        return 10000 + $duration;
                    }
                } else {
                    if(!empty($voxlist->featured)) {
                        return 100000 - $duration;
                    } else {
                        return 10000 - $duration;
                    }
                }
            }
        });

        $get = request()->query();
        unset($get['page']);
        unset($get['submit']);

        if ($user) {
            $voxList = $user->notRestrictedVoxesList($voxList);
            $voxList = self::paginate($user, $voxList, 6, request('slice') ?? 1 )->appends($get);
        } else {
            $voxList = self::paginate($user, $voxList, 6, request('slice') ?? 1)->withPath(App::getLocale().'/paid-dental-surveys/')->appends($get);
        }

        return $voxList;
    }

    public static function paginate($user, $items, $perPage, $page, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $pageItems = $perPage;
        if(!empty($user) && $user->is_dentist && $page == 1) {
            $pageItems = $pageItems - 1;
        }
        return new LengthAwarePaginator($items->forPage($page, $pageItems), $items->count(), $pageItems, $page, $options);
    }





    // ---------- DailyPolls ---------



    public static function getPollContent($poll_id, $user, $admin, $for_app) {

		$ret = [
        	'success' => false,
        ];
		
		$poll = Poll::find($poll_id);

		if(!empty($poll)) {

			if (!empty($user)) {
				$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first();
			} else {
                if($for_app) {
                    $taken_daily_poll = null;
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
			}

			if (!empty($poll) && $poll->status == 'open' && empty($taken_daily_poll) || !empty($admin)) {

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

                if($for_app) {
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
                } else {

                    $answers = [];
                    foreach ($json_answers as $key => $answer) {
                        $answers[] = Poll::handleAnswerTooltip($answer);
                    }

                    $randomize_answers = empty($poll->dont_randomize_answers) && $poll->type != 'scale' ? true : false;
                }
                
                $ret = [
		        	'success' => true,
		        	'title' => $poll->question,
		        	'url' => getLangUrl('poll/'.$poll->id),
		        	'answers' => $answers,
		        	'date_href' => date('d-m-Y',$poll->launched_at->timestamp),
		        	'show_poll' => true,
		        ];

                if($for_app) {
                    $ret['id'] = $poll->id;
                    $ret['scale_type'] = $poll->type == 'scale' ? true : false;
                } else {
                    $ret['randomize_answers'] = $randomize_answers;
                }
			}
		}
		
        return Response::json( $ret );
	}


    public static function getPollStats($poll_id, $user) {

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
	        	'chart' => self::chartData($poll),
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

    public static function chartData($poll) {

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

    public static function doPoll($id, $user, $admin, $for_app) {

        $ret = [
            'success' => false,
        ];

		$poll = Poll::find($id);

		if (!empty($poll)) {
            $taken_daily_poll = !empty($user) ? PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first() : null;

            $more_polls_to_take = Poll::where('status', 'open');
            if($taken_daily_poll) {
                $more_polls_to_take = $more_polls_to_take->whereNotIn('id', [$taken_daily_poll->id]);
            }
            $more_polls_to_take = $more_polls_to_take->first();

			if ($poll->respondentsCount() >= 100) {
				$ret = [
					'success' => false,
					'closed_poll' => $poll->id,
				];

                if($for_app) {
                    $ret['next_poll'] = $more_polls_to_take ? $more_polls_to_take->id : false;
                }

				return Response::json( $ret );
			}

			$a = intval(Request::input('answer'));

			if(!$user && $poll->status == 'open') {

				if(!$admin) {
					$country_code = strtolower(\GeoIP::getLocation(User::getRealIp())->iso_code);
					$country_db = Country::where('code', 'like', $country_code)->first();

					$answer = new PollAnswer;
					$answer->user_id = 0;
					$answer->country_id = !empty($country_db) ? $country_db->id : null;
					$answer->poll_id = $poll->id;
					$answer->answer = $a;
					$answer->save();

					$poll->recalculateUsersPercentage();

                    if(!$for_app) {
                        $cv = Cookie::get('daily_poll');
                        if(empty($cv)) {
                            $cv = [];
                        } else {
                            $cv = json_decode($cv, true);
                        }
                        
                        $cv[$poll->id] = $answer->id;
                        Cookie::queue('daily_poll', json_encode($cv), 1440, null, '.dentacoin.com');
                    }
				}

				self::checkStatus($poll);

				$ret = [
					'success' => true,
					'logged' => false,
					'chart' => self::chartData($poll),
					'respondents' => trans('vox.daily-polls.popup.respondents', ['current_respondents' => $poll->respondentsCount() ]),
					'has_user' => false,
				];

                if($for_app) {
                    $ret['answer_id'] = $answer->id;
                }

				return Response::json( $ret );
			}

			if( empty($taken_daily_poll) && $poll->status == 'open' ) {

				if(!$admin) {

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
				}

				self::checkStatus($poll);

				$taken_daily_polls = PollAnswer::where('user_id', $user->id)->pluck('poll_id')->toArray();
				$more_polls_to_take = Poll::where('status', 'open')->whereNotIn('id', $taken_daily_polls)->first();

				$ret = [
					'success' => true,
					'chart' => self::chartData($poll),
					'next_poll' => $more_polls_to_take ? $more_polls_to_take->id : false,
					'respondents' => trans('vox.daily-polls.popup.respondents', ['current_respondents' => $poll->respondentsCount() ]),
					'has_user' => true,
				];
			}
		}

        return Response::json( $ret );
	}

	public static function checkStatus($poll) {
		$respondents = $poll->respondentsCount();

		if ($respondents >= 100) {
			$poll->status = 'closed';
			$poll->hasimage_social = false;
			$poll->save();
		}
	}

	public static function getDailyPollsByMonth($user, $admin, $for_app) {

		$year = Request::input('year') ?? date('Y');
        $month = Request::input('month') ?? date('m');

        $all_daily_polls = Poll::where('launched_at', '>=', $year."-".$month."-01 00:00:00")
		->where('launched_at', '<', $year."-".str_pad($month, 2)."-31 23:59:59");

		if( empty($admin)) {
			$all_daily_polls = $all_daily_polls->where('status', '!=', 'scheduled');
		}

		$all_daily_polls = $all_daily_polls->orderBy('launched_at','asc')->get();

		if ($all_daily_polls->isNotEmpty()) {
            $daily_polls = [];
			foreach ($all_daily_polls as $poll) {
				
				if (!empty($user)) {
					$taken_daily_poll = PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->first();
				} else {
                    if(!$for_app) {
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
                    } else {
                        $taken_daily_poll = false;
                    }
				}

				$to_take_poll = $poll->status=='open' && !$taken_daily_poll;
                $vox_category = VoxCategory::find($poll->category);

				$daily_polls[] = [
                    'title' => $poll->question,
					'category_image' => $vox_category->getImageUrl(),
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
					'color' => $vox_category->color,
				];
			}
		} else {
			$daily_polls = null;
		}

        return $daily_polls;
	}
    
}