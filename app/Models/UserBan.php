<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBan extends Model {
    
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'domain',
        'expires',
        'type',
        'ban_for_id',
        'question_id',
        'answer',
        'notified',
    ];

    protected $dates = [
        'expires',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'ban_for_id');
    }

    public function question() {
        return $this->hasOne('App\Models\VoxQuestion', 'id', 'question_id');
    }
}

?>