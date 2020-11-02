<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBan extends Model {
    
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'domain',
        'expires',
        'type',
        'ban_for_id',
        'notified',
    ];

    protected $dates = [
        'expires',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}


?>