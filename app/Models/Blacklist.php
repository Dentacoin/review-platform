<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model {
    
    protected $fillable = [
        'pattern',
        'field',
        'comments'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}

?>