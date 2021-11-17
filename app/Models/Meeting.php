<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Image;

class Meeting extends Model {
    
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'seo_title',
        'seo_description',
        'checklist_title',
        'checklists',
        'duration',
        'after_checklist_info',
        'iframe_id',
        'video_id',
        'video_title',
        'website_url',
        'has_website_image',
        'hasimage',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getImageUrl() {
        return $this->hasimage ? url('/storage/meetings/'.($this->id%100).'/'.$this->id.'.png').'?rev=1'.$this->updated_at->timestamp : url('new-vox-img/stats-dummy.png');
    }

    public function getImagePath() {
        $folder = storage_path().'/app/public/meetings/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'.png';
    }

    public function addImage($img) {

        $to = $this->getImagePath();
        $img->resize(610, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);
        $this->hasimage = true;
        $this->save();
    }

    public function getWebsiteImageUrl() {
        return $this->has_website_image ? url('/storage/meetings-website/'.($this->id%100).'/'.$this->id.'.png').'?rev=1'.$this->updated_at->timestamp : url('new-vox-img/stats-dummy.png');
    }

    public function getWebsiteImagePath() {
        $folder = storage_path().'/app/public/meetings-website/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'.png';
    }

    public function addWebsiteImage($img) {

        $to = $this->getWebsiteImagePath();
        $img->resize(610, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);
        $this->has_website_image = true;
        $this->save();
    }
}

?>