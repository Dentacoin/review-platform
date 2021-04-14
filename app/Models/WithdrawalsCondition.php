<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalsCondition extends Model {
    
    protected $fillable = [
        "min_amount",
        "min_vox_amount",
        "timerange",
        "count_pending_transactions",
        "daily_max_amount",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];

}