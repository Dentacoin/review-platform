<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class UserGuidedTour extends Model {
    
    protected $fillable = [
        'user_id',
        'first_login_trp',
        'login_after_first_review',
        'dcn_assurance',
        'dentacare_app',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'check_stats_on',
        'check_reviews_on',
    ];

	public function user() {
	    return $this->hasOne('App\Models\User', 'id', 'user_id');
	}
}

?>