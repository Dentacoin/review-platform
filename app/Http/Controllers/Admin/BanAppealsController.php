<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\UserHistory;
use App\Models\UserAction;
use App\Models\BanAppeal;
use App\Models\User;

use App\Helpers\AdminHelper;
use Carbon\Carbon;

use Response;
use Request;
use Route;
use Auth;

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
            if(request('pending') == 'pending') {
                $items = $items->whereNotNull('pending_fields');
            }

            if(request('pending') == 'no-pending') {
                $items = $items->whereNull('pending_fields');
            }
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
            'search_from' => request('search-from'),
            'search_to' => request('search-to'),
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if (empty(Request::input('approve_radio')) || (!empty(Request::input('approve_radio')) && Request::input('approve_radio') == 'Other' && empty(Request::input('approved_reason')))) {
            // $this->request->session()->flash('error-message', "You have to write a reason why this appeal has to be approved" );
            return Response::json( ['success' => false, 'message' => "You have to write a reason why this appeal has to be approved"] );
        } else {
            $item = BanAppeal::find($id);
            $user = $item->user;

            $last_user_action = UserAction::where('user_id', $user->id)->orderBy('id', 'desc')->first();

            if (!empty($last_user_action) && !empty($last_user_action->reason) && mb_strpos($last_user_action->reason, 'KYC country') !== false) {
                $user->skip_civic_kyc_country = true;
                $user->save();
            }

            $user->restoreActions();
            $user->restore();

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'restored';
            $action->reason = !empty(Request::input('approved_reason')) ? Request::input('approved_reason') : Request::input('approve_radio');
            $action->actioned_at = Carbon::now();
            $action->save();

            $item->status = 'approved';
            $item->pending_fields = null;
            $item->save();

            // $this->request->session()->flash('success-message', "Appeal approved" );
            return Response::json( ['success' => true, 'type' => 'approved'] );
        }

        // return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');
    }

    public function reject($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if (empty(Request::input('reject_radio')) || (!empty(Request::input('reject_radio')) && (Request::input('reject_radio') == 'Other' || Request::input('reject_radio') == 'Multiple accounts') && empty(Request::input('rejected_reason')))) {

            if(!empty(Request::input('reject_radio')) && Request::input('reject_radio') == 'Multiple accounts' && empty(Request::input('rejected_reason'))) {
                // $this->request->session()->flash('error-message', "You have to write which are the multiple accounts" );
                return Response::json( ['success' => false, 'message' => "You have to write which are the multiple accounts"] );
            } else {
                // $this->request->session()->flash('error-message', "You have to write a reason why this appeal has to be rejected" );
                return Response::json( ['success' => false, 'message' => "You have to write a reason why this appeal has to be rejected"] );
            }

        } else {

            $item = BanAppeal::find($id);
            $user = $item->user;

            $user_history = new UserHistory;
            $user_history->user_id = $user->id;
            $user_history->patient_status = $user->patient_status;
            $user_history->save();

            $user->patient_status = 'deleted';
            $user->save();

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'deleted';
            $action->reason = Request::input('reject_radio').(!empty(Request::input('rejected_reason')) ? ': '.Request::input('rejected_reason') : '');
            $action->actioned_at = Carbon::now();
            $action->save();
            
            $user->sendTemplate(9, null, 'dentacoin');

            $user->deleteActions();
            User::destroy( $user->id );

            $item->status = 'rejected';
            $item->pending_fields = null;
            $item->save();

            // $this->request->session()->flash('success-message', "Appeal rejected" );
            return Response::json( ['success' => true, 'type' => 'rejected'] );
        }

        // return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');

    }

    public function pending($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

            // $this->request->session()->flash('success-message', "Email send" );
            return Response::json( ['success' => true, 'type' => 'pending'] );
        } else {
            // $this->request->session()->flash('error-message', "Select missing info" );
            return Response::json( ['success' => false, 'message' => "Select missing info"] );
        }

        // return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');

    }

    public function userInfo($user_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $user = User::withTrashed()->find($user_id);

        if(!empty($user)) {
            return Response::json( ['data' => $user->banAppealInfo()] );
        }
    }

}
