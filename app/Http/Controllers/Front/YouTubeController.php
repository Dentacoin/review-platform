<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use DB;

use App\Models\Blacklist;
use App\Models\Country;
use App\Models\Dcn;
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
}