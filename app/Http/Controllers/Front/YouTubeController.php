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

        // $vox = Vox::find(48);

        // $q = $vox->questions->first();

        // $max = $this->tryAll($vox, $q, []);

        //-----------------------------

        // $item = User::find(37530);

        // $substitutions = [
        //     "image_unclaimed_profile" => $item->getSocialCover(),
        //     "invitation_link" => getLangUrl( 'dentist/'.$item->slug.'/claim/'.$item->id , null, 'https://reviews.dentacoin.com/').'?'. http_build_query(['popup'=>'claim-popup']),
        // ];

        // if(!empty($item->email)) {
        //     $item->sendGridTemplate(43, $substitutions, 'trp');
        // }    


        //-----------------------------

    	// $users = Review::get();

    	// foreach ($users as $user) {
    	// 	$user->hasimage_social = false;
    	// 	$user->save();
    	// }

    	// $review = Review::find(8600);

    	// $review->generateSocialCover();
    	// dd($review->getSocialCover());


    	// $user = User::find(37530);
    	// $user->generateSocialCover();
    	// dd($user->getSocialCover());
        
    	exit;
    }

    // public function tryAll($vox, $question, $answers) {

    //     $answers_count = count(json_decode($question->answers, true));

    //     $found = false;
    //     foreach ($vox->questions as $vq) {
    //         if( $found ) {
    //             $paths = [];
    //             for ($i=1; $i <= $answers_count ; $i++) { 
    //                 $newAnswers = $answers;
    //                 $newAnswers[$question->id] = $i;
    //                 $paths[$i] = $this->tryAll($vox, $vq, $newAnswers);
    //             }

    //             return 1 + max($paths);

    //         }

    //         if($vq->id == $question->id) {
    //             $found = true;
    //         }
    //     }

    //     $backtrack = array_keys($answers);
    //     $cbacktrack = count($backtrack);
    //     for( $i = $cbacktrack-1;$i>=0;$i--) {
    //         echo $backtrack[$i].' - ';
    //     }

    //     echo $question->id;

    //     echo '<br/>';
    //     return 1;
    //}
}