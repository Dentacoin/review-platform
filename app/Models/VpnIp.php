<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VpnIp extends Model {
        
    protected $fillable = [
        'ip',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}

?>