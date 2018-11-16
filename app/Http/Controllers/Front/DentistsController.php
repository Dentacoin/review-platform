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
        // $rec = User::where('is_dentist', 1)->get();
        // foreach ($rec as $u) {
        //     $u->updateStrength();
        // }

        $items = User::where('is_dentist', 1)->where('status', 'approved');

        $page = max(1, $page);
        $ppp = 12;
        $all_locations = Request::input('all_locations');
        $city = null;
        $country = null;
        $order = 'rating';
        $name = '';
        $partner = '';
        $type = '';

        if( Request::input('partner') ) {
            $partner = Request::input('partner');
        }
        if( Request::input('username') ) {
            $name = Request::input('username');
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
        if( Request::input('type') ) {
            $type = Request::input('type');
        }
        // if( Request::input('show-dentist') ) {
        //     $show_dentist = Request::input('show-dentist');
        // }
        // if( Request::input('show-clinic') ) {
        //     $show_clinic = Request::input('show-clinic');
        // }

        if( Request::input('order') && ( Request::input('order')=='reviews' || Request::input('order')=='name' || Request::input('order')=='strength' ) ) {
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

        if(!empty($type)) {
            if( $type=='clinic' ) {
                $items = $items->where('is_clinic', 1);
            } else {
                $items = $items->where(function ($query) {
                    $query->where('is_clinic', 0)
                    ->orWhereNull('is_clinic');
                });
            }
        }
        // if(!empty($show_dentist)) {
        //     $items = $items->where('is_clinic', false);
        // }
        // if(!empty($show_clinic)) {
        //     $items = $items->where('is_clinic', true);
        // }

        if($order=='rating') {
            $items->orderBy('avg_rating', 'DESC');
        }
        if($order=='reviews') {
            $items->orderBy('ratings', 'DESC');
        }
        if($order=='name') {
            $items->orderBy('name', 'ASC');
        }
        if($order=='strength') {
            $items->orderBy('strength', 'DESC');
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

        $this->types = [
            'clinic' => trans('front.common.clinic'),
            'dentist' => trans('front.common.dentist'),
        ];

		return $this->ShowView('dentists', [
			'items' => $items,
            'search_location' => !empty($city) && !empty($country) ? $city->name.', '.$country->name : ( !empty($country) ? $country->name : ''),
            'all_locations' => $all_locations,
            'city' => $city,
            'country' => $country,
            'countries' => Country::get(),
            'name' => $name,
            'category' => Request::input('category'),
            'order' => $order,
            'partner' => $partner,
            'top_cities' => $top_cities,
            'top_countries' => $top_countries,
            'ppp' => $ppp,
            'page_num' => $page,
            'placeholder' => $placeholder,
            'types' => $this->types,
            'type' => $type,
            'orders' => [
                'rating',
                'reviews',
                'name',
                'strength',
            ],
            'is_ajax' => $ajax,
            'js' => [
                'search.js'
            ]
		]);
    }

}