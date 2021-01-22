<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronjobSecondRun extends Model {
    
    protected $fillable = [];
    
    protected $dates = [
        'started_at',
        'created_at',
        'updated_at',
    ];

}