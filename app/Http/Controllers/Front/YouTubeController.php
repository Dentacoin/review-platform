<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\IncompleteRegistration;
use App\Models\ScrapeDentistResult;
use App\Models\UnclaimedDentist;
use App\Models\DcnTransaction;
use App\Models\ScrapeDentist;
use App\Models\VoxQuestion;
use App\Models\PollAnswer;
use App\Models\UserInvite;
use App\Models\Blacklist;
use App\Models\DcnReward;
use App\Models\Country;
use App\Models\UserAsk;
use App\Models\Review;
use App\Models\Reward;
use App\Models\Email;
use App\Models\User;
use App\Models\Poll;
use App\Models\Vox;
use App\Models\Dcn;

use Carbon\Carbon;

use Response;
use Request;
use Mail;
use DB;

class YouTubeController extends FrontController
{
    public function test() {

        
        exit;


        // $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

        // $request_body = json_decode('{
        //   "recipient_emails": [
        //     "gergana_vankova@abv.bg"
        //   ]
        // }');
        // $group_id = "16467";

        // try {
        //     $response = $sg->client->asm()->groups()->_($group_id)->suppressions()->post($request_body);    
        //     print $response->statusCode() . "\n";
        //     print_r($response->headers());
        //     print $response->body() . "\n";
        // } catch (Exception $e) {
        //     echo 'Caught exception: ',  $e->getMessage(), "\n";
        // }

        // dd('done');


        exit;
    }
}