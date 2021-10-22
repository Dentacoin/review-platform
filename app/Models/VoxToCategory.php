<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxToCategory extends Model {
    
    protected $fillable = [
        'vox_id',
        'vox_category_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');        
    }

    public function category() {
        return $this->hasOne('App\Models\VoxCategory', 'id', 'vox_category_id');        
    }
}

?>