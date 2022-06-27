<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\ReviewAnswer;
use App\Models\Review;

use App\Services\TrpService;
use App\Helpers\AdminHelper;
use Carbon\Carbon;

use Request;
use Auth;

class ReviewsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $reviews = Review::with(['user', 'dentist', 'clinic'])->orderBy('id', 'DESC');

        if(!empty($this->request->input('id'))) {
            $reviews = $reviews->where('id', $this->request->input('id'));
        }

        if(!empty($this->request->input('search-name-dentist'))) {
            $name = $this->request->input('search-name-dentist');
            $reviews = $reviews->whereHas('dentist', function ($query) use ($name) {
                $query->where('name', 'LIKE', $name.'%');
            });
        }
        if(!empty($this->request->input('search-name-user'))) {
            $name = $this->request->input('search-name-user');
            $reviews = $reviews->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'LIKE', $name.'%');
            });
        }

        if(!empty($this->request->input('search-reviews-from'))) {
            $firstday = new Carbon($this->request->input('search-reviews-from'));
            $reviews = $reviews->where('created_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-reviews-to'))) {
            $firstday = new Carbon($this->request->input('search-reviews-to'));
            $reviews = $reviews->where('created_at', '<=', $firstday);
        }

        if(!empty($this->request->input('search-answer'))) {
            $reviews = $reviews->where('answer', 'like', '%'.$this->request->input('search-answer').'%');
        }

        if(!empty($this->request->input('search-deleted'))) {
            $reviews = $reviews->onlyTrashed();
        }

        $total_count = $reviews->count();
        $page = max(1,intval(request('page')));
        $ppp = 50;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $reviews = $reviews->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('reviews', array(
            'reviews' => $reviews,
            'id' => $this->request->input('id'),
            'search_name_user' => $this->request->input('search-name-user'),
            'search_name_dentist' => $this->request->input('search-name-dentist'),
            'search_reviews_from' => $this->request->input('search-reviews-from'),
            'search_reviews_to' => $this->request->input('search-reviews-to'),
            'search_answer' => $this->request->input('search-answer'),
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Review::find($id);
        
        if(!empty($item)) {
            TrpService::deleteReview($item);
            $this->request->session()->flash('success-message', 'Review deleted' );
        } else {
            $this->request->session()->flash('error-message', 'Review not found' );
        }

        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function massdelete() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            foreach (Request::input('ids') as $r_id) {
                $item = Review::find($r_id);
                
                if(!empty($item)) {
                    TrpService::deleteReview($item);
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected reviews are now deleted' );
        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function restore( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $item = Review::onlyTrashed()->find($id);

        if(!empty($item)) {            
            $item->restore();

            ReviewAnswer::onlyTrashed()->where([
                ['review_id', $item->id],
            ])->restore();

            $item->original_dentist->recalculateRating();
        }

        $this->request->session()->flash('success-message', 'Review restored' );
        return redirect('cms/trp/'.$this->current_subpage);
    }
}