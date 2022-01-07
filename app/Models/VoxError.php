<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxError extends Model {

    protected $fillable = [
        'is_read',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'questions_order_bugs' => 'array',
        'without_translations' => 'array',
        'errors' => 'array',
    ];
}

?>