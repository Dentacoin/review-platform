<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncompleteRegistration extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'email',
        'email_public',
        'password',
        'mode',
        'name',
        'name_alternative',
        'address',
        'phone',
        'website',
        'country_id',
        'clinic_name',
        'clinic_email',
        'dentist_practice',
        'worker_name',
        'working_position',
        'working_position_label',
        'platform',
        'completed',
        'notified1',
        'notified2',
        'notified3',
        'unsubscribed',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}


?>