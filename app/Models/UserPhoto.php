<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPhoto extends Model {

    protected $fillable = [
        'user_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getImageUrl($thumb = false) {
        return url('/storage/gallery/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg');
    }
    
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/gallery/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.($thumb ? '-thumb' : '').'.jpg';
    }

    public function addImage($img) {

        $to = $this->getImagePath();
        $to_thumb = $this->getImagePath(true);

        $img->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);
        $img->heighten(400, function ($constraint) {
            $constraint->upsize();
        });
        $img->save($to_thumb);
        $this->save();
    }
}


?>