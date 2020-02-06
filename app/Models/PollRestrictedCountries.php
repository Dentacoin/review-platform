<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollRestrictedCountries extends Model {
    
    protected $casts = [
        'users_percentage' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function recalculateUsersPercentage() {

        $respondents_count = PollAnswer::count();
        $respondents_users = PollAnswer::get();

        $arr = [];
        foreach ($respondents_users as $ru) {
            if (!empty($ru->country_id)) {

                if (!isset($arr[$ru->country_id])) {
                    $arr[$ru->country_id] = 0;
                }
                $arr[$ru->country_id] += 1;
            }
        }

        foreach ($arr as $key => $value) {
            $arr[$key] = round((($value / $respondents_count) * 100), 2);
        }

        $this->users_percentage = $arr;
        $this->save();
    }


    public static function isPollRestricted($country_id) {

        $is_restricted = false;

        $rescricted_countries = PollRestrictedCountries::find(1)->users_percentage;

        if(array_key_exists($country_id, $rescricted_countries) && $rescricted_countries[$country_id] >= 20 ) {
        	$is_restricted = true;
        }

        return $is_restricted;
    }
    
}

?>