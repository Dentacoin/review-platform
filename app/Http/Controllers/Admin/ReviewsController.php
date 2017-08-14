<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Review;

use Request;
use Route;

class ReviewsController extends AdminController
{
    public function list() {

        $reviews = Review::orderBy('id', 'DESC');

        if(!empty($this->request->input('search-rate_from'))) {
            $reviews = $reviews->where('rating', '>=', $this->request->input('search-rate_from'));
        }
        if(!empty($this->request->input('search-rate_to'))) {
            $reviews = $reviews->where('rating', '<=', $this->request->input('search-rate_to'));
        }

        if(!empty($this->request->input('search-country_id'))) {
            $reviews = $reviews->whereHas('dentist', function ($query) {
                $query->where('country_id', Request::input('search-country_id'));
            });
        }
        if(!empty($this->request->input('search-city_id'))) {
            $reviews = $reviews->whereHas('dentist', function ($query) {
                $query->where('city_id', Request::input('search-city_id'));
            });
        }

        if(!empty($this->request->input('search-deleted'))) {
            $reviews = $reviews->onlyTrashed();
        }

        
        $reviews = $reviews->take(50)->get();

        return $this->showView('reviews', array(
            'reviews' => $reviews,
            'search_rate_from' => $this->request->input('search-rate_from'),
            'search_rate_to' => $this->request->input('search-rate_to'),
            'search_country_id' => $this->request->input('search-country_id'),
            'search_city_id' => $this->request->input('search-city_id'),
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

    public function restore( $id ) {
        $item = Review::onlyTrashed()->find($id);

        if(!empty($item)) {
            $item->restore();
        }

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.restored') );
        return redirect('cms/'.$this->current_page);
    }

}
