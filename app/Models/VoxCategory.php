<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VoxToCategory;

class VoxCategory extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;


    public function voxes() {
        return $this->hasMany('App\Models\VoxToCategory', 'vox_category_id', 'id')->with('vox')->whereHas('vox', function ($query) {
            $query->where('type', 'normal');
        });
    }

    public function stats_voxes() {
        return $this->hasMany('App\Models\VoxToCategory', 'vox_category_id', 'id')->whereHas('vox', function ($query) {
            $query->where('type', 'normal')->where( 'has_stats', 1 );
        });
    }

    public function voxesWithoutAnswer($user) {
        $answer_ids = $user->vox_rewards->pluck('vox_id')->toArray();

        return $this->voxes->filter(function($vox) use ($answer_ids) {
            return !in_array($vox->vox_id, $answer_ids);
        });
    }

}

class VoxCategoryTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'name',
    ];

}

?>