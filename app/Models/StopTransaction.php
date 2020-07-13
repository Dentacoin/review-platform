<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StopTransaction extends Model {
    
    protected $fillable = [
        "stopped",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];

}