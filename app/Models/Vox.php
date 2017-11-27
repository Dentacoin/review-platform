<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vox extends Model {
    
    use \Dimsav\Translatable\Translatable;
    use SoftDeletes;
    
    public $translatedAttributes = [
        'title',
        'description',
    ];

    protected $fillable = [
        'title',
        'description',
        'reward',
        'duration',
        'type',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    
    public function questions() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->orderBy('order', 'ASC');
    }
    
    public function questionsReal() {
        return $this->hasMany('App\Models\VoxQuestion', 'vox_id', 'id')->whereNull('is_control')->orderBy('order', 'ASC');
    }
    public function rewards() {
        return $this->hasMany('App\Models\VoxReward', 'vox_id', 'id')->orderBy('id', 'DESC');
    }

    public function formatDuration() {
        return ($this->duration>=60 ? floor($this->duration/60).' hours ' : '').( $this->duration%60 ? ($this->duration%60).' min' : '' );
    }
    
}

class VoxTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'title',
        'description',
    ];

}



?>