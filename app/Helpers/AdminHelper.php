<?php

namespace App\Helpers;

use App\Models\DcnTransactionHistory;
use App\Models\UserHistory;
use App\Models\VoxQuestion;

use App\Helpers\VoxHelper;

class AdminHelper {

    public static function paginationsFunction($total_pages, $adjacents, $page) {

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
            $start = 1;
            $end   = $total_pages;
        } else {
            if(($page - $adjacents) > 1) { 
                if(($page + $adjacents) < $total_pages) { 
                    $start = ($page - $adjacents);            
                    $end   = ($page + $adjacents);         
                } else {             
                    $start = ($total_pages - (1+($adjacents*2)));  
                    $end   = $total_pages;               
                }
            } else {               
                $start = 1;                                
                $end   = (1+($adjacents * 2));             
            }
        }

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    public static function bumpTransaction($transaction, $admin_id=null) {

        if($transaction->status == 'first' && !empty($transaction->user) && !$transaction->user->is_dentist) {
            $user_history = new UserHistory;
            if($admin_id) {
                $user_history->admin_id = $admin_id;
            }
            $user_history->user_id = $transaction->user->id;
            $user_history->patient_status = $transaction->user->patient_status;
            $user_history->save();

            $transaction->user->patient_status = 'new_verified';
            $transaction->user->save();
        }

        $dcn_history = new DcnTransactionHistory;
        $dcn_history->transaction_id = $transaction->id;
        if($admin_id) {
            $dcn_history->admin_id = $admin_id;
        }
        $dcn_history->status = 'new';
        $dcn_history->old_status = $transaction->status;
        $dcn_history->history_message = $admin_id ? 'Bumped by admin' : 'Bumped automatically';
        $dcn_history->save();

        $transaction->status = 'new';
        $transaction->processing = 0;
        $transaction->retries = 0;
        $transaction->save();
    }

    public static function getQuestionTriggers($question, $scales) {
        $trigger = '';
        $trigger_same_as_prev = false;

        if($question->question_trigger) {

            foreach (explode(';', $question->question_trigger) as $v) {
                $question_id = explode(':',$v)[0];

                if($question_id==-1) {
                    $trigger .= 'Same as previous<br/>';
                    $trigger_same_as_prev = $question->id;
                } else if(!is_numeric($question_id)) {
                    $trigger .= ($question_id == 'age_groups' ? 'Age groups' : ($question_id == 'gender' ? 'Gender' : config('vox.details_fields.'.$question_id)['label'])).' : '.explode(':',$v)[1];
                } else {
                    $q = VoxQuestion::find($question_id);

                    if(!empty($q)) {
                        if (!empty(explode(':',$v)[1])) {
                            $answ = explode(':',$v)[1];

                            $questionAnswers = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);

                            if (str_contains($answ, '-')) {

                                if(str_contains($answ, ',')) {

                                    $answerText = [];
        
                                    foreach (explode(',', $answ) as $ar) {
                                        if(mb_strpos($ar, '-')!==false) {
                                            list($from, $to) = explode('-', $answ);

                                            for ($i=$from; $i <= $to ; $i++) {
                                                // dd($questionAnswers, $i);
                                                
                                                try {
                                                    $answerText[] = VoxHelper::getQuestionAnswerText($questionAnswers, $i);
                                                } catch (\Exception $e) {
                                                    $answerText[] = $i;
                                                }
                                            }
                                        } else {
                                            $answerText[] = VoxHelper::getQuestionAnswerText($questionAnswers, $ar);
                                        }
                                    }

                                    $answerText = implode('; ',$answerText);
                                } else {

                                    $answerText = [];
                                    list($from, $to) = explode('-', $answ);

                                    for ($i=$from; $i <= $to ; $i++) {
                                        // dd($questionAnswers, $i);
                                        
                                        try {
                                            $answerText[] = VoxHelper::getQuestionAnswerText($questionAnswers, $i);
                                        } catch (\Exception $e) {
                                            $answerText[] = $i;
                                        }
                                    }

                                    $answerText = implode('; ',$answerText);
                                    // dd($answerText);
                                }
                            } else {

                                if(str_contains($answ, ',')) {

                                    $answerText = [];
        
                                    foreach (explode(',', $answ) as $ar) {
                                        if(mb_strpos($ar, '-')!==false) {
                                            list($from, $to) = explode('-', $answ);

                                            for ($i=$from; $i <= $to ; $i++) {
                                                // dd($questionAnswers, $i);
                                                
                                                try {
                                                    $answerText[] = VoxHelper::getQuestionAnswerText($questionAnswers, $i);
                                                } catch (\Exception $e) {
                                                    $answerText[] = $i;
                                                }
                                            }
                                        } else {
                                            $answerText[] = VoxHelper::getQuestionAnswerText($questionAnswers, $ar);
                                        }
                                    }

                                    $answerText = implode('; ',$answerText);
                                } else {
                                    $answerText = VoxHelper::getQuestionAnswerText($questionAnswers, $answ);
                                }
                            }
                            // dd($questionAnswers, $answ);

                            $trigger .= '<b>'.$q->order.'</b>. '.$q->questionWithTooltips().': <b>'.$answ.'</b> ( Answer Texts: '.$answerText.' )<br/>';
                        } else {
                            $trigger .= '<b>'.$q->order.'</b>. '.$q->questionWithTooltips().'<br/>';
                        }                            
                    }
                }
            }
        }

        return [
            'trigger' => $trigger,
            'trigger_same_as_prev' => $trigger_same_as_prev,
        ];
    }
}