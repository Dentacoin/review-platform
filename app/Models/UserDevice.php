<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model {

    use SoftDeletes;
        
    protected $fillable = [
        'user_id',
        'device_token',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
}

?>