<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Order;

use Response;
use Request;
use Auth;

class OrdersController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $orders = Order::orderBy('id', 'desc');

        $total_count = $orders->count();
        $page = max(1,intval(request('page')));
        
        $ppp = 100;
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

        $orders = $orders->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $current_url = url('cms/orders');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

    	return $this->showView('orders', array(
            'orders' => $orders,
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
        ));
    }

    public function sended( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $order = Order::find( $id );
        $order->is_send = true;
        $order->save();

        $this->request->session()->flash('success-message', 'Order Sended' );
        return redirect('cms/orders/');
    }

	public function addPaymentInfo( $id ) {

		if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if (!empty(Request::input('payment-info'))) {

            $item = Order::find($id);
            $item->payment_info = Request::input('payment-info');
            $item->save();

            // $this->request->session()->flash('success-message', "Appeal rejected" );
            return Response::json( ['success' => true, 'payment_info' => $item->payment_info] );
        }
	}

}