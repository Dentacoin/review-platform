<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Helpers\GeneralHelper;

use Mail;
// cron('00 10,20 * * *')

class SuspiciousFiles extends Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspicious:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for suspicious uploaded files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        echo 'Check for suspicious uploaded files - START'.PHP_EOL.PHP_EOL.PHP_EOL;
        
        $publicFiles = GeneralHelper::getDirContents(storage_path().'/app/public/');
        $privateFiles = GeneralHelper::getDirContents(storage_path().'/app/private/');

        $files = array_merge($publicFiles, $privateFiles);

        $suspiciousFiles = [];

        foreach($files as $file) {
            if(!empty($file)) {
                $file_mime = exec('file -b --mime-type '.$file);

                // dd($file_mime);
                if(!in_array($file_mime, [
                    'text/html',
                    'inode/x-empty', 
                    'application/zip', 
                    'application/pdf', 
                    'application/octet-stream', 
                    'inode/directory', 
                    'video/x-matroska', 
                    'image/jpeg', 
                    'image/webp', 
                    'image/png', 
                    'video/mp4', 
                    'video/quicktime', 
                    'video/webm'
                ] )) {
                    // if(!in_array($file_mime, ['text/plain', 'text/x-php', 'inode/x-empty',        'text/html',              'application/zip', 'application/pdf', 'application/octet-stream', 'inode/directory', 'video/x-matroska', 'image/jpeg', 'image/webp', 'image/png', 'video/mp4', 'video/quicktime', 'video/webm'] )) {
                    $suspiciousFiles[$file] = ' ( Mime type - '.$file_mime.')';
                    // dd($file, $file_mime);
                }

                $file_name = exec('basename '.$file);

                if (mb_strpos(strtolower($file_name), '.php') !== false) {
                    $suspiciousFiles[$file] = ' extension with .php';
                }
            }
        }

        if(!empty($suspiciousFiles)) {
            $mtext = 'Files:

            ';

            foreach ($suspiciousFiles as $path => $info) {
                $mtext .= $path.$info.'
                ';
            }
            Mail::raw($mtext, function ($message) {
                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');
    
                $message->from($sender, $sender_name);
                $message->to('gergana@youpluswe.com');
                $message->to('miroslav.nedelchev@dentacoin.com');
                $message->subject('Suspicious files uploaded in TRP server');
            });
        }
        

        echo 'Check for suspicious uploaded files - DONE!'.PHP_EOL.PHP_EOL.PHP_EOL;


        //to add more commands - crontab -e
    }
}
