<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\IncompleteRegistration;
use App\Models\WithdrawalsCondition;
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
use App\Models\InvalidEmail;



use Carbon\Carbon;

use Response;
use Request;
use Mail;
use Log;
use DB;

class YouTubeController extends FrontController {

    /**
     * recover token from admin for youtube video reviews
     */
    public function test() {

        if(!empty($this->admin)) {

            $transactions = DcnTransaction::where('status', 'unconfirmed')->where('processing', 0)->where('test3', 0)->orderBy('id', 'asc')->take(10)->get();

            foreach ($transactions as $trans) {
                $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT).': '.str_pad($trans->amount, 10, ' ', STR_PAD_LEFT).' DCN '.str_pad($trans->status, 15, ' ', STR_PAD_LEFT).' -> '.$trans->address.' || '.$trans->tx_hash;
                echo $log.PHP_EOL;

                if( $trans->tx_hash ) {
                    $curl = file_get_contents('https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$trans->tx_hash.'&apikey='.env('ETHERSCAN_API'));
                    if(!empty($curl)) {
                        echo 'result: '.$curl.PHP_EOL;

                        $curl = json_decode($curl, true);
                        if($curl['status']) {
                            if(!empty($curl['result']['status'])) {
                                $trans->status = 'completed';
                                $trans->save();
                                if( $trans->user && !empty($trans->user->email) ) {
                                    $trans->user->sendTemplate( 20, [
                                        'transaction_amount' => $trans->amount,
                                        'transaction_address' => $trans->address,
                                        'transaction_link' => 'https://etherscan.io/tx/'.$trans->tx_hash
                                    ], $trans->type=='vox' ? 'vox' : 'trp' );
                                }
                                echo 'COMPLETED!'.PHP_EOL;
                                sleep(1);
                            }
                        }
                    }
                }


                $trans->test3 = true;
                $trans->save();
            }

            dd('DONE');




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