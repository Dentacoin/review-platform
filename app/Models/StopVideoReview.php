<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StopVideoReview extends Model {
    
    protected $fillable = [
        "stopped",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];

}