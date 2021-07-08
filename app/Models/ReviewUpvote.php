<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewUpvote extends Model {
    
    protected $fillable = [
        'review_id',
        'user_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function review() {
        return $this->hasOne('App\Models\Review', 'id', 'review_id');
    }
    
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}

?>