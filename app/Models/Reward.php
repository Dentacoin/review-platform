<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model {
    
    protected $fillable = [
        'reward_type',
        'amount',
    ];

    public $timestamps = false;

    public static function getReward($type) {
    	return self::where('reward_type', $type)->first()->dcn;
    }
}

?>