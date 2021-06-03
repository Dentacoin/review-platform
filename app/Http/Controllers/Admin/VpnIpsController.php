<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;

use App\Models\VpnIp;

use Carbon\Carbon;

use Response;
use Request;
use Image;
use Route;

class VpnIpsController extends AdminController {

    public function list() {
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
