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
        //depricated; check /app/Http/Controllers/Admin/ImagesControler::getImage()
        return $this->image ? url('/storage/appeals/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.jpg').'?rev='.$this->updated_at->timestamp : url('new-vox-img/no-avatar-'.($this->is_dentist ? '1' : '0').'.png');
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