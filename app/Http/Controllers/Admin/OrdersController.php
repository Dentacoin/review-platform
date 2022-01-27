<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Helpers\AdminHelper;

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

        $orders = Order::with(['report', 'report.translations'])->orderBy('id', 'desc');

        $total_count = $orders->count();
        $page = max(1,intval(request('page')));
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $orders = $orders->skip( ($page-1)*$ppp )->take($ppp)->get();

        $current_url = url('cms/orders');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
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