<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportContact extends Model {
    
    protected $fillable = [
        "user_id",
        "email",
        "platform",
        "issue",
        "description",
        "file_extension",
    ];
    
    protected $dates = [
        'created_at', 
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')->withTrashed();
    }

    public function userEmail() {
        return $this->hasOne('App\Models\User', 'email', 'email')->withTrashed();
    }

    public function getFileUrl($thumb = false) {
        return url('/storage/support-contact/'.($this->id%100).'/'.$this->id.($thumb ? '-thumb' : '').'.'.$this->file_extension).'?rev='.$this->updated_at->timestamp;
    }
}