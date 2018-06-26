<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrpCashout extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'reward',
        'address',
        'tx_hash',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
}

?>