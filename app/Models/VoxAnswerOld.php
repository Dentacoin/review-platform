<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxAnswerOld extends Model {
    
    protected $fillable = [
        'user_id',
        'vox_id',
        'question_id',
        'answer',
        'scale',
        'country_id',
        'gender',
        'marital_status',
        'children',
        'education',
        'employment',
        'age',
        'household_children',
        'job_title',
        'income',
        'is_completed',
        'is_skipped',
        'is_admin'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function question() {
        return $this->hasOne('App\Models\VoxQuestion', 'id', 'question_id');
    }
    
    public function country() {
        return $this->hasOne('App\Models\Country', 'id', 'country_id');
    }
}

?>