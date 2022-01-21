<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Helpers\GeneralHelper;

use Carbon\Carbon;

class DcnTransaction extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'amount',
        'address',
        'tx_hash',
        'allowance_hash',
        'nonce',
        'type',
        'layer_type',
        'reference_id',
        'status',
        'hash',
        'message',
        'retries',
        'ip',
        'processing',
        'unconfirmed_retry',
        'is_paid_by_the_user',
        'cronjob_unconfirmed',
    ];

    protected $dates = [
        'notified_at',
        'sended_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public function history() {
        return $this->hasMany('App\Models\DcnTransactionHistory', 'transaction_id', 'id');
    }

    public function user_patient_no_kyc() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->where('is_dentist', 0)->where('civic_kyc', 0)->whereNull('self_deleted')->whereNull('patient_status');
    }

    public function user_patient_with_kyc() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->where('is_dentist', 0)->where('civic_kyc', 1)->whereNull('self_deleted')->where('patient_status', '!=', 'old_verified_no_sc');
    }

    public function shouldRetry() {
        $times = intval($this->retries)+1;
        $period = 7200 + (300*pow(2, $times));
        $period = min(86400, $period);
        return !GeneralHelper::isGasExpensive() && Carbon::now()->diffInMinutes($this->updated_at) > $period;
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