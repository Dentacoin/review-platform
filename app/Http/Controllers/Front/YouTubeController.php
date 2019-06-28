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


    	$query = "
			SELECT 
				`id`
			FROM 
				users
			WHERE 
				`is_dentist` = 1
				AND `id` NOT IN ( SELECT `user_id` FROM `emails` WHERE  `template_id` IN ( 55, 56) AND `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00' )
				AND `created_at` < '".date('Y-m-d', time() - 86400*30)." 00:00:00'
				AND `deleted_at` is null
				AND `unsubscribe` is null



				AND `status` = 'test'
				AND `id` = '68809'
		";

		$users = DB::select(
			DB::raw($query), []
		);


		// foreach ($users as $u) {
		// 	$user = User::find($u->id);

		// 	if (!empty($user)) {

		// 		$found = false;
		// 		if ($user->reviews_in()->isNotEmpty()) {
		// 			foreach ($user->reviews_in() as $review) {
		// 				if ($review->created_at->timestamp > time() - 86400*30 ) {
		// 					$found = true;
		// 					break;
		// 				}
		// 			}
		// 		}

		// 		if ($found) {

		// 			$avg_rating = 0;
		// 			foreach($user->getMontlyRating() as $cur_month_reviews) {
		// 				$avg_rating += $cur_month_reviews->rating;
		// 			}

		// 			$cur_month_rating = number_format($avg_rating / $user->getMontlyRating()->count(), 2);
		// 			$cur_month_reviews_num = $user->getMontlyRating()->count();

		// 			$prev_avg_rating = 0;
		// 			foreach($user->getMontlyRating(1) as $prev_month_reviews) {
		// 				$prev_avg_rating += $prev_month_reviews->rating;
		// 			}

		// 			$prev_month_rating = !empty($prev_avg_rating) ? $prev_avg_rating / $user->getMontlyRating(1)->count() : 0;
		// 			$prev_month_reviews_num = $user->getMontlyRating(1)->count();

		// 			if (!empty($prev_month_rating)) {
						
		// 				if ($cur_month_rating < $prev_month_rating) {
		// 					$cur_month_rating_percent = intval((($cur_month_rating - $prev_month_rating) / $prev_month_rating) * -100).'%';
		// 					$change_month = 'lower than last month';
		// 				} else if($cur_month_rating > $prev_month_rating) {
		// 					$cur_month_rating_percent = intval((($cur_month_rating / $prev_month_rating) - 1) * 100).'%';
		// 					$change_month = 'higher than last month';
		// 				} else {
		// 					$cur_month_rating_percent = '';
		// 					$change_month = 'the same as last month';
		// 				}
		// 			} else {
		// 				$cur_month_rating_percent = '100%';
		// 				$change_month = 'higher than last month';
		// 			}


		// 			if (!empty($prev_month_reviews_num)) {
		// 				if ($cur_month_reviews_num < $prev_month_reviews_num) {
		// 					$reviews_num_percent_month = intval((($cur_month_reviews_num - $prev_month_reviews_num) / $prev_month_reviews_num) * -100).'%';
		// 					$change_month_num = 'lower than last month';

		// 				} else if($cur_month_reviews_num > $prev_month_reviews_num) {
		// 					$reviews_num_percent_month = intval((($cur_month_reviews_num / $prev_month_reviews_num) - 1) * 100).'%';
		// 					$change_month_num = 'higher than last month';
		// 				} else {
		// 					$reviews_num_percent_month = '';
		// 					$change_month_num = 'the same as last month';
		// 				}
		// 			} else {
		// 				$reviews_num_percent_month = '100%';
		// 				$change_month_num = 'higher than last month';
		// 			}


		// 			//status?
		// 			$country_id = $user->country->id;
		// 			$country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
		//                 $query->where('country_id', $country_id);
		//             })->get();

		// 			$country_rating = 0;
		// 			foreach ($country_reviews as $c_review) {
		// 				$country_rating += $c_review->rating;
		// 			}

		// 			$avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

		// 			if (!empty($avg_country_rating)) {
		// 				if ($cur_month_rating < $avg_country_rating) {
		// 					$cur_country_month_rating_percent = intval((($cur_month_rating - $avg_country_rating) / $avg_country_rating) * -100).'%';
		// 					$change_country = 'lower than the average';
		// 				} else if($cur_month_rating > $avg_country_rating) {
		// 					$cur_country_month_rating_percent = intval((($cur_month_rating / $avg_country_rating) - 1) * 100).'%';
		// 					$change_country = 'higher than the average';
		// 				} else {
		// 					$cur_country_month_rating_percent = '0%';
		// 					$change_country = 'same as average';
		// 				}
		// 			} else {
		// 				$cur_month_rating_percent = '100%';
		// 				$change_country = 'higher than the average';
		// 			}


		// 			// $top3_dentists_query = User::where('is_dentist', 1)->where('status', 'approved')->where('country_id', $user->country_id)->orderby('avg_rating', 'desc')->take(3)->get();

		// 			// $top3_dentists = [];
		// 			// foreach ($top3_dentists_query as $top3_dentist) {
		// 			// 	$top3_dentists[] = '<a href="'.$top3_dentist->getLink().'">'.$top3_dentist->getName().'</a>';
		// 			// }

		// 			$user->sendGridTemplate(55, [
		// 				'score_last_month_aver' => $cur_month_rating,
		// 				'score_percent_month' => $cur_month_rating_percent,
		// 				'change_month' => $change_month,
		// 			 	'reviews_last_month_num' => $cur_month_reviews_num,
		// 			 	'score_percent_country' => $cur_country_month_rating_percent,
		// 			 	'change_country' => $change_country,
		// 			 	'reviews_num_percent_month' => $reviews_num_percent_month,
		// 			 	'change_month_num' => $change_month_num,
		// 				// 'top3-dentists' => implode('<br/>',$top3_dentists)
		// 			]);

		// 		} else {


		// 			$country_id = $user->country->id;
		// 			$country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
		//                 $query->where('country_id', $country_id);
		//             })->get();

		//             if ($country_reviews->count()) {
		//             	$country_rating = 0;
		// 				foreach ($country_reviews as $c_review) {
		// 					$country_rating += $c_review->rating;
		// 				}

		// 				$avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

		// 				$compare_with_others = 'Other dentists in '.Country::find($user->country_id)->name.' achieved average rating score: '.$avg_country_rating.'. Are you ready to challenge them?';
		//             } else {
		//             	$compare_with_others = 'Don\'t miss the chance to stand out from other dentists in '.Country::find($user->country_id)->name.' this month! Invite your patients to post a review and boost your monthly performance!';
		//             }

		//             $month = \Carbon\Carbon::now();

		// 			$user->sendGridTemplate(56, [
		// 				'month' => $month->subMonth()->format('F'),
		// 				'compare_with_others' => $compare_with_others,
		// 			]);
		// 		}		
		// 	}
		// }
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