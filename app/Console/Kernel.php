<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Article;
use Carbon\Carbon;
use DB;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

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
