<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxAnswersDependency extends Model {
    
    protected $fillable = [
        'question_dependency_id',
        'question_id',
        'answer_id',
        'answer',
        'cnt',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}

?>