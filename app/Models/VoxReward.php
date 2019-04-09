<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class VoxReward extends Model {
    
    //use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'vox_id',
        'reward',
        'is_scam',
        'mistakes',
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
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');        
    }

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');        
    }

    public function formatDuration() {
        return ($this->seconds>=60 ? floor($this->seconds/60).' min ' : '').( $this->seconds%60 ? ($this->seconds%60).' sec' : '' );
    }
    
    
}

?>