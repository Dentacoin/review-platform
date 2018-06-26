<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


use App\Models\User;

class UserTeam extends Model {
    
    protected $fillable = [
        'user_id', //The clinic
        'dentist_id', //The dentist
        'approved',
    ];

    public $timestamps = false; 

	public function clinicTeam() {
	    return $this->hasOne('App\Models\User', 'id', 'dentist_id');
	}
	public function clinic() {
	    return $this->hasOne('App\Models\User', 'id', 'user_id');
	}
}

?>