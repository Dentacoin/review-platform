<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'slug',
        'title',
        'seo_title',
        'description',
        'content',
    ];

    protected $fillable = [
        'hasimage',
        'header',
        'footer',
        'slug',
        'title',
        'seo_title',
        'description',
        'content',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function author() {
        return $this->hasOne('App\Models\Admin', 'id', 'author_id');
    }

    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/pages/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg') : 'http://www.freeiconspng.com/uploads/no-image-icon-23.jpg';
    }
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/pages/'.($this->id%100);
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
        $img->fit( 300, 300 * 280 / 600);
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->save();
    }
}

class PageTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'page_id',
        'slug',
        'title',
        'seo_title',
        'description',
        'content',
    ];

}



?>