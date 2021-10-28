<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAction extends Model {

    protected $fillable = [
        'admin_id',
        'user_id',
        'ban_appeal_id',
        'action',
        'info',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'id', 'admin_id')->withTrashed();
    }
}

?>