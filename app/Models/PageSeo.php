<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSeo extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'seo_title',
        'seo_description',
        'social_title',
        'social_description',
    ];

    protected $fillable = [
    	'name',
    	'url',
        'hasimage',
        'seo_title',
        'seo_description',
        'social_title',
        'social_description',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getImageUrl($thumb = false) {
        return $this->hasimage ? url('/storage/pagesseo/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg').'?rev='.$this->updated_at->timestamp : ( $this->platform == 'vox' ? url('img-vox/logo-text.png') : url('img-trp/socials-cover.jpg'));
    }
    
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/pagesseo/'.($this->id%100);
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

        if ($img->height() > $img->width()) {
            $img->heighten(400);
        } else {
            $img->widen(400);
        }
        $img->resizeCanvas(400, 400);

        //$img->fit( 400, 400 );
        $img->save($to_thumb);
        $this->hasimage = true;
        $this->save();
    }
}

class PageSeoTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'page_seo_id',
        'seo_title',
        'seo_description',
        'social_title',
        'social_description',
    ];
}

?>