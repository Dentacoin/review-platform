<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use DB;

use App\Models\Country;
use App\Models\Dcn;
use App\Models\DcnReward;
use App\Models\DcnTransaction;
use App\Models\User;
use App\Models\Vox;
use App\Models\Review;
use App\Models\VoxQuestion;
use App\Models\Email;

use \SendGrid\Mail\From as From;
use \SendGrid\Mail\To as To;
use \SendGrid\Mail\Subject as Subject;
use \SendGrid\Mail\PlainTextContent as PlainTextContent;
use \SendGrid\Mail\HtmlContent as HtmlContent;
use \SendGrid\Mail\Mail as Mail;

use Carbon\Carbon;

class YouTubeController extends FrontController
{
    public function test() {

    	exit;

    	// $us = User::find(37530);
    	// $us->sendGridTemplate(26);

    	//dd(session('intended'));
    	
    	//First 3 weeks engagement		

    	//Email2
    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id = 26
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id = 44
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*4)." 00:00:00' 
				AND `created_at` > '".date('Y-m-d', time() - 86400*7)." 00:00:00'
			GROUP BY 
				`user_id`
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// 	$user = User::find($e->user_id);
		// 	if (!empty($user)) {
		// 		$user->sendGridTemplate(44);
		// 	}
			
		// }
    	
        // dd($emails);
    

