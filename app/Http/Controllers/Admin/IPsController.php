<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\UserLogin;
use App\Models\VpnIp;
use App\Models\User;

use App\Helpers\AdminHelper;
use Carbon\Carbon;

use Request;
use Auth;
use DB;

class IPsController extends AdminController {

    public function bad() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        return $this->showView('scammers', array(
            'list' => $list,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
        ));
    }

    public function vpn() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
    	$items = VpnIp::orderBy('id', 'desc');

        if(!empty(request('search-ip'))) {
            $items = $items->where('ip', "LIKE", request('search-ip'));
        }

        if(!empty(request('search-from'))) {
            $firstday = new Carbon(request('search-from'));
            $items = $items->where('created_at', '>=', $firstday);
        }
        if(!empty(request('search-to'))) {
            $firstday = new Carbon(request('search-to'));
            $items = $items->where('created_at', '<=', $firstday->addDays(1));
        }

        $total_count = $items->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 50;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $items = $items->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->ShowView('vpn-ips', array(
            'items' => $items,
            'search_ip' => request('search-ip'),
            'search_from' => request('search-from'),
            'search_to' => request('search-to'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }
}
