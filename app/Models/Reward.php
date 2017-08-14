<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model {
    
    protected $fillable = [
        'reward_type',
        'amount',
    ];

    public $timestamps = false;
}

?>