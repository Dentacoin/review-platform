<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DcnTransactionHistory extends Model {
    
    use SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'admin_id',
        'address',
        'tx_hash',
        'allowance_hash',
        'nonce',
        'status',
        'message',
        'history_message',
        'from_creating'
	];
    protected $dates = [
        'send_date',
        'created_at',
    	'updated_at',
        'deleted_at',
    ];

    public function admin() {
        return $this->hasOne('App\Models\Admin', 'id', 'admin_id')->withTrashed();
    }
    public function transaction() {
        return $this->hasOne('App\Models\Order', 'id', 'transaction_id');
    }

}
