<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBan extends Model {

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
    ];

}


?>