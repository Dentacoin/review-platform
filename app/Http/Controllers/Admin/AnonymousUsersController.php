<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\AnonymousUser;

use Request;
use Auth;

class AnonymousUsersController extends AdminController {

    public function anonymous_list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Auth::guard('admin')->user()->role!='admin' && Auth::guard('admin')->user()->role!='super_admin' ) {
            return redirect('cms/users/users/edit/'.Auth::guard('admin')->user()->user_id);            
        }

        $users = AnonymousUser::orderBy('id', 'DESC');

        if(!empty(request('search-email'))) {
            $users = $users->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
        }

        $total_count = $users->count();

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

        $users = $users->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = (!empty($this->request->input('search-email')) ? '&search-email='.$this->request->input( 'search-email' ) : '');

        return $this->showView('anonymous-users', array(
            'users' => $users,
            'search_email' => request('search-email'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function anonymousDelete($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = AnonymousUser::find($id);

        if(!empty($item)) {
            $item->delete();
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/users/anonymous_users');
    }
}