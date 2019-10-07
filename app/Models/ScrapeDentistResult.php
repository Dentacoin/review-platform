<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeDentistResult extends Model {

    protected $fillable = [
        'scrape_dentists_id',
        'place_id',
        'num',
        'data',
        'scrape_email',
        'emails',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function scrapeUrl($url) {
        $site_mails = [];
        $site_mails_filtered = [];

        $file = @file_get_contents($url, true);
        if(!empty($file)) {
            preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $file, $matches);
            $site_mails = $matches[0];
            foreach ($site_mails as $email) {
                if(!in_array($email, $site_mails_filtered)) {
                    list($bla, $domain) = explode('@', $email);
                    if( checkdnsrr($domain, 'MX') ) {
                        $site_mails_filtered[] = $email;
                    }
                }
            }
        }

        return $site_mails_filtered;
    }
    
}


?>