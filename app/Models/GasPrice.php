<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasPrice extends Model {
    
    protected $fillable = [
        "gas_price",
        "max_gas_price",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];

}