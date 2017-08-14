<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Country;
use App\Models\City;
use Response;
use Request;

class CitiesController extends BaseController
{

	public function getCities($id, $empty=false) {

		$country = Country::find($id);
		$arr = $country->cities->pluck('name', 'id')->toArray();
		if($empty) {
			$arr = ['' => '-'] + $arr;
		}
		return Response::json([
			'cities' => $arr,
			'code' => '+'.$country->phone_code
		]);
	}

	public function getLocation() {
		$ret = [];
		$location = trim(Request::input('location'));

		$larr = explode(',', $location);
		$city_name = trim($larr[0]);
		$country_name = !empty($larr[1]) ? trim($larr[1]) : null;

		$city = City::whereHas('translations', function ($query) use ($city_name) {
            $query->where('name', 'LIKE', $city_name.'%');
        })->get();

        if($city->isNotEmpty()) {
        	foreach ($city as $c) {
        		$ret[$c->country->id.'-'.$c->id] = [
	        		'city' => $c->id,
	        		'country' => $c->country->id,
	        		'name' => $c->name.', '.$c->country->name
	        	];
        	}
        }

        $country = Country::whereHas('translations', function ($query) use ($city_name) {
            $query->where('name', 'LIKE', $city_name.'%');
        })->get();
        if($country->isNotEmpty()) {
        	foreach ($country as $c) {
        		$ret[$c->id] = [
	        		'city' => null,
	        		'country' => $c->id,
	        		'name' => $c->name
	        	];
        	}
        }


        if( !empty($country_name)) {
        	$country = Country::whereHas('translations', function ($query) use ($country_name) {
	            $query->where('name', 'LIKE', $country_name.'%');
	        })->get();
	        if($country->isNotEmpty()) {
	        	foreach ($country as $c) {
	        		$ret[$c->id] = [
		        		'city' => null,
		        		'country' => $c->id,
		        		'name' => $c->name
		        	];
	        	}
	        }
        }

        foreach ($ret as $key => $value) {
        	$ret[$key]['name'] = str_replace($location, '<b>'.$location.'</b>', $ret[$key]['name']);
        }

    	return Response::json($ret);

	}

}