<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


use App\Models\User;

class UserTeam extends Model {

	use SoftDeletes;
    
    protected $fillable = [
        'user_id', //The clinic
        'dentist_id', //The dentist
        'approved',
    ];


    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function clinicTeam() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id');
    }
    public function clinicTeamWithTrashed() {
        return $this->hasOne('App\Models\User', 'id', 'dentist_id')->withTrashed();
    }
	public function clinic() {
	    return $this->hasOne('App\Models\User', 'id', 'user_id');
	}
}

?>