<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaidReportPhoto extends Model {

    protected $fillable = [
        'paid_report_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getImageUrl($thumb = false) {
        return url('/storage/paid-reports-gallery/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg');
    }
    
    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/paid-reports-gallery/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.($thumb ? '-thumb' : '').'.jpg';
    }

    public function addImage($img) {
        
        $extensions = ['image/jpeg', 'image/png'];

        if (in_array($img->mime(), $extensions)) {

            $to = $this->getImagePath();
            $to_thumb = $this->getImagePath(true);

            $img->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($to);
            $img->heighten(405, function ($constraint) {
                $constraint->upsize();
            });
            $img->save($to_thumb);
            $this->save();
        }
    }
}

?>