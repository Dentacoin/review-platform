<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoxReward extends Model {
    
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'vox_id',
        'reward',
        'is_scam',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');        
    }
    
    
}

?>