<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model {
    
    protected $fillable = [
        'user_id',
        'poll_id',
        'answer',
        'country_id',
        'editing',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function poll() {
        return $this->hasOne('App\Models\Poll', 'id', 'poll_id');
    }
    
}

?>