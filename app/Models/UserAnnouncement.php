<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnnouncement extends Model {

    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}

?>