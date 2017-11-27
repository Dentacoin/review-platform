<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoxAnswer extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'vox_id',
        'question_id',
        'answer',
        'country_id',
        'is_scam',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}





?>