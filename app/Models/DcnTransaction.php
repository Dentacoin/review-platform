<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DcnTransaction extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'amount',
        'address',
        'tx_hash',
        'type',
        'reference_id',
        'status',
        'message',
        'retries',
        'ip',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public function shouldRetry() {
        $times = intval($this->retries)+1;
        $period = 300*pow(2, $times);
        $period = min(86400, $period);
        return !User::isGasExpensive() && Carbon::now()->diffInMinutes($this->updated_at) > $period;
    }

    public function getReferenceIdAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);            
        }
        return [];
    }
    
    public function setReferenceIdAttribute($value) {
        $this->attributes['reference_id'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['reference_id'] = implode(',', $value);            
        }
    }
}

?>