<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeDentist extends Model {

    protected $fillable = [
        'lat_start',
        'lat_end',
        'lon_start',
        'lon_end',
        'lat_step',
        'lon_step',
        'name',
        'completed',
        'requests',
        'requests_total',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}


?>