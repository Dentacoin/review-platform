<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenderBalance extends Model {
    
    protected $fillable = [
        "balance",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];

}