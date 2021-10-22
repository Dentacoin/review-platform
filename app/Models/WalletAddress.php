<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletAddress extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'dcn_address',
        'dcn_address_label',
        'selected_wallet_address',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }
}

?>