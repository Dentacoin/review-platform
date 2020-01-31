<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class DentistFbPage extends Model {
        
    protected $fillable = [
        'dentist_id',
        'fb_page',
        'reviews_type',
        'reviews_count',
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