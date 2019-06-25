<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//ReverseInvite
class UserAsk extends Model {

    protected $fillable = [
        'user_id',
        'dentist_id',
        'status',
        'on_review',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
    public function dentist() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id')->withTrashed();
    }
}


?>