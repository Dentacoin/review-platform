<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DcnReward extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'platform',
        'reward',
        'type',
        'reference_id',
        'seconds',
        'device',
        'brand',
        'model',
        'os',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'reference_id');
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');        
    }

    public function formatDuration() {
        return ($this->seconds>=60 ? floor($this->seconds/60).' min ' : '').( $this->seconds%60 ? ($this->seconds%60).' sec' : '' );
    }

    public function getDeviceName() {
        $arr = [];

        if (!empty($this->device) && $this->device != 'smartphone') {
            $arr[] = ucfirst($this->device);
        }
        if (!empty($this->brand)) {
            $arr[] = ucfirst($this->brand);
        }
        if (!empty($this->model)) {
            $arr[] = ucfirst($this->model);
        }
        if (!empty($this->os)) {
            $arr[] = ucfirst($this->os);
        }

        return implode(',', $arr);
    }
}

?>