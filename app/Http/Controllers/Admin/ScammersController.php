<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\User;
use App\Models\UserLogin;
use Carbon\Carbon;

use DB;
use Request;

class ScammersController extends AdminController
{
    public function list() {

        $list = [];
        $ips = DB::select("
            SELECT 
                COUNT(DISTINCT `user_id`) as `count`,
                `ip`
            FROM  `user_logins` 
            GROUP BY `ip`
            HAVING `count`>1
            ORDER BY `count` DESC
        ");

        foreach ($ips as $ipgroup) {
            $ip = $ipgroup->ip;
            $list[$ip] = User::withTrashed()->whereIn('id', function($query) use ($ip) {
                $query->select('user_id')
                ->from(with(new UserLogin)->getTable())
                ->where('ip', 'LIKE', $ip);
            })->get();
        }

        return $this->showView('scammers', array(
            'list' => $list,
        ));
    }
}
