<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use DB;

use App\Models\Blacklist;
use App\Models\Country;
use App\Models\Dcn;
use App\Models\Poll;
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

        
    	exit;
    }
}