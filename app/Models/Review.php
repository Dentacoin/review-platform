<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;
use App\Models\ReviewAnswer;

class Review extends Model {
    
    use SoftDeletes;
    
    
    protected $fillable = [
        'user_id',
        'dentist_id',
        'rating',
        'answer',
        'reply',
        'verified',
        'upvotes',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    
    public function dentist() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id');
    }

    public function answers() {
        return $this->hasMany('App\Models\ReviewAnswer', 'review_id', 'id')->with('question');
    }

    public function upvotes() {
        return $this->hasMany('App\Models\ReviewUpvote', 'review_id', 'id');
    }
}

?>