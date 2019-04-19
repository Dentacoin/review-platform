<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class VoxRelated extends Model {
    
    //use SoftDeletes;
    
    protected $fillable = [
        'vox_id',
        'related_vox_id',
        'related_question_id',
        'related_answer',
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