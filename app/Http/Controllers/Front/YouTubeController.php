<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\IncompleteRegistration;
use App\Models\WithdrawalsCondition;
use App\Models\ScrapeDentistResult;
use App\Models\DeletedUserEmails;
use App\Models\UnclaimedDentist;
use App\Models\DcnTransaction;
use App\Models\ScrapeDentist;
use App\Models\EmailTemplate;
use App\Models\AnonymousUser;
use App\Models\WalletAddress;
use App\Models\VoxAnswerOld;
use App\Models\InvalidEmail;
use App\Models\DentistClaim;
use App\Models\VoxQuestion;
use App\Models\DcnCashout;
use App\Models\PollAnswer;
use App\Models\CronjobRun;
use App\Models\UserAction;
use App\Models\UserInvite;
use App\Models\Blacklist;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\UserLogin;
use App\Models\UserPhoto;
use App\Models\VoxScale;
use App\Models\GasPrice;
use App\Models\UserBan;
use App\Models\Country;
use App\Models\UserAsk;
use App\Models\Review;
use App\Models\Reward;
use App\Models\Civic;
use App\Models\Email;
use App\Models\User;
use App\Models\Vox;
use App\Models\Dcn;

use App\Helpers\GeneralHelper;
use App\Helpers\VoxHelper;
use App\Exports\Export;
use Carbon\Carbon;

use Response;
use Request;
use Mail;
use Auth;
use Log;
use App;
use DB;

class YouTubeController extends FrontController {

    /**
     * recover token from admin for youtube video reviews
     */
    public function test() {
        // $users = User::where('status', 'dentist_no_email')->whereNull('country_id')->get();
        
        // foreach($users as $user) {
        //     if($user->invitor) {

        //         $user->country_id = $user->invitor->country_id;
        //         $user->city_id = $user->invitor->city_id;
        //         $user->zip = $user->invitor->zip;
        //         $user->state_name = $user->invitor->state_name;
        //         $user->state_slug = $user->invitor->state_slug;
        //         $user->city_name = $user->invitor->city_name;
        //         $user->address = $user->invitor->address;
        //         $user->lat = $user->invitor->lat;
        //         $user->lon = $user->invitor->lon;
        //         $user->custom_lat_lon = $user->invitor->custom_lat_lon;
        //         $user->save();
        //     }
        // }
        if(!empty($this->admin)) {

            $client = new \Google_Client();
            $client->setApplicationName('API Samples');
            $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
            // Set to name/location of your client_secrets.json file.
            $client->setAuthConfig( storage_path() . '/client_secrets.json');
            $client->setAccessType('offline');

            // Load previously authorized credentials from a file.
            $credentialsPath = storage_path() . '/yt-oauth2.json';
            if (false && file_exists($credentialsPath)) {
                $accessToken = json_decode(file_get_contents($credentialsPath), true);
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);

                if (isset($_GET['code'])) {
                    $credentialsPath = storage_path() . '/yt-oauth2.json';
                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

                    // Store the credentials to disk.
                    if(!file_exists(dirname($credentialsPath))) {
                        mkdir(dirname($credentialsPath), 0700, true);
                    }
                    file_put_contents($credentialsPath, json_encode($accessToken));
                }

                return;
            }
            $client->setAccessToken($accessToken);

            // Refresh the token if it's expired.
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
            }

            return $client;
        }
    }
}