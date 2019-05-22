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
    public function review() {
        return $this->hasOne('App\Models\Review', 'id', 'reference_id');        
    }
    public function invitation() {
        return $this->hasOne('App\Models\UserInvite', 'id', 'reference_id');        
    }
    public function voxCashout() {
        return $this->hasOne('App\Models\DcnCashout', 'id', 'reference_id')->where('platform', 'vox');        
    }
    public function mobident() {
        return $this->hasOne('App\Models\Mobident', 'id', 'reference_id');        
    }

    public function shouldRetry() {
        $times = intval($this->retries)+1;
        $period = 5*pow(2, $times); //5 minutes
        $period = min(60*24, $period);
        return !User::isGasExpensive() && Carbon::now()->diffInMinutes($this->updated_at) > $period;
    }
}

?>