<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\IncompleteRegistration;
use App\Models\Article;
use App\Models\User;
use App\Models\Dcn;
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
                    $u->sendTemplate($time['tempalte_id'], [
                        'link' => $notify->id.'/'.md5($notify->id.env('SALT_INVITE')),
                        'missing-info' => $missingInfo,
                    ]);

                    $u->email = $tmpEmail;
                    $u->name = $tmpName;
                    $u->save();

                    $notify->$field = true;
                    $notify->save();
                }
            }

            echo 'Incomplete Dentist Registrations cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;
        })->cron("*/5 * * * *"); //every 5 min
        
        $schedule->call(function () {
            $price = null;
            //for($i=0;$i<5;$i++) {
            
            $info = @file_get_contents('https://api.coinmarketcap.com/v1/ticker/dentacoin/');
            $p = json_decode($info, true);
            if(!empty($p) && !empty($p[0]['price_usd'])) {
                $price = floatval($p[0]['price_usd']);
                file_put_contents('/tmp/dcn_price', sprintf('%.10F',$price));
            }
            if(!empty($p) && !empty($p[0]['percent_change_24h'])) {
                $pc = floatval($p[0]['percent_change_24h']);
                file_put_contents('/tmp/dcn_change', $pc);
            }
            
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

        $schedule->call(function () {

            $json = [];

            foreach (config('currencies') as $currency) {
                $url = 'https://api.coinmarketcap.com/v1/ticker/dentacoin/?convert='.$currency;
                $info = @file_get_contents($url);
                $p = json_decode($info, true);
                $price = floatval($p[0]['price_'.mb_strtolower($currency)]);
                $json[$currency] = $price;
            }

            file_put_contents('/tmp/dcn_currncies', json_encode($json));

            echo 'Currencies cron - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;

        })->cron("*/10 * * * *"); //05:00h
        
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
                                if( $trans->user ) {
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

                if($trans->shouldRetry()) {
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
                $curl = file_get_contents($data['url']);
                if(!empty($curl)) {
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
                    $nuser->sendTemplate( $nuser->platform=='vox' ? 11 : 39, null, ($nuser->platform=='vox' ? 'vox' : 'trp') ); 
                    $nuser->grace_notified = true;
                    $nuser->save();
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
