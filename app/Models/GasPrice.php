<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasPrice extends Model {
    
    protected $fillable = [
        "gas_price",
        "max_gas_price",
        "max_gas_price_approval",
    ];
    
    protected $dates = [
        "cron_new_trans",
        "cron_not_sent_trans",
        'created_at', 
        'updated_at',
    ];

}