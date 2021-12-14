<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxHistory extends Model {

    protected $fillable = [
        'admin_id',
        'vox_id',
        'question_id',
        'info',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'id', 'admin_id')->withTrashed();
    }

    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id')->withTrashed();
    }

    public function question() {
        return $this->hasOne('App\Models\VoxQuestion', 'id', 'question_id');
    }
}

?>