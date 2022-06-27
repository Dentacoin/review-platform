<?php

namespace App\Services;

use App\Models\ReviewAnswer;
use App\Models\DcnReward;
use App\Models\UserBan;
use App\Models\Review;
use App\Models\User;

class TrpService {

    public static function deleteReview($item) {
        
        $uid = $item->user_id;
        $patient = User::where('id', $uid)->withTrashed()->first();

        ReviewAnswer::where([
            ['review_id', $item->id],
        ])->delete();

        $originalDentist = $item->original_dentist;

        $reward_for_review = DcnReward::where('user_id', $patient->id)
        ->where('platform', 'trp')
        ->where('type', 'review')
        ->where('reference_id', $item->id)
        ->first();

        if (!empty($reward_for_review)) {
            $reward_for_review->delete();
        }

        Review::destroy( $item->id );

        $originalDentist->recalculateRating();
        $substitutions = [
            'spam_author_name' => $patient->name,
        ];
        
        $originalDentist->sendGridTemplate(87, $substitutions, 'trp');

        $ban = new UserBan;
        $ban->user_id = $patient->id;
        $ban->domain = 'trp';
        $ban->type = 'spam-review';
        $ban->save();

        $notifications = $patient->website_notifications;
        if(!empty($notifications)) {
            if(($key = array_search('trp', $notifications)) !== false) {
                unset($notifications[$key]);
            }

            $patient->website_notifications = $notifications;
            $patient->save();
        }

        $request_body = new \stdClass();
        $request_body->recipient_emails = [$patient->email];
        $trp_group_id = config('email-preferences')['product_news']['trp']['sendgrid_group_id'];

        $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
        $sg->client->asm()->groups()->_($trp_group_id)->suppressions()->post($request_body);

        $patient->sendGridTemplate(86, null,'trp');
    }
}