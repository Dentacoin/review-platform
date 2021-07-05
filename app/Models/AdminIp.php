<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminIp extends Model {

	use SoftDeletes;
        
    protected $fillable = [
        'ip',
        'comment'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

?>