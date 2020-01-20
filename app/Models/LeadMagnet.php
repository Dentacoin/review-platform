<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadMagnet extends Model {
    
    protected $fillable = [
        'email',
        'name',
        'website',
        'country_id',
        'answers',
        'total',
        'review_collection',
        'review_volume',
        'impact',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'answers' => 'array',
    ];
    
}


?>