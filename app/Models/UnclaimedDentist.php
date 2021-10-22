<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnclaimedDentist extends Model {

    protected $fillable = [
        'user_id',
        'completed',
        'notified1',
        'notified2',
        'notified3',
        'unsubscribed',
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