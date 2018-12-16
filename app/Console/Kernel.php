<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
            try { 
                DB::statement("
                UPDATE 
                    `cities` `c`, 
                    ( 
                        SELECT 
                            `u`.`city_id`, 
                            AVG(`r`.`rating`) as `avg`, 
                            COUNT(`r`.`id`) AS `cnt` 
                        FROM 
                            `reviews` `r`, 
                            `users` `u` 
                        WHERE 
                            `u`.`id`=`r`.`dentist_id` 
                        GROUP BY 
                        `u`.`city_id` 
                    ) `info`
                SET 
                    `c`.`avg_rating` = `info`.`avg`, 
                    `c`.`ratings` = `info`.`cnt` 
                WHERE 
                    `c`.`id` = `info`.`city_id`
                ");
            } catch(\Illuminate\Database\QueryException $ex){ 
              dd($ex->getMessage()); 
            }
            
            
            try { 
                DB::statement("
                UPDATE 
                    `countries` `c`, 
                    ( 
                        SELECT 
                            `u`.`country_id`, 
                            AVG(`r`.`rating`) as `avg`, 
                            COUNT(`r`.`id`) AS `cnt` 
                        FROM 
                            `reviews` `r`, 
                            `users` `u` 
                        WHERE 
                            `u`.`id`=`r`.`dentist_id` 
                        GROUP BY 
                        `u`.`country_id` 
                    ) `info`
                SET 
                    `c`.`avg_rating` = `info`.`avg`, 
                    `c`.`ratings` = `info`.`cnt` 
                WHERE 
                    `c`.`id` = `info`.`country_id`
                ");
            } catch(\Illuminate\Database\QueryException $ex){ 
              dd($ex->getMessage()); 
            }

            echo 'DONE!';
        //})->everyMinute();
        //})->everyFiveMinutes();
        })->hourly();

        
        $schedule->call(function () {
            return;
            SitemapGenerator::create('https://reviews.dentacoin.com')
            ->hasCrawled(function (Url $url) {
                return $url;                
            })->writeToFile(public_path().'/sitemaps/sitemap-reviews.xml');

            SitemapGenerator::create('https://dentavox.dentacoin.com')
            ->hasCrawled(function (Url $url) {
                return $url;                
            })->writeToFile(public_path().'/sitemaps/sitemap-vox.xml');

            echo 'DONE!';
        })->cron("0 5 * * *"); //05:00h
        
        $schedule->call(function () {
            $price = null;
            for($i=0;$i<5;$i++) {
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
                
                if($i!=4) {
                    sleep(10);
                }                
            }

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

            echo 'DONE!';

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

            echo 'DONE!';

        })->cron("*/10 * * * *"); //05:00h
        
        $schedule->call(function () {
            $transactions = DcnTransaction::where('status', '!=', 'completed')->get(); //->take(100)
            foreach ($transactions as $trans) {
                $log = str_pad($trans->id, 6, ' ', STR_PAD_LEFT).': '.str_pad($trans->amount, 10, ' ', STR_PAD_LEFT).' DCN '.str_pad($trans->status, 15, ' ', STR_PAD_LEFT).' -> '.$trans->address.' || '.$trans->tx_hash;

                if($trans->status=='failed' || $trans->status=='new') {
                    if($trans->shouldRetry()) {
                        Dcn::retry($trans);
                        echo $log.'
NEW STATUS: '.$trans->status.' / '.$trans->message.' '.$trans->tx_hash.'
';
                        //sleep(2);
                    }
                } else if($trans->status=='unconfirmed') {
                    $found = false;
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
                                echo $log.'
COMPLETED!
';
                                sleep(1);
                            }
                        }
                    }

                    if(!$found && Carbon::now()->diffInMinutes($trans->updated_at) > 60*24) {
                        Dcn::retry($trans);
                        echo $log.'
NEW STATUS: '.$trans->status.' / '.$trans->message.' '.$trans->tx_hash.'
';
                        //sleep(2);
                    }
                }
            }

            echo 'DONE!';
        })->cron("* * * * *"); //05:00h


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
