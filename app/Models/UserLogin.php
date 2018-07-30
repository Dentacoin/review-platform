<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model {
        
    protected $fillable = [
        'user_id',
        'platform',
        'ip'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
    
}

?>