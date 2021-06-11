<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class Admin extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use SoftDeletes, Authenticatable, CanResetPassword;

    protected $fillable = [
    	'username', 
    	'email', 
    	'password', 
        'comments',
        'role',
        'lang_from',
        'lang_to',
        'text_domain',
    	'user_id',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    	'deleted_at',
    ];

    public static function getAdminProfileIds() {
        // return self::whereNotNull('user_id')->pluck('user_id')->toArray();
        return ['65003'];
    }
}
