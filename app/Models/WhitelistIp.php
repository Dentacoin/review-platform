<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhitelistIp extends Model {

	use SoftDeletes;
        
    protected $fillable = [
        'ip',
        'for_vpn',
        'comment'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

?>