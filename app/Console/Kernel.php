<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\TransactionScammersByBalance;
use App\Models\TransactionScammersByDay;
use App\Models\IncompleteRegistration;
use App\Models\DcnTransactionHistory;
use App\Models\WithdrawalsCondition;
use App\Models\ScrapeDentistResult;
use App\Models\VoxQuestionAnswered;
use App\Models\UserSurveyWarning;
use App\Models\CronjobSecondRun;
use App\Models\CronjobThirdRun;
use App\Models\StopVideoReview;
use App\Models\DcnTransaction;
use App\Models\VoxCronjobLang;
use App\Models\ScrapeDentist;
use App\Models\UserHistory;
use App\Models\VoxQuestion;
use App\Models\LeadMagnet;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\CronjobRun;
use App\Models\VoxAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\BanAppeal;
use App\Models\GasPrice;
use App\Models\VoxError;
use App\Models\Country;
use App\Models\Review;
use App\Models\Reward;
use App\Models\User;
use App\Models\Poll;
use App\Models\Dcn;
use App\Models\Vox;

use App\Helpers\GeneralHelper;
use WebPConvert\WebPConvert;
use App\Helpers\VoxHelper;
use Carbon\Carbon;

use Mail;
use DB;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {

        $schedule->call(function () {

            echo 'Incomplete Dentist Registrations cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $notifications = [];

            $notifications['trp'] = [
                [
                    'time' => Carbon::now(),
                    'tempalte_id' => 3,
                ],
                [
                    'time' => Carbon::now()->addDays(-1),
                    'tempalte_id' => 5,
                ],
                [
                    'time' => Carbon::now()->addDays(-3),
                    'tempalte_id' => 41,
                ]
            ];

            $notifications['vox'][] = [
                'time' => Carbon::now()->addDays(-1),
                'tempalte_id' => 94,
            ];

            $notifications['assurance'][] = [
                'time' => Carbon::now()->addDays(-1),
                'tempalte_id' => 98,
            ];

            $notifications['dentacoin'] = [
                [
                    'time' => Carbon::now(),
                    'tempalte_id' => 95,
                ],
                [
                    'time' => Carbon::now()->addDays(-1),
                    'tempalte_id' => 96,
                ],
                [
                    'time' => Carbon::now()->addDays(-3),
                    'tempalte_id' => 97,
                ]
            ];

            $notifications['dentists'] = [
                [
                    'time' => Carbon::now(),
                    'tempalte_id' => 99,
                ],
                [
                    'time' => Carbon::now()->addDays(-1),
                    'tempalte_id' => 100,
                ],
                [
                    'time' => Carbon::now()->addDays(-3),
                    'tempalte_id' => 101,
                ]
            ];

            foreach ($notifications as $platform => $value) {
                $i = 0;
                foreach ($value as $k => $v) {
                    $i++;
                    if($platform == 'vox' || $platform == 'assurance') {
                        $field = 'notified2';
                    } else {
                        $field = 'notified'.$i;
                    }

                    $list = IncompleteRegistration::whereNull('completed')
                    ->whereNull( $field )
                    ->whereNotNull( 'platform' )
                    ->where('platform', $platform)
                    ->where('created_at', '<', $v['time'])
                    ->get();

                    if(!empty($list)) {
                        foreach ($list as $notify) {

                            if (!empty($notify->email) && filter_var($notify->email, FILTER_VALIDATE_EMAIL)) {

                                $user = User::where('email', 'LIKE', $notify->email)->first();

                                if(empty($user)) {

                                    echo 'USER: '.$notify->id;
                                    $u = User::find(113928);

                                    echo 'Sending '.$field.' to '.$notify->name.' / '.$notify->email.PHP_EOL;

                                    $missingInfo = '';

                                    if(!empty($notify->address)) {
                                        $missingInfo .= 'Select the areas of specialty, upload a logo or photo of your team, and click complete.';
                                    } else {
                                        $missingInfo .= 'Fill in the contact info about your practice, select the areas of specialty, upload a logo or photo of your team, and click complete.';
                                    }

                                    $active_voxes_count = Vox::where('type', '!=', 'hidden')->count();

                                    $content = [];

                                    if($platform == 'trp') {
                                        $content['trp-signup-continue'] = 'https://reviews.dentacoin.com/?temp-data-key='.md5($notify->id.env('SALT_INVITE')).'&temp-data-id='.$notify->id;
                                    } else if($platform == 'vox') {
                                        $content['vox-signup-continue'] = 'https://dentavox.dentacoin.com/?temp-data-key='.md5($notify->id.env('SALT_INVITE')).'&temp-data-id='.$notify->id;
                                    } else if($platform == 'assurance') {
                                        $content['assurance-signup-continue'] = 'https://assurance.dentacoin.com/?temp-data-key='.md5($notify->id.env('SALT_INVITE')).'&temp-data-id='.$notify->id;
                                    } else if($platform == 'dentacoin') {
                                        $content['dcn-signup-continue'] = 'https://dentacoin.com/?temp-data-key='.md5($notify->id.env('SALT_INVITE')).'&temp-data-id='.$notify->id;
                                    } else if($platform == 'dentists') {
                                        $content['dentists-signup-continue'] = 'https://dentists.dentacoin.com/?temp-data-key='.md5($notify->id.env('SALT_INVITE')).'&temp-data-id='.$notify->id;
                                    }

                                    $content['missing-info'] = $missingInfo;
                                    $content['active-surveys'] = $active_voxes_count;

                                    $unsubscribed = User::isUnsubscribedAnonymous($v['tempalte_id'], 'trp', $notify->email);
                                    $mail = GeneralHelper::unregisteredSendGridTemplate($u, $notify->email, $notify->name, $v['tempalte_id'], $content, $platform, $unsubscribed, $notify->email);

                                    $notify->$field = true;
                                    $notify->save();

                                    $mail->delete();
                                }
                            }
                        }
                    }
                }
            }

            echo 'Incomplete Dentist Registrations cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("*/15 * * * *"); //every 15 min

        $schedule->call(function () {

            echo 'Dentist Invite Patient For Review'.PHP_EOL.PHP_EOL.PHP_EOL;

            $notificaitons[] = [
                'time' => Carbon::now()->addDays(-2),
                'tempalte_id' => 72,
            ];
            $notificaitons[] = [
                'time' => Carbon::now()->addDays(-4),
                'tempalte_id' => 73,
            ];
            $notificaitons[] = [
                'time' => Carbon::now()->addDays(-7),
                'tempalte_id' => 74,
            ];

            foreach ($notificaitons as $key => $time) {

                $field = 'notified'.(intval($key)+1);

                $list = UserInvite::whereNull('completed')
                ->whereNotNull('review')
                ->whereNull( $field )
                ->where('created_at', '<', $time['time'])
                ->get();

                foreach ($list as $notify) {
                    if (!empty($notify->email) && filter_var($notify->email, FILTER_VALIDATE_EMAIL)) {

                        echo 'USER: '.$notify;
                        echo 'Sending '.$field.' to '.$notify->name.' / '.$notify->email.PHP_EOL;

                        $user = User::find(113928);
                        $unsubscribed = User::isUnsubscribedAnonymous($time['tempalte_id'], 'trp', $notify->email);
                        $mail = GeneralHelper::unregisteredSendGridTemplate($user, $notify->email, $notify->name, $time['tempalte_id'], null, 'trp', $unsubscribed, $notify->email);

                        $notify->$field = true;
                        $notify->save();

                        $mail->delete();
                    }
                }
            }

            echo 'Dentist Invite Patient For Review cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
        })->cron("*/5 * * * *"); //every 5 min

        
        $schedule->call(function () {
            echo 'DCN Prices cron - Start'.PHP_EOL.PHP_EOL.PHP_EOL;

            $price = null;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => "https://api.coingecko.com/api/v3/coins/dentacoin",
                CURLOPT_SSL_VERIFYPEER => 0
            ));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);
            if(!empty($resp))   {
                if(!empty($resp->market_data->current_price->usd))  {
                   $price = floatval($resp->market_data->current_price->usd);
                }
            }

            // $curl = curl_init();
            // curl_setopt_array($curl, array(
            //     CURLOPT_RETURNTRANSFER => 1,
            //     CURLOPT_URL => 'https://indacoin.com/api/GetCoinConvertAmount/USD/DCN/100/dentacoin',
            //     CURLOPT_SSL_VERIFYPEER => 0
            // ));
            // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            // $resp = json_decode(curl_exec($curl));
            // curl_close($curl);

            // if(!empty($resp))   {
            //     $price = 1 / (int)((int)$resp / 100);
            // }

            if(!empty($price)) {
                file_put_contents('/tmp/dcn_original_price', sprintf('%.10F',$price));

                if($price < 0.00001) {
                    $price = 0.00001;
                }

                file_put_contents('/tmp/dcn_price', sprintf('%.10F',$price));

                DB::table('voxes')
                ->where('reward_usd', '>', 0)
                ->update([
                    'reward' =>  DB::raw( 'CEIL(`reward_usd` / '.$price.')' )
                ]);

                DB::table('rewards')
                ->update([
                    'dcn' =>  DB::raw( 'CEIL(`amount` / '.$price.')' )
                ]);
            }

            echo 'DCN Prices cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("* * * * *"); //every minute


        $schedule->call(function () {
            echo '
PENDING TRANSACTIONS

========================

';

            $transactions = DcnTransaction::where('status', 'pending')
            ->whereNotNull('tx_hash')
            ->where('cronjob_unconfirmed', 0)
            ->where('processing', 0)
            ->orderBy('id', 'asc')
            ->take(50)
            ->get(); //

            if(empty($transactions)) {
                $transactions = DcnTransaction::where('status', 'pending')
                ->whereNotNull('tx_hash')
                ->where('processing', 0)
                ->orderBy('id', 'asc')
                ->take(50)
                ->get(); //

                if($transactions->isNotEmpty()) {
                    foreach ($transactions as $trans) {
                        $trans->cronjob_unconfirmed = 0;
                        $trans->save();
                    }
                }
            }

            if($transactions->isNotEmpty()) {
                $int = 0;
                foreach ($transactions as $trans) {
                    $int++;

                    $transactionIsCompleted = false;

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
                                if(!empty($curl['result']['status'])) {
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

                            if(isset($resp->success) && isset($resp->status) && $resp->status == 'success') {
                                $transactionIsCompleted = true;
                            }
                        }
                    }

                    if(!empty($transactionIsCompleted)) {
                        $trans->status = 'completed';
                        $trans->cronjob_unconfirmed = 0;
                        $trans->save();

                        $dcn_history = new DcnTransactionHistory;
                        $dcn_history->transaction_id = $trans->id;
                        $dcn_history->status = 'completed';
                        $dcn_history->save();

                        if( $trans->user && !empty($trans->user->email) ) {
                            $params = [
                                'transaction_amount' => $trans->amount,
                                'transaction_address' => $trans->address,
                                'transaction_link' => config('transaction-links')[$trans->layer_type].$trans->tx_hash
                            ];

                            if($trans->for_staking) {
                                $trans->user->sendGridTemplate(141, $params, 'dentacoin');
                            } else {
                                $trans->user->sendTemplate( 20, $params, $trans->type=='vox' ? 'vox' : ( $trans->type=='trp' ? 'trp' : 'dentacoin') );
                            }
                        }

                        echo 'COMPLETED!'.PHP_EOL;
                        if($int % 5 == 0) {
                            sleep(1);
                        }
                    }
                }
            }
        })->cron("*/30 * * * *");


        $schedule->call(function () {

            $cron_running = CronjobRun::first();
            //!!!!!new and not_sent transactions must be in one schedule, because of the manual nonces!!!!!!

            if(empty($cron_running) || (!empty($cron_running) && Carbon::now()->addHours(-1) > $cron_running->started_at )) {

                if(!empty($cron_running)) {
                    CronjobRun::destroy($cron_running->id);
                }

                $cronjob_starts = new CronjobRun;
                $cronjob_starts->started_at = Carbon::now();
                $cronjob_starts->save();

                echo '
NEW & NOT SENT TRANSACTIONS

=========================

';
                $number = 30; //always has to be %2
                $half_number = $number/2;
                $cronjobMinutes = 5;

                $count_new_trans = DcnTransaction::where('status', 'new')
                ->where('for_staking', 0)
                ->whereNull('is_paid_by_the_user')
                ->where('processing', 0)
                ->count();

                $count_not_sent_trans = DcnTransaction::where('status', 'not_sent')
                ->where('for_staking', 0)
                ->whereNull('is_paid_by_the_user')
                ->where('processing', 0)
                ->count();

                if(empty($count_not_sent_trans )) {
                    $count_new_trans = $number;
                } else if(empty($count_new_trans)) {
                    $count_not_sent_trans = $number;
                } else {
                    if($count_not_sent_trans >= $half_number && $count_new_trans >= $half_number) {
                        $count_not_sent_trans = $half_number;
                        $count_new_trans = $half_number;

                    } else if($count_not_sent_trans < $half_number && $count_new_trans >= $half_number) {
                        $count_new_trans = $number - $count_not_sent_trans;

                    } else if($count_not_sent_trans >= $half_number && $count_new_trans < $half_number) {
                        $count_not_sent_trans = $number - $count_new_trans;
                    }
                }

                $new_transactions = DcnTransaction::where('status', 'new')
                ->where('for_staking', 0)
                ->whereNull('is_paid_by_the_user')
                ->where('processing', 0)
                ->orderBy('id', 'asc')
                ->take($count_new_trans)
                ->get(); //

                $not_sent_transactions = DcnTransaction::where('status', 'not_sent')
                ->where('for_staking', 0)
                ->whereNull('is_paid_by_the_user')
                ->where('processing', 0)
                ->orderBy('id', 'asc')
                ->take($count_not_sent_trans)
                ->get();

                $transactions = $new_transactions->concat($not_sent_transactions);
                
                if($transactions->isNotEmpty()) {
                    
                    $cron_new_trans_time = GasPrice::find(1); // 2021-02-16 13:43:00

                    if ($cron_new_trans_time->cron_new_trans < Carbon::now()->subMinutes($cronjobMinutes)) {
                        if (!GeneralHelper::isGasExpensive()) {

                            foreach ($transactions as $trans) {
                                $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT) . ': ' . str_pad($trans->amount, 10, ' ', STR_PAD_LEFT) . ' DCN ' . str_pad($trans->status, 15, ' ', STR_PAD_LEFT) . ' -> ' . $trans->address . ' || ' . $trans->tx_hash;
                                echo $log . PHP_EOL;
                            }

                            Dcn::retry($transactions);

                            foreach ($transactions as $trans) {
                                echo 'NEW STATUS: ' . $trans->status . ' / ID ' . $trans->id . ' / ' . $trans->message . ' ' . $trans->tx_hash . PHP_EOL;
                            }

                            $cron_new_trans_time->cron_new_trans = Carbon::now();
                            $cron_new_trans_time->save();
                        } else {
                            $cron_new_trans_time->cron_new_trans = Carbon::now()->subMinutes($cronjobMinutes);
                            $cron_new_trans_time->save();

                            echo 'New Transactions High Gas Price';
                        }
                    }
                }

                echo 'Transactions cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

                CronjobRun::destroy($cronjob_starts->id);
            } else {
                echo 'New transactions cron - skipped!'.PHP_EOL.PHP_EOL.PHP_EOL;
            }

        })->cron("* * * * *");


        $schedule->call(function () {

            $cron_running = CronjobSecondRun::first();

            if(empty($cron_running) || (!empty($cron_running) && Carbon::now()->addHours(-1) > $cron_running->started_at )) {

                if(!empty($cron_running)) {
                    CronjobSecondRun::destroy($cron_running->id);
                }

                $cronjob_starts = new CronjobSecondRun;
                $cronjob_starts->started_at = Carbon::now();
                $cronjob_starts->save();

                echo '
STAKING TRANSACTIONS

=========================

';

                $transactions = DcnTransaction::where('status', 'new')
                ->where('for_staking', 1)
                ->whereNull('is_paid_by_the_user')
                ->where('processing', 0)
                ->orderBy('id', 'asc')
                ->take(30)
                ->get(); 
                
                if($transactions->isNotEmpty()) {
                    if (!GeneralHelper::isStakingGasExpensive()) {
                        foreach ($transactions as $trans) {
                            $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT) . ': ' . str_pad($trans->amount, 10, ' ', STR_PAD_LEFT) . ' DCN ' . str_pad($trans->status, 15, ' ', STR_PAD_LEFT) . ' -> ' . $trans->address . ' || ' . $trans->tx_hash;
                            echo $log . PHP_EOL;
                        }

                        Dcn::staking($transactions);

                        foreach ($transactions as $trans) {
                            echo 'NEW STATUS: ' . $trans->status . ' / ID ' . $trans->id . ' / ' . $trans->message . ' ' . $trans->tx_hash . PHP_EOL;
                        }
                    } else {
                        echo 'Staking Transactions High Gas Price';
                    }
                }

                echo 'Staking Transactions cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

                CronjobSecondRun::destroy($cronjob_starts->id);
            } else {
                echo 'Staking transactions cron - skipped!'.PHP_EOL.PHP_EOL.PHP_EOL;
            }

        })->cron("*/5 * * * *");


        $schedule->call(function () {

            $cron_running = CronjobThirdRun::first();

            if(empty($cron_running) || (!empty($cron_running) && Carbon::now()->addHours(-1) > $cron_running->started_at )) {

                if(!empty($cron_running)) {
                    CronjobThirdRun::destroy($cron_running->id);
                }

                $cronjob_starts = new CronjobThirdRun;
                $cronjob_starts->started_at = Carbon::now();
                $cronjob_starts->save();

                echo '
UNCONFIRMED TRANSACTIONS

========================

';

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
                                $params = [
                                    'transaction_amount' => $trans->amount,
                                    'transaction_address' => $trans->address,
                                    'transaction_link' => config('transaction-links')[$trans->layer_type].$trans->tx_hash
                                ];

                                if($trans->for_staking) {
                                    $trans->user->sendGridTemplate(141, $params, 'dentacoin');
                                } else {
                                    $trans->user->sendTemplate( 20, $params, $trans->type=='vox' ? 'vox' : ( $trans->type=='trp' ? 'trp' : 'dentacoin') );
                                }
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

                CronjobThirdRun::destroy($cronjob_starts->id);
            } else {
                echo 'Unconfirmed transactions cron - skipped!'.PHP_EOL.PHP_EOL.PHP_EOL;
            }

        })->cron("*/10 * * * *");


        $schedule->call(function () {
            echo 'Suspicious Dentist Delete Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $users = User::where('is_dentist', '1')
            ->where('status', 'pending')
            ->where('updated_at', '<', Carbon::now()
            ->subDays(7) )
            ->get();

            if ($users->isNotEmpty()) {
                $userNames = [];

                foreach ($users as $user) {
                    $userNames[] = $user->getNames();

                    $user_history = new UserHistory;
                    $user_history->user_id = $user->id;
                    $user_history->status = $user->status;
                    $user_history->save();

                    $user->status='rejected';
                    $user->save();

                    $action = new UserAction;
                    $action->user_id = $user->id;
                    $action->action = 'deleted';
                    $action->reason = 'Automatically - Dentist with status suspicious over a week';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $user->deleteActions();
                    User::destroy( $user->id );
                }
            }

            echo 'Suspicious Dentist Delete Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron("30 7 * * *"); //10:30h BG Time


        $schedule->call(function () {
            echo 'Suspicious Patients Delete Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $users = User::whereIn('patient_status', ['suspicious_admin', 'suspicious_badip'])
            ->where('updated_at', '<', Carbon::now()->subDays(30) )
            ->doesnthave('newBanAppeal')
            ->get();

            if ($users->isNotEmpty()) {

                foreach ($users as $user) {
                    $action = new UserAction;
                    $action->user_id = $user->id;
                    $action->action = 'deleted';
                    $action->reason = 'Automatically - Patient with status suspicious over a month';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $user->deleteActions();
                    User::destroy( $user->id );
                }
            }

            echo 'Suspicious Patients Delete Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->dailyAt('13:00');


        $schedule->call(function () {
            echo 'Delete pending ban appeals Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $pendingBanAppeals = BanAppeal::whereNotNull('pending_fields')
            ->where('updated_at', '<', Carbon::now()->subDays(14) )
            ->get();

            if($pendingBanAppeals->isNotEmpty()) {
                foreach ($pendingBanAppeals as $item) {
                    $user = $item->user;

                    if($user->patient_status != 'deleted') {
                        $user_history = new UserHistory;
                        $user_history->user_id = $user->id;
                        $user_history->patient_status = $user->patient_status;
                        $user_history->save();

                        $user->patient_status = 'deleted';
                        $user->save();

                        $action = new UserAction;
                        $action->user_id = $user->id;
                        $action->action = 'deleted';
                        $action->reason = 'Pending ban appeal - automatically rejected after 14 days';
                        $action->actioned_at = Carbon::now();
                        $action->save();

                        $user->sendTemplate(9, null, 'dentacoin');

                        $user->deleteActions();
                        User::destroy( $user->id );
                    }

                    $item->status = 'rejected';
                    $item->pending_fields = null;
                    $item->save();
                }
            }

            echo 'Delete pending ban appeals Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("30 7 * * *"); //10:30h BG Time


        $schedule->call(function () {
            echo 'First 3 weeks engagement email 2 START'.PHP_EOL.PHP_EOL.PHP_EOL;

            //First 3 weeks engagement      

            //Email 2
            $query = "
                SELECT 
                    * 
                FROM 
                    emails 
                WHERE 
                    template_id = 26
                    AND `user_id` NOT IN ( 
                        SELECT 
                            `user_id`
                        FROM 
                            emails 
                        WHERE template_id = 44
                    )
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null 
                            AND `platform` = 'trp'
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*4)." 00:00:00' 
                    AND `created_at` > '".date('Y-m-d', time() - 86400*7)." 00:00:00'
                    AND `deleted_at` is null
                GROUP BY 
                    `user_id`
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {                
                    $user = User::find($e->user_id);
                    if (!empty($user)) {
                        $user->sendGridTemplate(44, null, 'trp');
                    }
                }
            }

            echo 'First 3 weeks engagement email 2 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

            //Email 3
            $query = "
                SELECT 
                    * 
                FROM 
                    emails 
                WHERE 
                    template_id = 44
                    AND `user_id` NOT IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE template_id = 45
                    )
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null 
                            AND `platform` = 'trp'
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*3)." 00:00:00' 
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {
                    $user = User::find($e->user_id);

                    if (!empty($user)) {
                        $missingInfo = [];

                        if(empty($user->short_description)) {
                            $missingInfo[] = 'a short intro';
                        }

                        if(empty($user->work_hours)) {
                            $missingInfo[] = 'opening hours';
                        }

                        if(empty($user->socials)) {
                            $missingInfo[] = 'social media pages';
                        }

                        if(empty($user->description)) {
                            $missingInfo[] = 'a description';
                        }

                        if($user->photos->isEmpty()) {
                            $missingInfo[] = 'more photos';
                        }

                        if (!empty($missingInfo)) {

                            $substitutions = [
                                'profile_missing_info' => $missingInfo[0],
                            ];

                            $user->sendGridTemplate(45, $substitutions, 'trp');
                        } else {
                            $user->sendGridTemplate(45, null, 'trp', 1);
                        }
                    }           
                }
            }
            echo 'First 3 weeks engagement email 3 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;


            //Email 4
            $query = "
                SELECT 
                    * 
                FROM 
                    emails 
                WHERE 
                    template_id = 45
                    AND `user_id` NOT IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE template_id IN ( 46, 47)
                    )
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null 
                            AND `platform` = 'trp'
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*4)." 00:00:00'
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {
                    $user = User::find($e->user_id);
                    if (!empty($user) && $user->invites->isNotEmpty()) {
                        $user->sendGridTemplate(46, null, 'trp');
                    } else {
                        $user->sendGridTemplate(47, null, 'trp');
                    }       
                }
            }
            echo 'First 3 weeks engagement email 4 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;
            

            //Email 5
            $query = "
                SELECT 
                    * 
                FROM 
                    emails 
                WHERE 
                    template_id IN ( 46, 47)
                    AND `user_id` NOT IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE template_id = 48
                    )                    
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null 
                            AND `platform` = 'trp'
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*10)." 00:00:00'
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {
                    $user = User::find($e->user_id);
                    if (!empty($user) && $user->reviews_in()->isNotEmpty()) {

                        $substitutions = [
                            'score_last_month_aver' => number_format($user->avg_rating,2),
                            'reviews_last_month_num' => $user->reviews_in()->count().($user->reviews_in()->count() > 1 ? ' reviews' : ' review'),
                        ];

                        $user->sendGridTemplate(48, $substitutions, 'trp');
                    }
                }
            }
            echo 'First 3 weeks engagement email 5 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

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
                        AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed')
                        AND `self_deleted` is null
                        AND `id` NOT IN ( 
                            SELECT `user_id` FROM wallet_addresses
                        )
                        AND (rewards_total - IF (withdraws_total IS NULL, 0,withdraws_total) ) > ".WithdrawalsCondition::find(1)->min_vox_amount."
                        AND `deleted_at` is null
                        AND `id` NOT IN ( 
                            SELECT 
                                `user_id`
                            FROM 
                                emails 
                            WHERE 
                                template_id = 57 
                                AND `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00'
                        )
                        AND `id` NOT IN ( 
                            SELECT 
                                `user_id` 
                            FROM 
                                emails 
                            WHERE 
                                template_id = 57 
                                AND `created_at` < '".date('Y-m-d', time() - 86400*31*6)." 00:00:00'
                        )
                LIMIT 100
            ";

            $users = DB::select(
                DB::raw($query), []
            );

            if(!empty($users)) {

                foreach ($users as $u) {
                    $user = User::find($u->user_id);

                    if (!empty($user)) {
                        $user->sendGridTemplate(57);
                    }
                }
            }

            echo 'Create Wallet Email DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

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
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE 
                            template_id = 50 
                            AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00'
                    )
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*4)." 00:00:00'
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {
                    $user = User::find($e->user_id);
                    if (!empty($user)) {
                        $user->sendGridTemplate(50, null, 'trp');
                    }
                }
            }
            echo 'No reviews last 30 days Email 2 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

            //Email3

            $query = "
                SELECT 
                    * 
                FROM 
                    emails 
                WHERE 
                    template_id = 50
                    AND `user_id` NOT IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE 
                            template_id IN ( 51, 52) 
                            AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00'
                    )
                    AND `user_id` IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE 
                            template_id = 49 
                            AND `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00'
                    )                    
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*7)." 00:00:00'
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {
                    $user = User::find($e->user_id);
                    if (!empty($user) && $user->invites->isNotEmpty()) {

                        if ( $user->reviews_in()->isNotEmpty()) {
                            $id = $user->id;
                            $from_day = Carbon::now()->subDays(11);

                            $prev_reviews = Review::where(function($query) use ($id) {
                                $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
                            })->where('created_at', '>=', $from_day)
                            ->get();

                            $rating = 0;
                            foreach($prev_reviews as $reviews) {
                                if (!empty($reviews->team_doctor_rating) && ($user->id == $reviews->dentist_id)) {
                                    $rating += $reviews->team_doctor_rating;
                                } else {
                                    $rating += $reviews->rating;
                                }
                            }

                            $rating_avg = !empty($rating) ? $rating / $prev_reviews->count() : 0;
                            $results_sentence = 'Congrats, you are on the right track! In the past weeks you achieved '.number_format($rating_avg, 2).' rating score based on '.$prev_reviews->count().($prev_reviews->count() > 1 ? ' reviews' : ' review').'.';                   
                        } else {
                            $invites_text = $user->invites->count() > 1 ? "invites" : "invite";
                            $results_sentence = 'Congrats, you are on the right track! In the past weeks you sent '.$user->invites->count().' review '.$invites_text.' to your patients.';
                        }

                        $substitutions = [
                            'results_sentence' => $results_sentence
                        ];

                        $user->sendGridTemplate(51, $substitutions, 'trp');
                    } else {
                        $user->sendGridTemplate(52, null, 'trp');
                    }   
                }
            }
            echo 'No reviews last 30 days Email 3 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

            //Email4

            $query = "
                SELECT 
                    * 
                FROM 
                    emails 
                WHERE 
                    template_id = 52
                    AND `user_id` NOT IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE 
                            template_id IN ( 53, 54) 
                            AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00'
                    )
                    AND `user_id` IN ( 
                        SELECT 
                            `user_id` 
                        FROM 
                            emails 
                        WHERE 
                            template_id = 49 
                            AND `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00'
                    )                    
                    AND `user_id` IN ( 
                        SELECT 
                            `id` 
                        FROM 
                            users 
                        WHERE 
                            is_dentist = 1 
                            AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed') 
                            AND `self_deleted` is null
                    )
                    AND `created_at` < '".date('Y-m-d', time() - 86400*14)." 00:00:00'
            ";

            $emails = DB::select(
                DB::raw($query), []
            );

            if (!empty($emails)) {
                foreach ($emails as $e) {
                    $user = User::find($e->user_id);
                    if (!empty($user) && $user->invites->isNotEmpty()) {

                        if ( $user->reviews_in()->isNotEmpty()) {
                            $id = $user->id;
                            $from_day = Carbon::now()->subDays(25);

                            $prev_reviews = Review::where(function($query) use ($id) {
                                $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
                            })->where('created_at', '>=', $from_day)
                            ->get();

                            $rating = 0;
                            foreach($prev_reviews as $reviews) {
                                if (!empty($reviews->team_doctor_rating) && ($user->id == $reviews->dentist_id)) {
                                    $rating += $reviews->team_doctor_rating;
                                } else {
                                    $rating += $reviews->rating;
                                }
                            }

                            $rating_avg = !empty($rating) ? $rating / $prev_reviews->count() : 0;
                            $results_sentence = 'Congrats, you are on the right track! In the past weeks you achieved '.number_format($rating_avg, 2).' rating score based on '.$prev_reviews->count().($prev_reviews->count() > 1 ? ' reviews' : ' review').'.';                   
                        } else {
                            $invites_text = $user->invites->count() > 1 ? "invites" : "invite";
                            $results_sentence = 'Congrats, you are on the right track! In the past weeks you sent '.$user->invites->count().' review '.$invites_text.' to your patients.';
                        }

                        $substitutions = [
                            'results_sentence' => $results_sentence
                        ];

                        $user->sendGridTemplate(53, $substitutions, 'trp');
                    } else {
                        $user->sendGridTemplate(54, null, 'trp');
                    }   
                }
            }
            echo 'No reviews last 30 days Email 4 DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("15 */6 * * *"); //05:00h

        //
        //Monthly score
        //

        $schedule->call(function () {
            echo 'Monthly score Email START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $query = "
                SELECT 
                    `id`
                FROM 
                    users
                WHERE 
                    `is_dentist` = 1
                    AND `created_at` < '".date('Y-m-d', time() - 86400*30)." 00:00:00'
                    AND `deleted_at` is null
                    AND `self_deleted` is null
                    AND `status` IN ('approved', 'test', 'added_by_clinic_claimed','added_by_dentist_claimed')
            ";

            // Cron runs 1x per month
            // AND `id` NOT IN ( SELECT `user_id` FROM `emails` WHERE  `template_id` IN ( 55, 56) AND `created_at` > '".date('Y-m-d', time() - 86400*20)." 00:00:00' )

            $users = DB::select(
                DB::raw($query), []
            );

            if (!empty($users)) {
                foreach ($users as $u) {
                    $user = User::find($u->id);

                    if (!empty($user)) {

                        $found = false;
                        if ($user->reviews_in()->isNotEmpty()) {
                            foreach ($user->reviews_in() as $review) {
                                if ($review->created_at->timestamp > time() - 86400*30 ) {
                                    $found = true;
                                    break;
                                }
                            }
                        }

                        if ($found && $user->getMontlyRating()->count()) {

                            $avg_rating = 0;
                            foreach($user->getMontlyRating() as $cur_month_reviews) {

                                if (!empty($cur_month_reviews->team_doctor_rating) && ($user->id == $cur_month_reviews->dentist_id)) {
                                    $avg_rating += $cur_month_reviews->team_doctor_rating;
                                } else {
                                    $avg_rating += $cur_month_reviews->rating;
                                }
                            }

                            $cur_month_rating = number_format($avg_rating / $user->getMontlyRating()->count(), 2);
                            $cur_month_reviews_num = $user->getMontlyRating()->count();

                            $prev_avg_rating = 0;
                            foreach($user->getMontlyRating(1) as $prev_month_reviews) {
                                if (!empty($prev_month_reviews->team_doctor_rating) && ($user->id == $prev_month_reviews->dentist_id)) {
                                    $prev_avg_rating += $prev_month_reviews->team_doctor_rating;
                                } else {
                                    $prev_avg_rating += $prev_month_reviews->rating;
                                }
                            }

                            $prev_month_rating = !empty($prev_avg_rating) ? $prev_avg_rating / $user->getMontlyRating(1)->count() : 0;
                            $prev_month_reviews_num = $user->getMontlyRating(1)->count();

                            if (!empty($prev_month_rating)) {
                                
                                if ($cur_month_rating < $prev_month_rating) {
                                    $cur_month_rating_percent = intval((($cur_month_rating - $prev_month_rating) / $prev_month_rating) * -100).'%';
                                    $change_month = 'lower than last month';
                                } else if($cur_month_rating > $prev_month_rating) {
                                    $cur_month_rating_percent = intval((($cur_month_rating / $prev_month_rating) - 1) * 100).'%';
                                    $change_month = 'higher than last month';
                                } else {
                                    $cur_month_rating_percent = '';
                                    $change_month = 'the same as last month';
                                }
                            } else {
                                $cur_month_rating_percent = '100%';
                                $change_month = 'higher than last month';
                            }


                            if (!empty($prev_month_reviews_num)) {
                                if ($cur_month_reviews_num < $prev_month_reviews_num) {
                                    $reviews_num_percent_month = intval((($cur_month_reviews_num - $prev_month_reviews_num) / $prev_month_reviews_num) * -100).'%';
                                    $change_month_num = 'lower than last month';

                                } else if($cur_month_reviews_num > $prev_month_reviews_num) {
                                    $reviews_num_percent_month = intval((($cur_month_reviews_num / $prev_month_reviews_num) - 1) * 100).'%';
                                    $change_month_num = 'higher than last month';
                                } else {
                                    $reviews_num_percent_month = '';
                                    $change_month_num = 'the same as last month';
                                }
                            } else {
                                $reviews_num_percent_month = '100%';
                                $change_month_num = 'higher than last month';
                            }


                            //status?
                            $country_id = $user->country->id;
                            $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
                                $query->where('country_id', $country_id);
                            })->get();

                            $country_rating = 0;
                            foreach ($country_reviews as $c_review) {
                                $country_rating += $c_review->rating;
                            }

                            $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

                            if (!empty($avg_country_rating)) {
                                if ($cur_month_rating < $avg_country_rating) {
                                    $cur_country_month_rating_percent = intval((($cur_month_rating - $avg_country_rating) / $avg_country_rating) * -100).'%';
                                    $change_country = 'lower than the average';
                                } else if($cur_month_rating > $avg_country_rating) {
                                    $cur_country_month_rating_percent = intval((($cur_month_rating / $avg_country_rating) - 1) * 100).'%';
                                    $change_country = 'higher than the average';
                                } else {
                                    $cur_country_month_rating_percent = '0%';
                                    $change_country = 'same as average';
                                }
                            } else {
                                $cur_month_rating_percent = '100%';
                                $change_country = 'higher than the average';
                            }


                            // $top3_dentists_query = User::where('is_dentist', 1)->where('status', 'approved')->where('country_id', $user->country_id)->orderby('avg_rating', 'desc')->take(3)->get();

                            // $top3_dentists = [];
                            // foreach ($top3_dentists_query as $top3_dentist) {
                            //  $top3_dentists[] = '<a href="'.$top3_dentist->getLink().'">'.$top3_dentist->getNames().'</a>';
                            // }

                            $user->sendGridTemplate(90, [
                                'score_last_month_aver' => $cur_month_rating,
                                'score_percent_month' => $cur_month_rating_percent,
                                'change_month' => $change_month,
                                'reviews_last_month_num' => $cur_month_reviews_num.($cur_month_reviews_num > 1 ? ' reviews' : ' review'),
                                'score_percent_country' => $cur_country_month_rating_percent,
                                'change_country' => $change_country,
                                'reviews_num_percent_month' => $reviews_num_percent_month,
                                'change_month_num' => $change_month_num,
                                // 'top3-dentists' => implode('<br/>',$top3_dentists)
                            ], 'trp');

                        } else {

                            if($user->country_id) {
                                $country_id = $user->country->id;
                                $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
                                    $query->where('country_id', $country_id);
                                })->get();

                                if ($country_reviews->count()) {
                                    $country_rating = 0;
                                    foreach ($country_reviews as $c_review) {
                                        $country_rating += $c_review->rating;
                                    }

                                    $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

                                    $compare_with_others = 'Other dentists in '.Country::find($user->country_id)->name.' achieved average rating score: '.$avg_country_rating.'. Are you ready to challenge them?';
                                } else {
                                    $compare_with_others = 'Don\'t miss the chance to stand out from other dentists in '.Country::find($user->country_id)->name.' this month! Invite your patients to post a review and boost your monthly performance!';
                                }

                                $month = \Carbon\Carbon::now();

                                $user->sendGridTemplate(91, [
                                    'month' => $month->subMonth()->format('F'),
                                    'compare_with_others' => $compare_with_others,
                                ], 'trp');
                            }
                        }       
                    }
                }
            }
            echo 'Monthly score Email - DONE'.PHP_EOL.PHP_EOL.PHP_EOL;
        })->monthlyOn(1, '12:30');


        //Daily Polls

        $schedule->call(function () {
            echo 'Daily Poll START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $daily_poll = Poll::where('launched_at', date('Y-m-d') )->first();

            if (!empty($daily_poll)) {
                $daily_poll->status = 'open';
                $daily_poll->save();
            }
            echo 'Daily Poll DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->dailyAt('03:00');


        $schedule->call(function () {
            echo 'Scrape dentists scron'.PHP_EOL.PHP_EOL.PHP_EOL;

            $scrapes = ScrapeDentist::whereNull('completed')->orderBy('id', 'desc')->get();

            if ($scrapes->count()) {

                foreach ($scrapes as $scrape) {
                    echo '<br/>';
                    echo '<br/>';
                    echo 'Scraping: '.$scrape->name.'<br/>';
                    $startingRequests = $scrape->requests;
                    $i=0;
                    for($lat = $scrape->lat_start; $lat<$scrape->lat_end; $lat+=$scrape->lat_step) {
                        for($lon = $scrape->lon_start; $lon<$scrape->lon_end; $lon+=$scrape->lon_step) {
                            $i++;
                            if($i<$startingRequests) {
                                continue;
                            }
                            if($i>=$startingRequests+30) {
                                continue;
                            }

                            $latlon = $lat.','.$lon;

                            //echo $i.' -> '.$latlon.'<br/>';

                            $query_dentists = [];
                            $pagetoken = null;

                            do {
                                if($pagetoken) {
                                    sleep(3);
                                }

                                $params = $pagetoken ? [
                                    'pagetoken' => $pagetoken,
                                ] : [
                                    'location'    => $latlon,
                                    'radius' => 1000,
                                    "type"      => "dentist",
                                ];

                                $geores2 = \GoogleMaps::load('nearbysearch')
                                ->setParam ($params)
                                ->get();

                                $geores2 = json_decode($geores2);
                                //dd($geores2);

                                if (!empty($geores2->results)) {
                                    foreach ($geores2->results as $res) {
                                        $query_dentists[] = $res;
                                    }
                                }

                                if(!empty($geores2->next_page_token)) {
                                    $pagetoken = $geores2->next_page_token;
                                } else {
                                    $pagetoken = null;
                                }

                            } while (!empty($pagetoken));

                                
                            $dentists = [];
                            if (!empty($query_dentists)) {

                                foreach ($query_dentists as $key => $dentist) {
                                    $place_info = \GoogleMaps::load('placedetails')
                                    ->setParam ([
                                        'place_id'    => $dentist->place_id,
                                    ])
                                    ->get();

                                    $place_info = json_decode($place_info);
                                    //dd($place_info);
                                    if (!empty($place_info)) {
                                        $dentists[$key] = [
                                            // 'original' => $dentist,
                                            // 'place' => $place_info,
                                            'place_id' => $dentist->place_id,
                                            'name' => $place_info->result->name,
                                        ];

                                        if (!empty($place_info->result->formatted_phone_number)) {
                                            $dentists[$key]['phone'] = $place_info->result->formatted_phone_number;
                                        }

                                        if (!empty($place_info->result->website)) {
                                            $dentists[$key]['website'] = $place_info->result->website;
                                        }

                                        if (!empty($place_info->result->opening_hours)) {
                                            $wh = [];
                                            foreach ($place_info->result->opening_hours->periods as $k => $period) {
                                                if (!empty($period) && !empty($period->open->time) && !empty($period->close->time)) {
                                                    $wh[$k + 1] = [
                                                        substr($period->open->time, 0, 2).':'.substr($period->open->time, 2),
                                                        substr($period->close->time, 0, 2).':'.substr($period->open->time, 2),
                                                    ];
                                                }
                                            }
                                            if ($wh) {
                                                $dentists[$key]['work_hours'] = json_encode($wh, true);
                                            }
                                        }

                                        $country_fields = [
                                            'country',
                                        ];

                                        foreach ($country_fields as $sf) {
                                            if( empty($dentists[$key]['country_name']) ) {
                                                foreach ($place_info->result->address_components as $ac) {
                                                    if( in_array($sf, $ac->types) ) {
                                                        $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                                                        $cname = iconv('ASCII', 'UTF-8', $cname);
                                                        $dentists[$key]['country_name'] = $cname;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                break;
                                            }
                                        }

                                        $dentists[$key]['address'] = $place_info->result->vicinity;
                                        //dd($dentists);
                                    }
                                }
                            }

                            if(!empty($dentists)) {
                                foreach ($dentists as $dentist) {
                                    $existing_result = ScrapeDentistResult::where('scrape_dentists_id', $scrape->id)->where('place_id', $dentist['place_id'])->first();
                                    if (empty($existing_result)) {
                                        $scrape_results = new ScrapeDentistResult;
                                        $scrape_results->scrape_dentists_id = $scrape->id;
                                        $scrape_results->place_id = $dentist['place_id'];
                                        $scrape_results->num = $i;
                                        $scrape_results->data = json_encode($dentist);
                                        $scrape_results->save();
                                    }
                                }
                            }

                            if ($scrape->requests == $scrape->requests_total) {
                                $scrape->completed = true;
                            } else {
                                $scrape->requests++;
                            }
                            $scrape->save();
                        }
                    }
                }
            }

            echo 'Scrape dentists scron DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->everyFiveMinutes();


        $schedule->call(function () {
            echo 'Scrape Dentist Emails Cron Start'.PHP_EOL.PHP_EOL.PHP_EOL;

            $sr = ScrapeDentistResult::whereNull('scrape_email')->orderBy('id', 'desc')->first();

            if (!empty($sr)) {

                $emails = [];
                if (array_key_exists('website', json_decode($sr->data, true))) {
                    $arr = json_decode($sr->data, true);

                    $emails = ScrapeDentistResult::scrapeUrl($arr['website']);

                    $file = @file_get_contents($arr['website'], true);
                    if(!empty($file)) {
                        
                        preg_match_all('#href\="([^"]*)"( [a-zA-Z_\:][a-zA-Z0-9_\:\.-]*\="[^"]*")*>(.*?)#', $file , $websites);

                        $href = [];
                        if ($websites[0]) {
                            foreach ($websites[0] as $ws) {
                                $f = explode('href="', $ws);
                                $l = explode('"', $f[1]);
                                if(filter_var($l[0], FILTER_VALIDATE_URL)) {
                                    $href[] = $l[0];
                                }
                            }
                        }

                        if(!empty($href)) {

                            $formats = [
                                '@',
                                '.jpg',
                                '.jpeg',
                                '.png',
                                '.ico',
                                '.cur',
                                '.gz',
                                '.svg',
                                '.svgz',
                                '.mp4',
                                '.ogg',
                                '.ogv',
                                '.webm',
                                '.htc',
                                '.css',
                                '.js',
                                '.ttf',
                                '.woff',
                                '.svg',
                                '.eot',
                                '.woff2',
                            ];

                            $domain = explode('/', explode('://', $arr['website'])[1])[0];
                            $real_hrefs = [];
                            foreach ($href as $h) {
                                if (!in_array($h, $real_hrefs)) {
                                    if (mb_strpos($h, $domain) !== false) {
                                        $real_hrefs[] = $h;
                                    }
                                    
                                }
                            }

                            foreach ($formats as $format) {
                                foreach ($real_hrefs as $k => $rh) {
                                    if (mb_strpos($rh, $format) !== false) {
                                        unset($real_hrefs[$k]);
                                    }
                                }
                            }
                            
                            foreach ($real_hrefs as $real_href) {
                                $emails_new = ScrapeDentistResult::scrapeUrl($real_href);
                                array_merge( $emails, $emails_new );
                            }
                        }
                    }
                }

                if (!empty($emails)) {
                    $sr->emails = implode(',', $emails);
                }

                $sr->scrape_email = true;
                $sr->save();
            }
            
            echo 'Scrape Dentist Emails Cron END'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->everyMinute();


        $schedule->call(function () {
            echo 'Vox answers count Cron START'.PHP_EOL.PHP_EOL.PHP_EOL;
            
            VoxAnswer::getCount(true);

            echo 'Vox answers count Cron END'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->everyThirtyMinutes();


        $schedule->call(function () {
            echo 'amount reward vox_question CRON START';

            $reward = json_encode(Reward::where('reward_type', 'vox_question')->first());
            file_put_contents('/tmp/reward_vox_question', $reward);

            echo 'amount reward vox_question CRON END';

        })->everyMinute();


        $schedule->call(function () {
            echo 'Self deleted users cron start'.PHP_EOL.PHP_EOL.PHP_EOL;

            $self_deleted_users = User::whereNotNull('self_deleted')
            ->whereNotNull('self_deleted_at')
            ->where('self_deleted_at', '<', Carbon::now()->subDays(90) )
            ->take(100)
            ->get();

            $i=0;
            foreach ($self_deleted_users as $sdu) {
                $i++;

                $sdu->name = 'Anonymous';
                $sdu->slug = '';                
                $sdu->email = 'anonymous'.(mb_substr(microtime(true), 0, 10)+$i);
                $sdu->save();
            }

            echo 'Self deleted users cron end'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->dailyAt('10:00');


        $schedule->call(function () {
            echo 'Find logins country cron start'.PHP_EOL.PHP_EOL.PHP_EOL;

            $logins = UserLogin::whereNull('country')
            ->whereNull('test')
            ->orderBy('id', 'desc')
            ->take(100)
            ->get();

            if ($logins->isNotEmpty()) {
                foreach ($logins as $login) {
                    $login->country = \GeoIP::getLocation($login->ip)->country;
                    $login->test=true;
                    $login->save();
                }
            }

            echo 'Find logins country cron end'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->everyMinute();
        

        $schedule->call(function () {
            echo 'Convert avatar to webp cron start'.PHP_EOL.PHP_EOL.PHP_EOL;

            $users = User::where('hasimage', 1)->whereNull('haswebp')->take(100)->get();

            if ($users->isNotEmpty()) {
                
                foreach ($users as $user) {
                    if (file_exists($user->getImagePath())) {
                        $destination = $user->getImagePath().'.webp';
                        WebPConvert::convert($user->getImagePath(), $destination, []);
                    }
                    
                    if (file_exists($user->getImagePath(true))) {
                        $destination_thumb = $user->getImagePath(true).'.webp';
                        WebPConvert::convert($user->getImagePath(true), $destination_thumb, []);
                    }

                    $user->haswebp = true;
                    $user->save();
                }
            }

            echo 'Convert avatar to webp cron end'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->everyFiveMinutes();


        $schedule->call(function () {
            echo 'Lead Magnet Delete Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $leads = LeadMagnet::where('created_at', '<', Carbon::now()->subDays(30) )->get();

            if ($leads->isNotEmpty()) {

                foreach ($leads as $lead) {
                    LeadMagnet::destroy( $lead->id );
                }
            }

            echo 'Lead Magnet Delete Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron("30 7 * * *"); //10:30h BG Time


        $schedule->call(function () {
            echo 'Dentists with ?? in address Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $users = User::where('is_dentist', 1)->where(function ($query) {
                $query->where('city_name', 'LIKE', '%??%')
                ->orWhere('state_name', 'LIKE', '%??%');
            })->whereNotIn('status', ['rejected', 'test', 'added_by_clinic_rejected', 'added_by_dentist_rejected'])
            ->get();

            $user_links = [];

            if ($users->isNotEmpty()) {
                foreach ($users as $user) {
                    $user_links[] = [
                        'link' => 'https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id,
                        'name' => $user->name,
                    ];
                }
            }

            if (!empty($user_links)) {
                $mtext = 'Dentist with ?? symbols in address.
                
                Link to profiles in CMS:  

                ';

                foreach ($user_links as $ul) {
                    $mtext .= '<a href="'.$ul['link'].'">'.$ul['name'].'</a> , ';
                }

                Mail::send([], [], function ($message) use ($mtext) {
                    $sender = config('mail.from.address');
                    $sender_name = config('mail.from.name');

                    $message->from($sender, $sender_name);
                    $message->to( 'petya.ivanova@dentacoin.com' );
                    $message->to( 'donika.kraeva@dentacoin.com' );
                    $message->subject('Dentist with ?? symbols in address');
                    $message->setBody($mtext, 'text/html'); // for HTML rich messages
                });
            }

            echo 'Dentists with ?? in address Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron("30 7 * * *"); //10:30h BG Time


        $schedule->call(function () {
            echo 'Count Unknown Countries from UserLogin Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $query = "
                SELECT 
                    * 
                FROM 
                    user_logins 
                WHERE 
                    country LIKE 'Unknown'
                GROUP BY 
                    `user_id`
            ";

            $uknown_by_user = DB::select(
                DB::raw($query), []
            );

            $query2 = "
                SELECT 
                    * 
                FROM 
                    user_logins 
                WHERE 
                    country LIKE 'Unknown'
            ";

            $uknown = DB::select(
                DB::raw($query2), []
            );

            $mtext = 'There are '.count($uknown).' results from Unknown country by '.count($uknown_by_user).' users
                
            Link to profiles in CMS:  <br/><br/>

            ';

            if (!empty($uknown_by_user)) {
                foreach ($uknown_by_user as $u) {
                    $user = User::where('id', $u->user_id)->withTrashed()->first();
                    $mtext .= '<a href="https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id.'">'.$user->name.'</a><br/>';
                }
            }

            Mail::send([], [], function ($message) use ($mtext) {
                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( 'petya.ivanova@dentacoin.com' );
                $message->subject('Unknown countries count');
                $message->setBody($mtext, 'text/html'); // for HTML rich messages
            });

            echo 'Count Unknown Countries from UserLogin Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron('0 0 */14 * *'); //10:30h BG Time


        $schedule->call(function () {
            echo 'Remove pdf and png stats files cron'.PHP_EOL.PHP_EOL.PHP_EOL;

            $files = glob(storage_path().'/app/public/pdf/*.pdf');

            foreach ($files as $file) {

                if (Carbon::now()->addDays(-1)->timestamp > filectime($file)) {
                    unlink($file);
                }
            }

            $zips = glob(storage_path().'/app/public/png/*.zip');

            foreach ($zips as $zip) {

                if (Carbon::now()->addDays(-1)->timestamp > filectime($zip)) {
                    unlink($zip);
                }
            }

            $files_png = glob(storage_path().'/app/public/png/*');

            foreach ($files_png as $file_png) {
                
                if (Carbon::now()->addDays(-1)->timestamp > filectime($file_png)) {
                    array_map('unlink', glob($file_png.'/*'));
                    rmdir($file_png);
                }
            }
            
            $files_gdpr = glob(storage_path().'/app/public/gdpr/*');

            foreach ($files_gdpr as $file_gdpr) {
                
                if (Carbon::now()->addDays(-1)->timestamp > filectime($file_gdpr)) {
                    array_map('unlink', glob($file_gdpr.'/*'));
                    if(is_dir($file_gdpr)) {
                        rmdir($file_gdpr);
                    }
                }
            }

            echo 'Remove pdf and png stats files cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron('00 09,19 * * *');


        $schedule->call(function () {

            echo 'Gas Price Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.etherscan.io/api?module=gastracker&action=gasoracle&apikey='.env('ETHERSCAN_API'),
                CURLOPT_SSL_VERIFYPEER => 0,
            ));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);
            if (!empty($resp) && isset($resp->result->SafeGasPrice)) {

                $gas = GasPrice::find(1);
                $gas->gas_price = intval(number_format($resp->result->SafeGasPrice));
                $gas->save();
            }

            echo 'Gas Price Cron - DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("* * * * *");


        $schedule->call(function () {

            echo 'Max Gas Price Cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            //for normal transactions
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://payment-server-info.dentacoin.com/get-max-gas-price',
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POST => 1,
            ));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!empty($resp) && isset($resp->success)) {
                $gas = GasPrice::find(1);
                $gas->max_gas_price = intval(number_format($resp->success / 1000000000));
                $gas->save();
            }
            
            //for staking transactions
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://payment-server-info.dentacoin.com/get-max-gas-price-for-staking',
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POST => 1,
            ));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!empty($resp) && isset($resp->success)) {
                $gas = GasPrice::find(1);
                $gas->max_staking_gas_price = intval(number_format($resp->success / 1000000000));
                $gas->save();
            }

            echo 'Max Gas Price Cron - DONE'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("*/15 * * * *");


        $schedule->call(function () {
            echo 'Video reviews cron - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $video_reviews = Review::where('youtube_id', '!=', '')
            ->where('created_at', '>=', Carbon::now()->addDays(-1))
            ->count();

            if($video_reviews >= 5) {
                echo 'Stop video reviews'.PHP_EOL.PHP_EOL.PHP_EOL;
                
                $stop_video_reviews = StopVideoReview::find(1);
                $stop_video_reviews->stopped = true;
                $stop_video_reviews->save();
            } else {
                $stop_video_reviews = StopVideoReview::find(1);
                $stop_video_reviews->stopped = false;
                $stop_video_reviews->save();
            }

            echo 'Video reviews cron - Done'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron('*/5 * * * *');


        $schedule->call(function () {
            echo 'Transaction scammers by day cron'.PHP_EOL.PHP_EOL.PHP_EOL;

           $min_withdraw_time = WithdrawalsCondition::find(1)->timerange;
           $transactions = DcnTransaction::where('created_at', '>', Carbon::now()->addDays(-30))->groupBy('user_id')->get();

           foreach ($transactions as $trans) {
               $user_transactions = DcnTransaction::where('user_id', $trans->user_id)
               ->where('created_at', '>', Carbon::now()->addDays(-30))
               ->get();

               foreach ($user_transactions as $user_trans) {
                   foreach ($user_transactions as $user_t) {
                       if($user_t->id != $user_trans->id && ($user_t->created_at->diffInDays($user_trans->created_at) < $min_withdraw_time) && empty(TransactionScammersByDay::where('user_id', $user_t->user_id)->first())) {

                           $scammer = new TransactionScammersByDay;
                           $scammer->user_id = $user_t->user_id;
                           $scammer->save();
                       }
                   }
               }
            }

            $transactions = DcnTransaction::where('created_at', '>', Carbon::now()->addDays(-30))
            ->where('status', '!=', 'failed')
            ->groupBy('user_id')
            ->get();

            foreach ($transactions as $trans) {
                $user = User::withTrashed()->find($trans->user_id);
                $isScammer = TransactionScammersByBalance::withTrashed()->where('user_id', $user->id)->first();

                if(!empty($user) && empty($isScammer) && intval(DcnReward::where('user_id', $user->id)->sum('reward')) < intval(DcnTransaction::where('user_id', $user->id)->where('created_at', '>', Carbon::now()->addDays(-30))->where('type', '!=', 'register-reward')->where('status', '!=', 'failed')->sum('amount'))) {
                    $scammer = new TransactionScammersByBalance;
                    $scammer->user_id = $user->id;
                    $scammer->save();
                }
            }

            echo 'Transaction scammers by day - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->dailyAt('04:00');


        $schedule->call(function () {
            echo 'Scheduled surveys '.PHP_EOL.PHP_EOL.PHP_EOL;

            $hidden_voxes = Vox::where('type', 'hidden')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', Carbon::now())
            ->where('scheduled_at', '>', Carbon::now()->addDays(-1) )
            ->get();

            if($hidden_voxes->isNotEmpty()) {

                foreach ($hidden_voxes as $hv) {
                    echo 'Scheduled survey - '.$hv->id.PHP_EOL.PHP_EOL.PHP_EOL;

                    $hv->type = 'normal';
                    $hv->save();
                    $hv->activeVox();
                }
            }

            echo 'Scheduled surveys - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("* * * * *");


        $schedule->call(function () {
            echo 'Empty logs '.PHP_EOL.PHP_EOL.PHP_EOL;

            exec('truncate -s 0 /root/.pm2/logs/civic-web-out.log');
            exec('truncate -s 0 /var/www/html/trp/storage/logs/geoip.log');

            echo 'Empty logs - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("0 0 * * 0"); //At 00:00 on Sunday


        $schedule->call(function () {
            echo 'Remove uploaded files'.PHP_EOL.PHP_EOL.PHP_EOL;

            $supportFiles = glob(storage_path().'/app/private/support-contact/*');

            foreach ($supportFiles as $supportFile) {

                if (Carbon::now()->addMonths(-6)->timestamp > filectime($supportFile)) {
                    unlink($supportFile);
                }
            }

            $banAppealsFiles = glob(storage_path().'/app/private/appeals/*');

            foreach ($banAppealsFiles as $banAppealFile) {

                if (Carbon::now()->addYears(-2)->timestamp > filectime($banAppealFile)) {
                    unlink($banAppealFile);
                }
            }

            echo 'Remove uploaded files cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->dailyAt('10:00');


        $schedule->call(function () {
            echo 'Remove old survey warnings'.PHP_EOL.PHP_EOL.PHP_EOL;

            UserSurveyWarning::withTrashed()->where('created_at', '<', Carbon::now()->addDays(-30)->toDateTimeString() )->forceDelete();

            echo 'Remove old survey warnings cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->dailyAt('10:00');


        $schedule->call(function () {
            echo 'Answered questions count'.PHP_EOL.PHP_EOL.PHP_EOL;

            $answered_questions_count = VoxAnswer::where('created_at', '>=', Carbon::now()->addMonths(-1)->firstOfMonth())
            ->where('created_at', '<=', Carbon::now()->addMonths(-1)->endOfMonth())
            ->count();
            // $answered_questions_count = VoxAnswer::where('created_at', '>=', '2021-09-01 00:00:00')->where('created_at', '<=', '2021-09-30 23:59:59')->count();
            
            $vox_q_count = new VoxQuestionAnswered;
            $vox_q_count->month = Carbon::now()->addMonths(-1)->month;
            $vox_q_count->year = Carbon::now()->addMonths(-1)->year;
            $vox_q_count->count = $answered_questions_count;
            $vox_q_count->save();

            echo 'Answered questions count cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron('00 3 1 * *');


        $schedule->call(function () {
            echo 'Translate surveys'.PHP_EOL.PHP_EOL.PHP_EOL;

            $voxes_for_translation = VoxCronjobLang::whereNull('is_completed')->whereNull('is_processing')->get();

            if($voxes_for_translation->isNotEmpty()) {
                foreach($voxes_for_translation as $vox_trans) {
                    $vox_trans->is_processing = true;
                    $vox_trans->save();

                    VoxHelper::translateSurvey($vox_trans->lang_code, $vox_trans->vox);

                    $vox_trans->is_completed = true;
                    $vox_trans->is_processing = false;
                    $vox_trans->save();
                }
            }

            echo 'Translate surveys cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->everyMinute();


        $schedule->call(function () {
            echo 'Remove user\'s vip access START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $users_with_vip_access = User::withTrashed()
            ->where('vip_access', 1)
            ->whereNotNull('vip_access_until')
            ->where('vip_access_until', '<', Carbon::now())
            ->get();

            if($users_with_vip_access->isNotEmpty()) {
                foreach($users_with_vip_access as $user) {
                    $user->vip_access = false;
                    $user->save();

                    // if(empty($user->deleted_at)) {
                    //     $user->sendGridTemplate(119, null, 'vox');
                    // }
                }
            }

            echo 'Remove user\'s vip access - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron("* * * * *");


        // $schedule->call(function () {
        //     echo 'Activate vip access START'.PHP_EOL.PHP_EOL.PHP_EOL;

        //     $users = User::where( function($query) {
        //         $query->where('vip_access', 0)
        //         ->orWhereNull('vip_access');
        //     })->whereNull('self_deleted')->doesntHave('permanentVoxBan')->where('id', '>=', 30000)->get();

        //     foreach($users as $user) {

        //         $user->vip_access = true;
        //         $user->vip_access_until = Carbon::now()->addDays(7);
        //         $user->save();

        //         $substitutions = [
        //             'valid_until' => date('F d, Y, H:i', strtotime(Carbon::parse($user->vip_access_until)) ).' GMT',
        //             'days' => Carbon::now()->diffInDays($user->vip_access_until),
        //         ];

        //         $user->sendGridTemplate(118, $substitutions, 'vox');
        //     }

        //     echo 'Activate vip access - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        // })->dailyAt('18:10');


        $schedule->call(function () {
            echo 'Voxes Errors Check - START'.PHP_EOL.PHP_EOL.PHP_EOL;

            $voxes = Vox::with('questions')->where('type', 'normal')->get();
            
            $questions_order_bugs = [];
            $errors = [];
            $without_translations = [];
            
            foreach($voxes as $survey) {

                if(empty($survey->translation_langs) && $survey->processingForTranslations->isEmpty()) {
                    $without_translations[] = $survey->id;
                }

                // if there are duplicated questions order
                if($survey->questions->isNotEmpty()) {
                    $count_qs = $survey->questionsCount();

                    for ($i=1; $i <= $count_qs ; $i++) {
                        $voxQuestionsCount = VoxQuestion::with('translations')
                        ->where('vox_id', $survey->id)
                        ->where('order', $i)
                        ->count();

                        if(!empty($voxQuestionsCount)) {
                            if($voxQuestionsCount > 1) {
                                $questions_order_bugs[$survey->id][] = 'Duplicated order number - '.$i.'<br/>';  //diplicated order
                            }
                        } else {
                            $questions_order_bugs[$survey->id][] = 'Missing order number - '.$i.'<br/>';  //missing order
                        }
                    }
                }

                if($survey->has_stats) {
                    if(empty($survey->stats_description)) {
                        $errors[$survey->id][] = 'Missing stats description';
                    }

                    if($survey->stats_questions->isEmpty()) {
                        $errors[$survey->id][] = 'Missing stats questions';
                    } else {

                        foreach ($survey->stats_questions as $stat) {
                            if(empty($stat->stats_title_question) && empty($stat->stats_title) && empty($stat->stats_title_question)) {
                                $errors[$survey->id][] = [
                                    'error' => 'Missing stats <a href="https://dentavox.dentacoin.com/cms/vox/edit/'.$survey->id.'/question/'.$stat->id.'/">question</a> title',
                                ];
                            }
                            if(empty($stat->stats_fields) && $stat->used_for_stats != 'dependency') {
                                $errors[$survey->id][] = 'Missing stats <a href="https://dentavox.dentacoin.com/cms/vox/edit/'.$survey->id.'/question/'.$stat->id.'/">question</a> demographics';
                            }
                        }
                    }
                }
            }

            if(!empty($errors) || !empty($questions_order_bugs) || !empty($without_translations)) {
                if(!empty(VoxError::first())) {
                    VoxError::first()->delete();
                }
                
                $new_errors = new VoxError;
                $new_errors->questions_order_bugs = $questions_order_bugs;
                $new_errors->without_translations = $without_translations;
                $new_errors->errors = $errors;
                $new_errors->save();
            }

            echo 'Voxes Errors Check - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->dailyAt('10:35');

        $schedule->call(function () {
            echo 'TEST CRON END '.date('Y-m-d H:i:s').PHP_EOL.PHP_EOL.PHP_EOL;
            echo 'TEST CRON END Carbon now: '.Carbon::now().PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("* * * * *");
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}