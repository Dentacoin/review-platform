<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistRecommendation extends Model {

    protected $fillable = [
        'user_id',
        'dentist_id',
        'friend_email',
        'friend_name',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}

?>