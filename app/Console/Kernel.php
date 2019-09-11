<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\IncompleteRegistration;
use App\Models\Article;
use App\Models\Email;
use App\Models\User;
use App\Models\Country;
use App\Models\Dcn;
use App\Models\Review;
use App\Models\Poll;
use App\Models\DcnTransaction;
use Carbon\Carbon;
use DB;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Mail;

class Kernel extends ConsoleKernel
{
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
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            

            $notificaitons[] = [
                'time' => Carbon::now()->addHours(-1),
                'tempalte_id' => 3,
            ];
            $notificaitons[] = [
                'time' => Carbon::now()->addDays(-1),
                'tempalte_id' => 5,
            ];
            $notificaitons[] = [
                'time' => Carbon::now()->addDays(-3),
                'tempalte_id' => 41,
            ];
            foreach ($notificaitons as $key => $time) {
                $field = 'notified'.(intval($key)+1);
                $list = IncompleteRegistration::whereNull('completed')->whereNull('unsubscribed')->whereNull( $field )->where('created_at', '<', $time['time'])->get();
                foreach ($list as $notify) {
                    if (!empty($notify->email)) {
                        echo 'USER: '.$notify;
                        echo 'USER EMAIL: '.$notify->email;
                        $u = User::find(3);
                        $tmpEmail = $u->email;
                        $tmpName = $u->name;

                        echo 'Sending '.$field.' to '.$notify->name.' / '.$notify->email.PHP_EOL;

                        $missingInfo = '';
                        if($time['tempalte_id']==3) {
                            if(empty($notify->address)) {
                                $missingInfo .= '<b>Enter your clinic address and webpage or social media page. </b>
Your practice will be easily found by patients looking for a dentist in your area.

';
                            }

                            if(empty($notify->photo)) {
                                $missingInfo .= '<b>Select your specialties</b>
Based on your selection, your profile will show to patients who are searching for a particular type of dental specialist.

<b>Upload your profile photo</b> - e.g. picture of you, the team, the clinic or your logo.
Why include a photo? Profile photo makes your practice more recognizable and easier for patients to remember.';

                            }

                            if(!empty($notify->address) && !empty($notify->photo)) {
                                $missingInfo .= '<b>Create your profile.</b>
Click the check box and confirm the CAPTCHA.

';
                            }
                        }
                        if($time['tempalte_id']==5) {
                            $parts = [];
                            if(empty($notify->address)) {
                                $parts[] = 'dental clinic contact details';
                            }

                            if(empty($notify->photo)) {
                                $parts[] = 'profile photo';
                            }

                            if(!empty( $parts )) {
                                $missingInfo .= 'It looks like last time you didn\'t have at hand your '.implode(' and ', $parts).'.';
                            } else {
                                $missingInfo .= 'It looks like you did not complete only the last step of your registration.';
                            }
                        }

                        $u->email = $notify->email;
                        $u->name = $notify->name;
                        $u->save();
                        $mail = $u->sendTemplate($time['tempalte_id'], [
                            'link' => $notify->id.'/'.md5($notify->id.env('SALT_INVITE')),
                            'missing-info' => $missingInfo,
                        ]);

                        $u->email = $tmpEmail;
                        $u->name = $tmpName;
                        $u->save();

                        $notify->$field = true;
                        $notify->save();

                        $mail->delete();
                    }
                }
            }

