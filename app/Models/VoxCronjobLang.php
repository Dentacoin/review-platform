<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxCronjobLang extends Model {
    
    protected $fillable = [
        'vox_id',
        'lang_code',
        'is_completed',
        'is_processing',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');        
    }
}

?>