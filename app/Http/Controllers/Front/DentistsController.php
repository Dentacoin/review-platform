<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\City;
use App\Models\Country;


class DentistsController extends FrontController
{

    public function paginate($locale=null, $page) {
        return $this->list($locale, null, null, $page, true);
    }

    public function list($locale=null, $country=null, $city=null, $page=null, $ajax=null) {
        $items = User::where('is_dentist', 1);

        $page = max(1, $page);
        $ppp = 12;
        $all_locations = Request::input('all_locations');
        $city = null;
        $country = !$all_locations && $this->request->attributes->get('country_id') ? Country::find($this->request->attributes->get('country_id')) : null; //$this->country_id;
        $order = 'rating';
        $name = '';
        $partner = '';

        if( Request::input('partner') ) {
            $partner = Request::input('partner');
        }
        if( Request::input('name') ) {
            $name = Request::input('name');
        }
        if( Request::input('city') && !$all_locations ) {
            $city = City::find(Request::input('city'));
        }
        if( Request::input('country') && !$all_locations ) {
            $country = Country::find(Request::input('country'));
        }
        if( Request::input('category') ) {
            $category = Request::input('category');
            
        }

        if( Request::input('order') && ( Request::input('order')=='reviews' || Request::input('order')=='name' ) ) {
            $order = Request::input('order');
        }

        if(!empty($country)) {
            $items = $items->where('country_id', $country->id);
        }
        if(!empty($city)) {
            $items = $items->where('city_id', $city->id);
        }
        if(!empty($name)) {
            $items = $items->where('name', 'LIKE', '%'.$name.'%');
        }
        if(!empty($partner)) {
            $items = $items->where('is_partner', true);
        }
        if(!empty($category)) {
            $items = $items->whereHas('categories', function ($query) use ($category) {
                $query->where('category_id', array_search($category, config('categories')));
            });
        }


        if($order=='rating') {
            $items->orderBy('avg_rating', 'DESC');
        }
        if($order=='reviews') {
            $items->orderBy('ratings', 'DESC');
        }
        if($order=='name') {
            $items->orderBy('name', 'ASC');
        }

        $items = $items->take($ppp)->skip( ($page-1)*$ppp )->get();

        if($country) {
            $top_cities = City::where('country_id', $country->id)->where('avg_rating', '>', 0)->orderBy('avg_rating', 'DESC')->take(20)->get();
        } else {
            $top_cities = City::orderBy('avg_rating', 'DESC')->where('avg_rating', '>', 0)->take(20)->get();            
        }
        $top_countries = Country::orderBy('avg_rating', 'DESC')->where('avg_rating', '>', 0)->take(10)->get();

        $placeholder = '';
        if($city) {
            $placeholder = $city->name.', '.$city->country->name;
        } else if($country) {
            $placeholder = $country->name;
        }

		return $this->ShowView('dentists', [
			'items' => $items,
            'search_location' => !empty($city) && !empty($country) ? $city->name.', '.$country->name : ( !empty($country) ? $country->name : ''),
            'all_locations' => $all_locations,
            'city' => $city,
            'country' => $country,
            'name' => $name,
            'category' => Request::input('category'),
            'order' => $order,
            'partner' => $partner,
            'top_cities' => $top_cities,
            'top_countries' => $top_countries,
            'ppp' => $ppp,
            'page_num' => $page,
            'placeholder' => $placeholder,
            'orders' => [
                'rating',
                'reviews',
                'name',
            ],
            'is_ajax' => $ajax,
            'js' => [
                'search.js'
            ]
		]);
    }

}