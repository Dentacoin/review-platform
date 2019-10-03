<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class DentistClaim extends Model {
        
    protected $fillable = [
        'dentist_id',
        'name',
        'email',
        'phone',
        'password',
        'job',
        'explain_related',
        'status',
        'from_mail'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

	public function user() {
	    return $this->hasOne('App\Models\User', 'id', 'dentist_id');
	}
}

?>