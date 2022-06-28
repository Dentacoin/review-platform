<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use WebPConvert\WebPConvert;

class UserPhoto extends Model {

    protected $fillable = [
        'user_id',
        'haswebp',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

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
        // dd($img->height() , $img->width());
        
        $extensions = ['image/jpeg', 'image/png'];

        if (in_array($img->mime(), $extensions)) {

            $to = $this->getImagePath();
            $to_thumb = $this->getImagePath(true);

            $img->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($to);

            if ($img->height() > $img->width()) {
                $img->heighten(300);
            } else {
                $img->widen(500);
            }
            $img->resizeCanvas(500, 300);

            // $img->heighten(400, function ($constraint) {
            //     $constraint->upsize();
            // });
            $img->save($to_thumb);
            $this->save();
    
            $destination = self::getImagePath().'.webp';
            WebPConvert::convert(self::getImagePath(), $destination, []);
    
            $destination_thumb = self::getImagePath(true).'.webp';
            WebPConvert::convert(self::getImagePath(true), $destination_thumb, []);
        }
    }
}


?>