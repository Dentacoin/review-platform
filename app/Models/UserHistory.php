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
        'history',
    ];

    protected $fillable = [
        'user_id',
        'admin_id',
        'status',
        'new_status',
        'patient_status',
        'new_patient_status',
        'gender',
        'new_gender',
        'birthyear',
        'new_birthyear',
        'phone',
        'new_phone',
        'country_id',
        'new_country_id',
        'civic_email',
        'new_civic_email',
        'fb_id',
        'new_fb_id',
        'history',
        'new_history',
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

    public function country() {
        return $this->hasOne('App\Models\Country', 'id', 'country_id')->with('translations');
    }

    public function newCountry() {
        return $this->hasOne('App\Models\Country', 'id', 'new_country_id')->with('translations');
    }
}