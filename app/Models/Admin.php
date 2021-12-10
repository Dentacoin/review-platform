<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;

class Admin extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use SoftDeletes, Authenticatable, CanResetPassword;

    protected $fillable = [
    	'name',
    	'username',
    	'email',
    	'password',
        'comments',
        'role',
        'lang_from',
        'lang_to',
        'text_domain',
    	'user_id',
        'logged_in',
        'two_factor_auth',
    ];
    protected $dates = [
        'password_last_updated_at',
        'created_at',
        'updated_at',
    	'deleted_at',
    ];

    public static function getAdminProfileIds() {
        // return self::whereNotNull('user_id')->pluck('user_id')->toArray();
        return ['65003'];
    }
    
    public function messages() {
        return $this->hasMany('App\Models\AdminMessage', 'admin_id', 'id')->where(function($query) {
			$query->where('is_read', '=', 0 )
			->orWhereNull('is_read');
		});
    }
}

?>