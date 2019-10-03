<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeDentistResult extends Model {

    protected $fillable = [
        'scrape_dentists_id',
        'place_id',
        'num',
        'data',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}


?>