<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\IncompleteRegistration;
use App\Models\ScrapeDentistResult;
use App\Models\UnclaimedDentist;
use App\Models\DcnTransaction;
use App\Models\ScrapeDentist;
use App\Models\EmailTemplate;
use App\Models\AnonymousUser;
use App\Models\DentistClaim;
use App\Models\DcnCashout;
use App\Models\PollAnswer;
use App\Models\CronjobRun;
use App\Models\UserAction;
use App\Models\UserInvite;
use App\Models\Blacklist;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\UserLogin;
use App\Models\GasPrice;
use App\Models\UserBan;
use App\Models\Country;
use App\Models\UserAsk;
use App\Models\Review;
use App\Models\Reward;
use App\Models\Civic;
use App\Models\Email;
use App\Models\User;
use App\Models\Poll;
use App\Models\Vox;
use App\Models\Dcn;

use Carbon\Carbon;

use Response;
use Request;
use Mail;
use Log;
use DB;

class YouTubeController extends FrontController
{
    public function test() {

        
    }
}