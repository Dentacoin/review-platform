<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use WebPConvert\WebPConvert;

class BanAppeal extends Model {
    
    protected $fillable = [
        'user_id',
        'link',
        'image',
        'description',
        'type',
        'status',
        'pending_fields',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public function getImageUrl($thumb = false) {
        return $this->image ? url('/storage/appeals/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg').'?rev='.$this->updated_at->timestamp : url('new-vox-img/no-avatar-'.($this->is_dentist ? '1' : '0').'.png');
    }

    public function getImagePath($thumb = false) {
        $folder = storage_path().'/app/public/appeals/'.($this->id%100);
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
            $img->fit( 400, 400 );
            $img->save($to_thumb);
            $this->image = true;
            $this->save();
        }
    }

    public function setPendingFieldsAttribute($value) {
        $this->attributes['pending_fields'] = null;
        if(!empty($value) && is_array($value)) {
            $this->attributes['pending_fields'] = implode(',', $value);
        }
    }

    public function getPendingFieldsAttribute($value) {
        if(!empty($value)) {
            return explode(',', $value);
        }
        return [];
    }
}

?>