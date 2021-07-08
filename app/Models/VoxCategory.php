<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxCategory extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'name',
        'color',
        'hasimage',
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
        })->with('vox.translations');
    }

    public function voxesWithoutAnswer($user) {
        $answer_ids = $user->vox_rewards->pluck('reference_id')->toArray();

        return $this->voxes->filter(function($vox) use ($answer_ids) {
            return !in_array($vox->vox_id, $answer_ids);
        });
    }

    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/voxcategories/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.png') : url('new-vox-img/no-avatar-0.png');
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/voxcategories/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.($thumb ? '-thumb' : '').'.png';
    }

    public function addImage($img) {

        $to = $this->getImagePath();
        $to_thumb = $this->getImagePath(true);

        $img->save($to);
        $img->fit( 50, 50 );
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->save();
    }

}

class VoxCategoryTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'name',
    ];

}

?>