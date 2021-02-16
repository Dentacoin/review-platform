<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalsCondition extends Model {
    
    protected $fillable = [
        "min_amount",
        "min_vox_amount",
        "timerange",
        "count_pending_transactions"
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];

}