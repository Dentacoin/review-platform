<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserLogin extends Model {
        
    protected $fillable = [
        'user_id',
        'platform',
        'ip',
        'device',
        'brand',
        'model',
        'os',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
    
    public function getUsersCount() {
        $ips = DB::select("
            SELECT 
                COUNT(DISTINCT `user_id`) as `count`
            FROM  `user_logins`
            WHERE `ip` LIKE '".$this->ip."'
        ");

        return !empty($ips) ? current($ips)->count : 1;
    }
}

?>