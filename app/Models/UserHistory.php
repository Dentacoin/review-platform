<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model {

    public static $fields = [
        'user_id',
        'admin_id',
        'status',
        'patient_status',
        'gender',
        'birthyear',
        'phone',
        'country_id',
        'civic_email',
        'fb_id',
        'history'
    ];

    protected $fillable = [
        'user_id',
        'admin_id',
        'status',
        'patient_status',
        'gender',
        'birthyear',
        'phone',
        'country_id',
        'civic_email',
        'fb_id',
        'history'
	];
    protected $dates = [
        'created_at',
    	'updated_at',
        'deleted_at',
    ];

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'id', 'admin_id')->withTrashed();
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
