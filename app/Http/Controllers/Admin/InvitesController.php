<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\UserInvite;

use Validator;
use Request;


class InvitesController extends AdminController {

    public function list( ) {

        $items = UserInvite::orderBy('id', 'desc');

        if(!empty(request('search-user-id'))) {
            $items = $items->where('user_id', request('search-user-id'));
        }

        if(!empty(request('search-email'))) {
            $items = $items->whereHas('user', function($query) {
                $query->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
            });
        }

        if(!empty(request('search-name'))) {
            $items = $items->whereHas('user', function($query) {
                $query->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
            });
        }

        if(!empty(request('search-invited-id'))) {
            $items = $items->whereHas('invited', function($query) {
                $query->where('id', request('search-invited-id'));
            });
        }

        if(!empty(request('search-invited-email'))) {
            $items = $items->where('invited_email', 'LIKE', '%'.trim(request('search-invited-email')).'%');
        }

        if(!empty(request('search-invited-name'))) {
            $items = $items->where('invited_name', 'LIKE', '%'.trim(request('search-invited-name')).'%');
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
            if($key != 'search') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->ShowView('invites', array(
            'items' => $items,
            'search_user_id' => request('search-user-id'),
            'search_email' => request('search-email'),
            'search_name' => request('search-name'),
            'search_invited_id' => request('search-invited-id'),
            'search_invited_email' => request('search-invited-email'),
            'search_invited_name' => request('search-invited-name'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function delete( $id ) {
        UserInvite::destroy( $id );

        $this->request->session()->flash('success-message', 'Invite Deleted' );
        return redirect('cms/invites');
    }
}