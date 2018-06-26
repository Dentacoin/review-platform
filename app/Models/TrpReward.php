<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrpReward extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'reward',
        'type',
        'reference_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

?>