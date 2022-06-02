<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use WebPConvert\WebPConvert;

class DentistBlogpost extends Model {
        
    protected $fillable = [
        'dentist_id',
        'image',
        'title',
        'link',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

	public function user() {
	    return $this->hasOne('App\Models\User', 'id', 'dentist_id');
	}

    public function getImageUrl($thumb = false) {
        if($this->image) {
            return url('/storage/highlights/'.($this->id%100).'/'.str_replace([' ', "'"], ['-', ''], $this->image).($thumb ? '-thumb' : '').'.jpg').'?rev=1'.$this->updated_at->timestamp;
        } else {
            return url('new-vox-img/stats-dummy.png');
        }
    }

    public function getImagePath($thumb = false, $name) {
        $folder = storage_path().'/app/public/highlights/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$name.($thumb ? '-thumb' : '').'.jpg';
    }

    public function addImage($img, $name) {
        $extensions = ['image/jpeg', 'image/png'];
        
        if (in_array($img->mime(), $extensions)) {
            $to = $this->getImagePath(false, $name);
            $to_thumb = $this->getImagePath(true, $name);

            $img->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($to);
            $img->fit( 350, 350 );
            $img->save($to_thumb);

            $this->image = $name;
            $this->save();

            $destination = $this->getImagePath(false, $name).'.webp';
            WebPConvert::convert($this->getImagePath(false, $name), $destination, []);

            $destination_thumb = $this->getImagePath(true, $name).'.webp';
            WebPConvert::convert($this->getImagePath(true, $name), $destination_thumb, []);
        }
    }
}

?>