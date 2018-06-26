<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Civic extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'jwtToken',
        'response',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}

?>