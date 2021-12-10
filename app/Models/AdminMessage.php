<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMessage extends Model {

    protected $fillable = [
        'admin_id',
        'message',
        'is_read',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'id', 'admin_id')->withTrashed();
    }
}

?>