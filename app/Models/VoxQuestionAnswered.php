<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxQuestionAnswered extends Model {

    protected $table = 'vox_question_answered';

    protected $fillable = [
        'month',
        'count',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

?>