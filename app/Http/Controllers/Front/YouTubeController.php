<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use DB;

use App\Models\Blacklist;
use App\Models\Country;
use App\Models\Dcn;
use App\Models\PollAnswer;
use App\Models\DcnReward;
use App\Models\DcnTransaction;
use App\Models\ScrapeDentistResult;
use App\Models\ScrapeDentist;
use App\Models\User;
use App\Models\Vox;
use App\Models\Review;
use App\Models\VoxQuestion;
use App\Models\Email;

use Carbon\Carbon;

class YouTubeController extends FrontController
{
    public function test() {

        // $vox = Vox::find(45);
        // $max = $this->tryAll($vox, null, []);

        // echo '<br/><br/>Max: '.$max;



        //to add trigger answers like 1-3
        //to add welcome q's triggers
        //scale qs triggers?

    	exit;
    }

    // public function tryAll($vox, $question, $answers) {

    //     $found = false;
    //     foreach ($vox->questions as $vq) {

    //         if( $found || empty($question) ) {
    //             $paths = [];

    //             //
    //             //Tova trigger li e?
    //             //
    //             $skip = false;
    //             if($vq->question_trigger) {
    //                 $skip = true;


    //                 if($vq->question_trigger=='-1') {
    //                     foreach ($vox->questions as $originalTrigger) {
    //                         if($originalTrigger->id == $vq->id) {
    //                             break;
    //                         }

    //                         if( $originalTrigger->question_trigger && $originalTrigger->question_trigger!='-1' ) {
    //                            $triggers = $originalTrigger->question_trigger;
    //                         }
    //                     }
    //                 } else {
    //                     $triggers = $vq->question_trigger;
    //                 }

    //                 $triggers = explode(';', $triggers);


    //                 foreach ($triggers as $trigger) {


    //                     list($triggerId, $triggerAnswers) = explode(':', $trigger);
    //                     $triggerAnswers = explode(',', $triggerAnswers);

    //                     //echo 'Trigger for: '.$triggerId.' / Valid answers '.var_export($triggerAnswers, true).' / Answer: '.$answers[$triggerId].'<br/>';

    //                     if( !empty($answers[$triggerId]) && in_array($answers[$triggerId], $triggerAnswers) ) {
    //                         $skip = false;
    //                         //echo 'OK<br/>';
    //                     } else {
    //                         //echo 'SKIP<br/>';
    //                     }

    //                 }
    //             }








    //             //
    //             //Ima li trigger, koito zavisi ot tozi vapros?
    //             //

    //             $hasRelatedTrigger = [];

    //             foreach ($vox->questions as $tq) {
    //                 if($tq->question_trigger) {
    //                     $triggers = explode(';', $tq->question_trigger);
    //                     foreach ($triggers as $trigger) {
    //                         $triggerQuestion = explode(':', $trigger)[0];
    //                         if($triggerQuestion==$vq->id) {
    //                             $triggerAnswers = explode(',' , explode(':', $trigger)[1]);
    //                             $hasRelatedTrigger = array_merge( $hasRelatedTrigger, $triggerAnswers );
    //                         }
    //                     }
    //                 }
    //             }

    //             //
    //             //Davame otgovori
    //             //

    //             if($hasRelatedTrigger) {
    //                 echo '<br/><br/>';
    //                 $answers_count = count(json_decode($vq->answers, true));
    //                 foreach ($hasRelatedTrigger as $i) {
    //                     $newAnswers = $answers;
    //                     $newAnswers[$vq->id] = $i;
    //                     echo $vq->id.' ('.$i.' | '.($skip ? 0 : 1).') => ';
    //                     $paths[] = $this->tryAll($vox, $vq, $newAnswers);
    //                 }

    //                 if( $answers_count != count( $hasRelatedTrigger ) ) {
    //                     $newAnswers = $answers;
    //                     $newAnswers[$vq->id] = 'x';
    //                     echo $vq->id.' (x | '.($skip ? 0 : 1).') => ';
    //                     $paths[] = $this->tryAll($vox, $vq, $newAnswers);    
    //                 }
    //             } else {
    //                 $newAnswers = $answers;
    //                 $newAnswers[$vq->id] = 'x';
    //                 echo $vq->id.' (x | '.($skip ? 0 : 1).') => ';
    //                 $paths[] = $this->tryAll($vox, $vq, $newAnswers);
    //             }

    //             //if( empty($question) ) {
    //             //    echo 'RESULT: '.(($skip ? 0 : 1) + max($paths)).'<br/>';
    //             //}

    //             return ($skip ? 0 : 1) + max($paths);

    //         }

    //         if($vq->id == $question->id) {
    //             $found = true;
    //         }
    //     }

    //     echo '<br/>';
    //     return 0;
    // }
}