            echo 'Incomplete Dentist Registrations cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
        })->cron("*/5 * * * *"); //every 5 min
        
        $schedule->call(function () {
            $price = null;
            //for($i=0;$i<5;$i++) {

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
                   file_put_contents('/tmp/dcn_price', sprintf('%.10F',$price));
                }
            }
            
            // $info = @file_get_contents('https://api.coinmarketcap.com/v1/ticker/dentacoin/');
            // $p = json_decode($info, true);
            // if(!empty($p) && !empty($p[0]['price_usd'])) {
            //     $price = floatval($p[0]['price_usd']);
            //     file_put_contents('/tmp/dcn_price', sprintf('%.10F',$price));
            // }
            
            // if(!empty($p) && !empty($p[0]['percent_change_24h'])) {
            //     $pc = floatval($p[0]['percent_change_24h']);
            //     file_put_contents('/tmp/dcn_change', $pc);
            // }
            
            //     if($i!=4) {
            //         sleep(10);
            //     }                
            // }

            if(!empty($price)) {
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

        })->cron("* * * * *"); //05:00h



        // $schedule->call(function () {

        //     $json = [];

        //     foreach (config('currencies') as $currency) {
        //         $url = 'https://api.coinmarketcap.com/v1/ticker/dentacoin/?convert='.$currency;
        //         $info = @file_get_contents($url);
        //         $p = json_decode($info, true);
        //         $price = floatval($p[0]['price_'.mb_strtolower($currency)]);
        //         $json[$currency] = $price;
        //     }

        //     file_put_contents('/tmp/dcn_currncies', json_encode($json));

        //     echo 'Currencies cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        // })->cron("* * * * *"); //05:00h
        
        $schedule->call(function () {

            echo '
UNCONFIRMED TRANSACTIONS

========================

';

            $transactions = DcnTransaction::where('status', 'unconfirmed')->inRandomOrder()->take(5)->get(); //
            foreach ($transactions as $trans) {
                $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT).': '.str_pad($trans->amount, 10, ' ', STR_PAD_LEFT).' DCN '.str_pad($trans->status, 15, ' ', STR_PAD_LEFT).' -> '.$trans->address.' || '.$trans->tx_hash;
                echo $log.PHP_EOL;

                $found = false;
                if( $trans->tx_hash ) {

                    $curl = file_get_contents('https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$trans->tx_hash.'&apikey='.env('ETHERSCAN_API'));
                    if(!empty($curl)) {
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
                                    ], $trans->type=='vox-cashout' ? 'vox' : 'trp' );
                                }
                                $found = true;
                                echo 'COMPLETED!'.PHP_EOL;
                                sleep(1);
                            }
                        }
                    }

                }

                if(!$found && Carbon::now()->diffInMinutes($trans->updated_at) > 60*24) {
                    Dcn::retry($trans);
                    echo 'RETRYING -> '.$trans->message.' '.$trans->tx_hash.PHP_EOL;
                }
            }


            echo '
NEW & FAILED TRANSACTIONS

=========================

