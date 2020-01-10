<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//ReverseInvite
class UserAction extends Model {

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'action',
        'reason',
    ];

    protected $dates = [
    	'actioned_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
}


?>