    	//Email3
    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id = 44
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id = 45
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*3)." 00:00:00' 
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// 	$user = User::find($e->user_id);
		// 	if (!empty($user)) {

		// 		$missingInfo = [];
				
		// 		if(empty($user->short_description)) {
  //                   $missingInfo[] = 'a short intro';
  //               }
				
		// 		if(empty($user->work_hours)) {
  //                   $missingInfo[] = 'opening hours';
  //               }
				
		// 		if(empty($user->socials)) {
  //                   $missingInfo[] = 'social media pages';
  //               }
				
		// 		if(empty($user->description)) {
  //                   $missingInfo[] = 'a description';
  //               }
				
		// 		if($user->photos->isEmpty()) {
  //                   $missingInfo[] = 'more photos';
  //               }

  //               if (!empty($missingInfo)) {
	 //                $substitutions = [
	 //                    'profile_missing_info' => $missingInfo[0],
	 //                ];

	 //                $user->sendGridTemplate(45, $substitutions);
	                
  //               } else {
	               
	 //            	$user->sendGridTemplate(45, null, null, 1);
  //               }

		// 	}			
		// }
    	
 	// 	dd($emails);


    	//Email4
    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id = 45
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id IN ( 46, 47)
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*4)." 00:00:00'
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// 	$user = User::find($e->user_id);
		// 	if (!empty($user) && $user->invites->isNotEmpty()) {
		// 		$user->sendGridTemplate(46);
		// 	} else {
		// 		$user->sendGridTemplate(47);
		// 	}		
		// }
    	
  //       dd($emails);


    	//Email5
    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id IN ( 46, 47)
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id = 48
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*10)." 00:00:00'
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// $user = User::find($e->user_id);
		// 	if (!empty($user) && $user->reviews_in()->isNotEmpty()) {

		// 		$substitutions = [
  //                   'score_last_month_aver' => number_format($user->avg_rating,2),
		// 			'reviews_last_month_num' => $user->reviews_in()->count().($user->reviews_in()->count() > 1 ? ' reviews' : ' review'),
  //               ];

  //               $user->sendGridTemplate(48, $substitutions);
		// 	}
		// }
    	
  //       dd($emails);











		//No reviews last 30 days        


		//Email2

    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id = 49
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id = 50 AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00'
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*4)." 00:00:00'
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// 	$user = User::find($e->user_id);
		// 	if (!empty($user)) {
		// 		$user->sendGridTemplate(50);
		// 	}	
		// }
    	
		//dd($emails);


		//Email3

    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id = 50
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id IN ( 51, 52) AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00'
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*7)." 00:00:00'
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// 	$user = User::find($e->user_id);
		// 	if (!empty($user) && $user->invites->isNotEmpty()) {

		// 		if ( $user->reviews_in()->isNotEmpty()) {
		// 			$id = $user->id;
		// 	        $from_day = Carbon::now()->subDays(11);

		// 	        $prev_reviews = Review::where(function($query) use ($id) {
		// 	            $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
		// 	        })
		// 	        ->where('created_at', '>=', $from_day)
		// 	        ->get();


		// 	        $rating = 0;
		// 			foreach($prev_reviews as $reviews) {
		// 				$rating += $reviews->rating;
		// 			}

		// 			$rating_avg = !empty($rating) ? $rating / $prev_reviews->count() : 0;

		// 			$results_sentence = 'Congrats, you are on the right track! In the past weeks you achieved '.number_format($rating_avg, 2).' rating score based on '.$prev_reviews->count().($prev_reviews->count() > 1 ? ' reviews' : ' review').'.';					

		// 		} else {

		// 			$invites_text = $user->invites->count() > 1 ? "invites" : "invite";

		// 			$results_sentence = 'Congrats, you are on the right track! In the past weeks you sent '.$user->invites->count().' review '.$invites_text.' to your patients.';
		// 		}

		// 		$substitutions = [
		// 			'results_sentence' => $results_sentence
		// 		];

		// 		$user->sendGridTemplate(51, $substitutions);

		// 	} else {
		// 		$user->sendGridTemplate(52);
		// 	}	
		// }
    	
		// dd($emails);


		




		//Email4

    	$query = "
			SELECT 
				* 
			FROM 
				emails 
			WHERE 
				template_id = 52
				AND `user_id` NOT IN ( 
					SELECT `user_id` FROM emails WHERE template_id IN ( 53, 54) AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00'
				)
				AND `user_id` NOT IN ( 
					SELECT `id` FROM users WHERE unsubscribe is not null
				)
				AND `created_at` < '".date('Y-m-d', time() - 86400*14)." 00:00:00'
		";

		$emails = DB::select(
			DB::raw($query), []
		);

		// foreach ($emails as $e) {
		// 	$user = User::find($e->user_id);
		// 	if (!empty($user) && $user->invites->isNotEmpty()) {

		// 		if ( $user->reviews_in()->isNotEmpty()) {
		// 			$id = $user->id;
		// 	        $from_day = Carbon::now()->subDays(25);

		// 	        $prev_reviews = Review::where(function($query) use ($id) {
		// 	            $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
		// 	        })
		// 	        ->where('created_at', '>=', $from_day)
		// 	        ->get();


		// 	        $rating = 0;
		// 			foreach($prev_reviews as $reviews) {
		// 				$rating += $reviews->rating;
		// 			}

		// 			$rating_avg = !empty($rating) ? $rating / $prev_reviews->count() : 0;

		// 			$results_sentence = 'Congrats, you are on the right track! In the past weeks you achieved '.number_format($rating_avg, 2).' rating score based on '.$prev_reviews->count().($prev_reviews->count() > 1 ? ' reviews' : ' review').'.';					

		// 		} else {

		// 			$invites_text = $user->invites->count() > 1 ? "invites" : "invite";

		// 			$results_sentence = 'Congrats, you are on the right track! In the past weeks you sent '.$user->invites->count().' review '.$invites_text.' to your patients.';
		// 		}

		// 		$substitutions = [
		// 			'results_sentence' => $results_sentence
		// 		];

		// 		$user->sendGridTemplate(53, $substitutions);

		// 	} else {
		// 		$user->sendGridTemplate(54);
		// 	}	
		// }
    	
		// dd($emails);





		//Monthly reminders

		// dd($users);




		//Create a Wallet

		//!!!!!! (repeates for six months) !!!!!!!!!!

    	$query = "
			SELECT 
				`rewards`.`user_id`
			FROM
				(
					SELECT 
						`user_id`, 
						sum(reward) as `rewards_total` 
					FROM 
						dcn_rewards 
					GROUP BY 
						`user_id`
				) `rewards`
				left OUTER JOIN
				(
					SELECT 
						`user_id`, 
						sum(reward) as `withdraws_total` 
					FROM 
						dcn_cashouts 
					GROUP BY 
						`user_id`
				) `cashouts`
				ON
					`rewards`.user_id = `cashouts`.user_id  
				LEFT JOIN 
					`users` `u`
				ON
					`u`.`id` = `rewards`.`user_id`
				WHERE
					`is_dentist` = 1
					AND `unsubscribe` is null
					AND `status` = 'approved'
					AND `dcn_address` is not null
					AND (rewards_total - IF (withdraws_total IS NULL, 0,withdraws_total) ) > 3000
					AND `deleted_at` is null
					AND `id` NOT IN ( 
						SELECT `user_id` FROM emails WHERE template_id = 57 AND `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00'
					)
					AND `id` NOT IN ( 
						SELECT `user_id` FROM emails WHERE template_id = 57 AND `created_at` < '".date('Y-m-d', time() - 86400*31*6)." 00:00:00'
					)
			LIMIT 100

		";

		$users = DB::select(
			DB::raw($query), []
		);

		// foreach ($users as $u) {
		// 	$user = User::find($u->user_id);

		// 	if (!empty($user)) {
		// 		$user->sendGridTemplate(57);
		// 	}
		// }
		// dd($users);

    }    
}