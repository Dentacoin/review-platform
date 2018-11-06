<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Review;
use Carbon\Carbon;

use Request;
use Route;

class ReviewsController extends AdminController
{
    public function list() {

        $reviews = Review::orderBy('id', 'DESC');

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

        if(!empty($this->request->input('search-deleted'))) {
            $reviews = $reviews->onlyTrashed();
        }

        if( null !== $this->request->input('results-number')) {
            $results = trim($this->request->input('results-number'));
        } else {
            $results = 50;
        }

        if($results == 0) {
            $reviews = $reviews->take(1000)->get();
        } else {
            $reviews = $reviews->take($results)->get();
        }

        return $this->showView('reviews', array(
            'reviews' => $reviews,
            'search_name_user' => $this->request->input('search-name-user'),
            'search_name_dentist' => $this->request->input('search-name-dentist'),
            'results_number' => $this->request->input('results-number'),
            'search_reviews_from' => $this->request->input('search-reviews-from'),
            'search_reviews_to' => $this->request->input('search-reviews-to'),
        ));
    }


    public function delete( $id ) {
        $item = Review::find($id);

        if(!empty($item)) {
            Review::destroy( $id );
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function massdelete(  ) {
        if( Request::input('ids') ) {
            Review::whereIn('id', Request::input('ids'))->delete();            
        }

        $this->request->session()->flash('success-message', 'All selected reviews and now deleted' );
        return redirect('cms/'.$this->current_page);
    }


    public function restore( $id ) {
        $item = Review::onlyTrashed()->find($id);

        if(!empty($item)) {
            $item->restore();
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.restored') );
        return redirect('cms/'.$this->current_page);
    }

}
