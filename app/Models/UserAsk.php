<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//ReverseInvite
class UserAsk extends Model {

    use \Awobaz\Compoships\Compoships;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'dentist_id',
        'status',
        'on_review',
        'hidden',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
    public function dentist() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id')->withTrashed();
    }
}


?>