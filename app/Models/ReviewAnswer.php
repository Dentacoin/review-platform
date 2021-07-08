<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewAnswer extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'review_id',
        'question_id',
        'options'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function question() {
        return $this->hasOne('App\Models\Question', 'id', 'question_id');
    }
    
    public function review() {
        return $this->hasOne('App\Models\Review', 'id', 'review_id');
    }
}

?>