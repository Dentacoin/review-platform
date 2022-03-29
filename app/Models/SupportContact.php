<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportContact extends Model {

    use SoftDeletes;
    
    protected $fillable = [
        "user_id",
        "email",
        "platform",
        "issue",
        "description",
        "file_extension",
        "admin_id",
        "admin_answer",
        "custom_title",
        "custom_subtitle",
        "custom_subject",
        "admin_answer_id",
        "replied_main_support_id",
        "replied_support_id"
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

    public function emailTemplate() {
        return $this->hasOne('App\Models\EmailTemplate', 'id', 'admin_answer_id')->withTrashed();
    }

    public function mainContactReply() {
        return $this->hasOne('App\Models\SupportContact', 'id', 'replied_main_support_id');
    }

    public function oldContactReply() {
        return $this->hasOne('App\Models\SupportContact', 'id', 'replied_support_id');
    }
}