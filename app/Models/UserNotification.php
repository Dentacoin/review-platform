<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model {
        
    protected $fillable = [
        'user_id',
        'support_id',
        'branch_clinic_id',
        'can_reply',
        'seen',
        'reply_support_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function support() {
        return $this->hasOne('App\Models\SupportContact', 'id', 'support_id');
    }

    public function reply() {
        return $this->hasOne('App\Models\SupportContact', 'id', 'reply_support_id');
    }
}

?>