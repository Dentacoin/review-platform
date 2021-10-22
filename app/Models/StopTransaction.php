<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StopTransaction extends Model {
    
    protected $fillable = [
        "stopped",
        "show_warning_text",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
    ];
}