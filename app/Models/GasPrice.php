<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasPrice extends Model {
    
    protected $fillable = [
        "gas_price",
        "max_gas_price",
        "max_staking_gas_price",
    ];
    
    protected $dates = [
        "cron_new_trans",
        'created_at', 
        'updated_at',
    ];
}