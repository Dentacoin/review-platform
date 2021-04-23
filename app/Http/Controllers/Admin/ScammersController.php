<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\UserLogin;
use App\Models\User;

use Carbon\Carbon;

use Request;
use DB;

class ScammersController extends AdminController {

    public function list() {

        $list = [];

        if (request('page')) {
            $pageno = request('page');
        } else {
            $pageno = 1;
        }
        $no_of_records_per_page = 10;
        $offset = ($pageno-1) * $no_of_records_per_page;

        $ips = DB::select("
            SELECT 
                COUNT(DISTINCT `user_id`) as `count`,
                `ip`
            FROM  `user_logins` 
            GROUP BY `ip`
            HAVING `count`>1
            ORDER BY `count` DESC
            LIMIT $offset, $no_of_records_per_page
        ");

        $count_ips = DB::select("
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


        $total_count = count($count_ips);

        $page = max(1,intval(request('page')));
        
        $ppp = 10;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
          $start = 1;
          $end   = $total_pages;
        } else {
          if(($page - $adjacents) > 1) { 
            if(($page + $adjacents) < $total_pages) { 
              $start = ($page - $adjacents);            
              $end   = ($page + $adjacents);         
            } else {             
              $start = ($total_pages - (1+($adjacents*2)));  
              $end   = $total_pages;               
            }
          } else {               
            $start = 1;                                
            $end   = (1+($adjacents * 2));             
          }
        }

        return $this->showView('scammers', array(
            'list' => $list,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
        ));
    }
}