';

            $executed = 0;
            $transactions = DcnTransaction::whereIn('status', ['new', 'failed'])->orderBy('id', 'asc')->take(100)->get(); //
            foreach ($transactions as $trans) {
                $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT).': '.str_pad($trans->amount, 10, ' ', STR_PAD_LEFT).' DCN '.str_pad($trans->status, 15, ' ', STR_PAD_LEFT).' -> '.$trans->address.' || '.$trans->tx_hash;
                echo $log.PHP_EOL;

                if($trans->status=='new' ||  $trans->shouldRetry()) {
                    $executed++;
                    Dcn::retry($trans);
                    echo 'NEW STATUS: '.$trans->status.' / '.$trans->message.' '.$trans->tx_hash.PHP_EOL;
                } else {
                    echo 'TOO EARLY TO RETRY'.PHP_EOL;
                }

                if($executed>5) {
                    echo '5 executed - enough for now'.PHP_EOL;
                    break;
                }
            }

            echo 'Transactions cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
        })->cron("* * * * *");


        $schedule->call(function () {

            $alerts = [
                [
                    'currency' => 'DCN',
                    'address' => '0xfb7442ac247ae842238b3e060cd8a5798c1969e3',
                    'url' => 'https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6&address=0xfb7442ac247ae842238b3e060cd8a5798c1969e3&tag=latest&apikey='.env('ETHERSCAN_API'),
                    'limit' => 200000
                ],
                [
                    'currency' => 'ETH',
                    'address' => '0xfb7442ac247ae842238b3e060cd8a5798c1969e3',
                    'url' => 'https://api.etherscan.io/api?module=account&action=balance&address=0xfb7442ac247ae842238b3e060cd8a5798c1969e3&tag=latest&apikey='.env('ETHERSCAN_API'),
                    'limit' => 250000000000000000
                ],
                [
                    'currency' => 'DCN',
                    'address' => '0xb20c179bb3675d0c1035db98ed6591f6a645df2a',
                    'url' => 'https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6&address=0xb20c179bb3675d0c1035db98ed6591f6a645df2a&tag=latest&apikey='.env('ETHERSCAN_API'),
                    'limit' => 200000
                ],
                [
                    'currency' => 'ETH',
                    'address' => '0xb20c179bb3675d0c1035db98ed6591f6a645df2a',
                    'url' => 'https://api.etherscan.io/api?module=account&action=balance&address=0xb20c179bb3675d0c1035db98ed6591f6a645df2a&tag=latest&apikey='.env('ETHERSCAN_API'),
                    'limit' => 250000000000000000
                ],
                [
                    'currency' => 'DCN',
                    'address' => '0x10714e939fa7b0232de065003cd827fd4e28e5de',
                    'url' => 'https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6&address=0x10714e939fa7b0232de065003cd827fd4e28e5de&tag=latest&apikey='.env('ETHERSCAN_API'),
                    'limit' => 200000
                ],
                [
                    'currency' => 'ETH',
                    'address' => '0x10714e939fa7b0232de065003cd827fd4e28e5de',
                    'url' => 'https://api.etherscan.io/api?module=account&action=balance&address=0x10714e939fa7b0232de065003cd827fd4e28e5de&tag=latest&apikey='.env('ETHERSCAN_API'),
                    'limit' => 250000000000000000
                ],
            ];


            foreach ($alerts as $data) {
                echo ' DATA URL '.$data['url'];
                $curl = file_get_contents($data['url']);
                if(!empty($curl)) {
                    echo ' CURL '.$curl;
                    $curl = json_decode($curl, true);
                    if(!empty(intval($curl['result']))) {
                        if( intval($curl['result']) < $data['limit'] ) { //0.25
                            $currency = $data['currency'];

                            Mail::send('emails.template', [
                                    'user' => User::find(4232),
                                    'content' => 'Address: '.$data['address'].': '.$currency.' balance is running low: '.( intval($curl['result']) / 1000000000000000000 ),
                                    'title' => $currency.' balance is running low',
                                    'subtitle' => '',
                                    'platform' => 'reviews',
                                ], function ($message) use ($currency) {

                                    $sender = config('mail.from.address');
                                    $sender_name = 'Low '.$currency.' Alert';

                                    $message->from($sender, $sender_name);
                                    $message->to( 'official@youpluswe.com' );
                                    $message->cc( [
                                        'jeremias.grenzebach@dentacoin.com', 
                                        'philipp@dentacoin.com', 
                                        'donika.kraeva@dentacoin.com', 
                                        'ludwig.mair@dentacoin.com', 
                                        'stoyan.georgiev@dentaprime.com',
                                        'admin@dentacoin.com'
                                    ] );
                                    //$message->to( 'dokinator@gmail.com' );
                                    $message->replyTo($sender, $sender_name);
                                    $message->subject($currency.' balance is running low');
                            });
                        }
                    }
                }
            }

            //Cron log
            unlink('/tmp/cron');
            echo 'DCN Low Balance Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("30 7 * * *"); //10:30h BG Time



        $schedule->call(function () {

            $notify = User::where( 'grace_end', '>', Carbon::now()->addDays(23) )->whereNull('grace_notified')->get();
            if($notify->isNotEmpty()) {
                foreach ($notify as $nuser) {
                    if (!empty($nuser->email)) {
                        echo 'Grace user email: '.$nuser->email;
                        $nuser->sendTemplate( $nuser->platform=='vox' ? 11 : 39, null, ($nuser->platform=='vox' ? 'vox' : 'trp') ); 
                        $nuser->grace_notified = true;
                        $nuser->save();
                    }
                }
            }

        })->cron("30 10 * * *"); //13:30h BG Time


        $schedule->call(function () {
            $users = User::where('is_dentist', '1')->where('status', 'pending')->where('created_at', '<', Carbon::now()->subDays(7) )->get();

            if (count($users)) {
                $userNames = [];

                foreach ($users as $user) {
                    $userNames[] = $user->getName();

                    $user->status=='rejected';
                    $user->save();
                    $user->deleteActions();
                    User::destroy( $user->id );
                }


                $mtext = 'We just deleted the following dentists, because they were suspicious for over a week:

                '.implode(', ', $userNames ).'

                ';

                Mail::raw($mtext, function ($message) {

                    $receiver = 'ali.hashem@dentacoin.com';
                    $sender = config('mail.from.address');
                    $sender_name = config('mail.from.name');

                    $message->from($sender, $sender_name);
                    $message->to( $receiver );
                    //$message->to( 'dokinator@gmail.com' );
                    $message->subject('Suspicios dentists deleted');
                });
            }

            echo 'Suspicious Dentist Delete Cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
            
        })->cron("30 7 * * *"); //10:30h BG Time






        $schedule->call(function () {

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

            foreach ($emails as $e) {                
                $user = User::find($e->user_id);
                if (!empty($user)) {
                    $user->sendGridTemplate(44);
                }                
            }

            echo 'First 3 weeks engagement email 2 DONE';
        

            //Email 3
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

                        $user->sendGridTemplate(45, $substitutions);
                    } else {
                        $user->sendGridTemplate(45, null, null, 1);
                    }
                }           
            }
            echo 'First 3 weeks engagement email 3 DONE';


            //Email 4
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

            foreach ($emails as $e) {
                $user = User::find($e->user_id);
                if (!empty($user) && $user->invites->isNotEmpty()) {
                    $user->sendGridTemplate(46);
                } else {
                    $user->sendGridTemplate(47);
                }       
            }
            echo 'First 3 weeks engagement email 4 DONE';
            

            //Email 5
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

            foreach ($emails as $e) {
                $user = User::find($e->user_id);
                if (!empty($user) && $user->reviews_in()->isNotEmpty()) {

                    $substitutions = [
                        'score_last_month_aver' => number_format($user->avg_rating,2),
                        'reviews_last_month_num' => $user->reviews_in()->count().($user->reviews_in()->count() > 1 ? ' reviews' : ' review'),
                    ];

                    $user->sendGridTemplate(48, $substitutions);
                }
            }
            echo 'First 3 weeks engagement email 5 DONE';

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

            foreach ($users as $u) {
                $user = User::find($u->user_id);

                if (!empty($user)) {
                    $user->sendGridTemplate(57);
                }
            }

            echo 'Create Wallet Email DONE';


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

            foreach ($emails as $e) {
                $user = User::find($e->user_id);
                if (!empty($user)) {
                    $user->sendGridTemplate(50);
                }
            }
            echo 'No reviews last 30 days Email 2 DONE';


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

            foreach ($emails as $e) {
                $user = User::find($e->user_id);
                if (!empty($user) && $user->invites->isNotEmpty()) {

                    if ( $user->reviews_in()->isNotEmpty()) {
                        $id = $user->id;
                        $from_day = Carbon::now()->subDays(11);

                        $prev_reviews = Review::where(function($query) use ($id) {
                        $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
                        })
                        ->where('created_at', '>=', $from_day)
                        ->get();


                        $rating = 0;
                        foreach($prev_reviews as $reviews) {
                            $rating += $reviews->rating;
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

                    $user->sendGridTemplate(51, $substitutions);

                } else {
                    $user->sendGridTemplate(52);
                }   
            }
            echo 'No reviews last 30 days Email 3 DONE';




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

            foreach ($emails as $e) {
                $user = User::find($e->user_id);
                if (!empty($user) && $user->invites->isNotEmpty()) {

                    if ( $user->reviews_in()->isNotEmpty()) {
                        $id = $user->id;
                        $from_day = Carbon::now()->subDays(25);

                        $prev_reviews = Review::where(function($query) use ($id) {
                        $query->where( 'dentist_id', $id)->orWhere('clinic_id', $id);
                        })
                        ->where('created_at', '>=', $from_day)
                        ->get();


                        $rating = 0;
                        foreach($prev_reviews as $reviews) {
                            $rating += $reviews->rating;
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

                    $user->sendGridTemplate(53, $substitutions);

                } else {
                    $user->sendGridTemplate(54);
                }   
            }
            echo 'No reviews last 30 days Email 4 DONE';


        })->cron("15 */6 * * *"); //05:00h



        $schedule->call(function () {
            //users with balance over 200,000 DCN

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
                        AND (rewards_total - IF (withdraws_total IS NULL, 0,withdraws_total) ) >= 200000
                        AND `deleted_at` is null

            ";

            $users = DB::select(
                DB::raw($query), []
            );

            $user_links = [];
            foreach ($users as $u) {
                $user = User::find($u->user_id);

                if (!empty($user)) {
                    $user_links[] = 'https://reviews.dentacoin.com/cms/users/edit/'.$user->id;
                }
            }

            if (!empty($user_links)) {
                $mtext = 'Users with balance of 200,000 DCN or more.
                
                Link to profiles in CMS:

                '.implode('
', $user_links ).'

                ';

                Mail::raw($mtext, function ($message) {

                    $sender = config('mail.from.address');
                    $sender_name = config('mail.from.name');

                    $message->from($sender, $sender_name);
                    $message->to( 'petar.stoykov@dentacoin.com' );
                    $message->to( 'donika.kraeva@dentacoin.com' );
                    $message->to( 'gergana@youpluswe.com' );
                    //$message->to( 'dokinator@gmail.com' );
                    $message->subject('Users with high balance');
                });
            }
            echo 'Balance over 200 000 Email 2 DONE';

        })->cron("0 10 * * *"); //05:00h



        //
        //Monthly score
        //


        $schedule->call(function () {

            $query = "
                SELECT 
                    `id`
                FROM 
                    users
                WHERE 
                    `is_dentist` = 1
                    AND `created_at` < '".date('Y-m-d', time() - 86400*30)." 00:00:00'
                    AND `deleted_at` is null
                    AND `unsubscribe` is null

                    AND `status` IN ('approved', 'test')
            ";

            // Cron runs 1x per month
            // AND `id` NOT IN ( SELECT `user_id` FROM `emails` WHERE  `template_id` IN ( 55, 56) AND `created_at` > '".date('Y-m-d', time() - 86400*20)." 00:00:00' )


            $users = DB::select(
                DB::raw($query), []
            );

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
                            $avg_rating += $cur_month_reviews->rating;
                        }

                        $cur_month_rating = number_format($avg_rating / $user->getMontlyRating()->count(), 2);
                        $cur_month_reviews_num = $user->getMontlyRating()->count();

                        $prev_avg_rating = 0;
                        foreach($user->getMontlyRating(1) as $prev_month_reviews) {
                            $prev_avg_rating += $prev_month_reviews->rating;
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
                        //  $top3_dentists[] = '<a href="'.$top3_dentist->getLink().'">'.$top3_dentist->getName().'</a>';
                        // }

                        $user->sendGridTemplate(55, [
                            'score_last_month_aver' => $cur_month_rating,
                            'score_percent_month' => $cur_month_rating_percent,
                            'change_month' => $change_month,
                            'reviews_last_month_num' => $cur_month_reviews_num.($cur_month_reviews_num > 1 ? ' reviews' : ' review'),
                            'score_percent_country' => $cur_country_month_rating_percent,
                            'change_country' => $change_country,
                            'reviews_num_percent_month' => $reviews_num_percent_month,
                            'change_month_num' => $change_month_num,
                            // 'top3-dentists' => implode('<br/>',$top3_dentists)
                        ]);

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

                            $user->sendGridTemplate(56, [
                                'month' => $month->subMonth()->format('F'),
                                'compare_with_others' => $compare_with_others,
                            ]);
                        }
                    }       
                }
            }
            echo 'Monthly score Email  DONE';
        })->monthlyOn(1, '12:30');



        //Daily Polls

        $schedule->call(function () {

            $daily_poll = Poll::where('launched_at', date('Y-m-d') )->first();

            if (!empty($daily_poll)) {
                $daily_poll->status = 'open';
                $daily_poll->save();
            }
            echo 'Daily Poll DONE';

        })->dailyAt('06:00');

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
