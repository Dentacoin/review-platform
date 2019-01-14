<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvite extends Model {

    protected $fillable = [
        'user_id',
        'invited_email',
        'invited_name',
        'invited_id',
        'rewarded',
        'join_clinic',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function invited() {
        return $this->hasOne('App\Models\User', 'id', 'invited_id')->withTrashed();
    }
}


?>