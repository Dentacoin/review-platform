<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserLogin extends Model {
        
    protected $fillable = [
        'user_id',
        'platform',
        'ip',
        'is_vpn',
        'device',
        'brand',
        'model',
        'os',
        'country',
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

    public function getDeviceName() {
        $arr = [];

        if (!empty($this->device) && $this->device != 'smartphone') {
            $arr[] = ucfirst($this->device);
        }
        if (!empty($this->brand)) {
            $arr[] = ucfirst($this->brand);
        }
        if (!empty($this->model)) {
            $arr[] = ucfirst($this->model);
        }
        if (!empty($this->os)) {
            $arr[] = ucfirst($this->os);
        }

        return implode(',', $arr);
    }
}

?>