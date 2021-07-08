<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistPageview extends Model {
        
    protected $fillable = [
        'dentist_id',
        'user_id',
        'ip',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}

?>