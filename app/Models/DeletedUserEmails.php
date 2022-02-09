<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedUserEmails extends Model {
    
    protected $fillable = [
        'user_id',
        'email',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

	public function user() {
	    return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
	}

	public function emailUser() {
	    return $this->hasOne('App\Models\User', 'email', 'email')->withTrashed();
	}
}

?>