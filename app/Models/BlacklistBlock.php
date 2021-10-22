<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistBlock extends Model {
    
    protected $fillable = [
        'blacklist_id',
        'name',
        'email'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}

?>