<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxBadge extends Model {
    
    protected $fillable = [
        'name',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getImageUrl() {
        return url('/storage/voxes/badge-'.$this->id.'.png').'?rev='.time();
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/voxes';
        return $folder.'/badge-'.$this->id.'.png';
    }

    public function addImage($img) {
        $to = $this->getImagePath();
        $img->save($to);
    }    
}





?>