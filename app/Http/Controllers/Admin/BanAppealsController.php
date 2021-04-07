<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;


use App\Models\DcnTransaction;
use App\Models\UserAction;
use App\Models\BanAppeal;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;

class BanAppealsController extends AdminController {

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->types = [
            'deleted' => 'Deleted',
            'bad_ip' => 'Bad IP',
            'suspicious_admin' => 'Suspicious (Admin)',
            'manual_verification' => 'Manual verification before withdrawing',
        ];
    }

    public function list() {
    	$items = BanAppeal::orderBy('id', 'desc');

        if(!empty(request('search-user-id'))) {
            $items = $items->where('user_id', request('search-user-id'));
        }

        if(!empty(request('search-email'))) {
            $items = $items->whereHas('user', function($query) {
                $query->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
            });
        }

        if(!empty(request('pending'))) {
            $items = $items->whereNotNull('pending_fields');
        }

        if(!empty(request('search-name'))) {
            $items = $items->whereHas('user', function($query) {
                $query->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
            });
        }

        if(!empty(request('search-type'))) {
            $items = $items->where('type', request('search-type'));
        }

        if(!empty(request('search-status'))) {
            $items = $items->where('status', request('search-status'));
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

        return $this->ShowView('ban-appeals', array(
            'types' => $this->types,
            'items' => $items,
            'js' => [
                '../js/lightbox.js',
            ],
            'css' => [
                'lightbox.css',
            ],
            'search_email' => request('search-email'),
            'search_user_id' => request('search-user-id'),
            'search_name' => request('search-name'),
            'search_type' => request('search-type'),
            'search_status' => request('search-status'),
            'pending' => request('pending'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function approve($id) {

        if (!empty(Request::input('approved_reason'))) {

            $item = BanAppeal::find($id);
            $user = $item->user;
            $user->restoreActions();
            $user->restore();

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'restored';
            $action->reason = Request::input('approved_reason');
            $action->actioned_at = Carbon::now();
            $action->save();

            $item->status = 'approved';
            $item->pending_fields = null;
            $item->save();

            $this->request->session()->flash('success-message', "Appeal approved" );
        } else {
            $this->request->session()->flash('error-message', "You have to write a reason why this appeal has to be approved" );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');
    }

    public function reject($id) {

        if (!empty(Request::input('rejected_reason'))) {

            $item = BanAppeal::find($id);
            $user = $item->user;

            $user->patient_status = 'deleted';
            $user->save();

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'deleted';
            $action->reason = Request::input('rejected_reason');
            $action->actioned_at = Carbon::now();
            $action->save();
            
            $user->sendTemplate(9, null, 'dentacoin');

            $user->deleteActions();
            User::destroy( $user->id );

            $item->status = 'rejected';
            $item->pending_fields = null;
            $item->save();

            $this->request->session()->flash('success-message', "Appeal rejected" );
        } else {
            $this->request->session()->flash('error-message', "You have to write a reason why this appeal has to be rejected" );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');

    }

    public function pending($id) {

	    if(!empty(Request::input('pending_info'))) {
	        $missing_info = Request::input('pending_info');

            $item = BanAppeal::find($id);
            $user = $item->user;

            if(in_array('link', $missing_info) && in_array('image', $missing_info)) {
                //both missing
                $item->pending_fields = ['link', 'image'];
                $user->sendGridTemplate(122, null, 'dentacoin');
            } else if(in_array('link', $missing_info) && !in_array('image', $missing_info)) {
                //no link
                $item->pending_fields = ['link'];
                $user->sendGridTemplate(121, null, 'dentacoin');
            } else if(!in_array('link', $missing_info) && in_array('image', $missing_info)) {
                //no selfie
                $item->pending_fields = ['image'];
                $user->sendGridTemplate(120, null, 'dentacoin');
            }

            $item->save();

            $this->request->session()->flash('success-message', "Email send" );
        } else {
            $this->request->session()->flash('error-message', "Select missing info" );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');

    }

    public function userInfo($user_id) {
        $user = User::withTrashed()->find($user_id);

        if(!empty($user)) {
            return Response::json( ['data' => $user->banAppealInfo()] );
        }
    }

}
