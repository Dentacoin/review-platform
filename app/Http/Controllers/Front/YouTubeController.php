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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://payment-server-info.dentacoin.com/check-l2-transaction");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "hash=0x3c154c9e2179f3aa18700161451fbca847485796e345f16853da8ad0d6e55480");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = json_decode(curl_exec($ch));
        curl_close($ch);

        dd($resp);
        if(!empty($resp)) {
            dd($resp);
        }

        exit;
        
        $transactions = DcnTransaction::where('status', 'unconfirmed')
        ->whereNotNull('tx_hash')
        ->where('cronjob_unconfirmed', 0)
        ->where('processing', 0)
        ->orderBy('id', 'asc')
        ->take(50)
        ->get();

        if($transactions->isEmpty()) {
            $transactions = DcnTransaction::where('status', 'unconfirmed')
            ->whereNotNull('tx_hash')
            ->where('processing', 0)
            ->orderBy('id', 'asc')
            ->get();

            if($transactions->isNotEmpty()) {
                foreach ($transactions as $trans) {
                    $trans->cronjob_unconfirmed = 0;
                    $trans->save();
                }

                $transactions = DcnTransaction::where('status', 'unconfirmed')
                ->whereNotNull('tx_hash')
                ->where('cronjob_unconfirmed', 0)
                ->where('processing', 0)
                ->orderBy('id', 'asc')
                ->take(50)
                ->get();
            }
        }

        if($transactions->isNotEmpty()) {

            $int = 0;
            foreach ($transactions as $trans) {
                $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT).': '.str_pad($trans->amount, 10, ' ', STR_PAD_LEFT).' DCN '.str_pad($trans->status, 15, ' ', STR_PAD_LEFT).' -> '.$trans->address.' || '.$trans->tx_hash;
                echo $log.PHP_EOL;

                $transactionIsCompleted = false;
                $found = false;
                $int++;

                if($trans->layer_type == 'l1') {
                    try {
                        $curl = file_get_contents('https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$trans->tx_hash.'&apikey='.env('ETHERSCAN_API'));
                    } catch (\Exception $e) {
                        $curl = false;
                    }
                    if(!empty($curl)) {
                        $trans->cronjob_unconfirmed = 1;
                        $trans->save();

                        $curl = json_decode($curl, true);
                        if($curl['status']) {
                            if($curl['result']['status'] === '1') {
                                $transactionIsCompleted = true;
                            }
                        }
                    }
                } else {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,"https://payment-server-info.dentacoin.com/check-l2-transaction");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "hash=".$trans->tx_hash);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                    // receive server response ...
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $resp = json_decode(curl_exec($ch));
                    curl_close($ch);

                    if(!empty($resp)) {
                        $trans->cronjob_unconfirmed = 1;
                        $trans->save();

                        if(isset($resp->success) && isset($resp->status)) {
                            if($resp->status == 'success') {
                                $transactionIsCompleted = true;
                            }
                        }
                    }
                }

                if($transactionIsCompleted) {
                    $trans->status = 'completed';
                    $trans->cronjob_unconfirmed = 0;
                    $trans->save();

                    $dcn_history = new DcnTransactionHistory;
                    $dcn_history->transaction_id = $trans->id;
                    $dcn_history->status = 'completed';
                    $dcn_history->save();

                    if( $trans->user && !empty($trans->user->email) ) {
                        $trans->user->sendTemplate( 20, [
                            'transaction_amount' => $trans->amount,
                            'transaction_address' => $trans->address,
                            'transaction_link' => config('transaction-links')[$trans->layer_type].$trans->tx_hash
                        ], $trans->type=='vox' ? 'vox' : ( $trans->type=='trp' ? 'trp' : 'dentacoin') );
                    }
                    $found = true;
                    echo 'COMPLETED!'.PHP_EOL;
                    if($int % 5 == 0) {
                        sleep(1);
                    }
                }

                //after 14 days
                if(!$found && Carbon::now()->diffInMinutes($trans->updated_at) > 60*336 && !GeneralHelper::isGasExpensive()) {  //14 days = 24 * 14 = 336
                    $trans->status = 'not_sent';
                    $trans->message = 'Unconfirmed from more than 14 days';
                    $trans->unconfirmed_retry = true;
                    $trans->save();

                    echo 'CHANGING STATUS -> '.$trans->id.PHP_EOL;
                }
            }
        }


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