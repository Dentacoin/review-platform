<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Country;
use App\Models\User;
use App\Models\UserTeam;
use App\Models\City;
use App\Models\VoxAnswer;
use App\Models\Wait;
use Response;
use Request;
use Validator;

class CitiesController extends BaseController
{

	public function getUsername() {

		$username = trim(Request::input('username'));
		$users = User::where('is_dentist', true)->where(function($query) use ($username) {
			$query->where('name', 'LIKE', '%'.$username.'%')
			->orWhere('name_alternative', 'LIKE', '%'.$username.'%');
		})->whereIn('status', ['approved','added_approved','admin_imported'])->take(10)->get();
		$user_list = [];
		foreach ($users as $user) {
			$user_list[] = [
				'name' => $user->getName().( $user->name_alternative && mb_strtolower($user->name)!=mb_strtolower($user->name_alternative) ? ' / '.$user->name_alternative : '' ) ,
				'link' => $user->getLink(),
				'type' => $user->is_clinic ? trans('front.common.clinic') : trans('front.common.dentist'),
				'is_clinic' => $user->is_clinic,
				'rating' => $user->avg_rating,
				'reviews' => $user->ratings,
				'location' => $user->city_name.', '.$user->country->name,
				'lat' => $user->lat,
				'lon' => $user->lon,
			];
		}

		return Response::json($user_list);
	}

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

	public function getQuestions() {
        $dcn_price = file_get_contents('/tmp/dcn_price');
        $dcn_change = file_get_contents('/tmp/dcn_change');

		$ret = [
			'question_count' => number_format( VoxAnswer::getCount() , 0, '', ' '),
			'dcn_price' => sprintf('%.5F', $dcn_price),
			'dcn_price_full' => sprintf('%.10F', $dcn_price),
			'dcn_change' => $dcn_change,
		];
    	return Response::json($ret);
	}


	public function wait() {
        $ret = [
        	'success' => false
        ];


        $validator_arr = [
            'email' => ['required', 'email'],
            'name' => ['required']
        ];
        $validator = Validator::make(Request::all(), $validator_arr);

        if ($validator->fails()) {
            $msg = $validator->getMessageBag()->toArray();
            $ret['messages'] = [];
            foreach ($msg as $key => $value) {
            	$ret['messages'][] = implode(', ', $value);
            }
        } else {
        	$wait = new Wait;
        	$wait->email = Request::input('email');
        	$wait->name = Request::input('name');
        	$wait->save();
        	$ret['success'] = true;
        }

        return Response::json($ret);
	}

	public function getClinic($id=null) {

		$joinclinic = trim(Request::input('joinclinic'));
		$clinics = User::where('is_clinic', true);
		if( $id ) {
			$clinics->whereDoesntHave('team', function ($query) use ($id) {
	            $query->where('dentist_id', $id);
	        });
		}
		$clinics = $clinics->where('name', 'LIKE', $joinclinic.'%')->take(10)->get();

		$clinic_list = [];
		foreach ($clinics as $clinic) {
			$clinic_list[] = [
				'name' => $clinic->getName(),
				'id' => $clinic->id,
			];
		}

		return Response::json($clinic_list);
	}

	public function getDentist($id=null) {

		$invitedentist = trim(Request::input('invitedentist'));

		$dentists = User::where('is_dentist', 1)->where(function($query) use ($invitedentist) {
			$query->where('is_clinic', '=', 0 )
			->orWhereNull('is_clinic');
		});

		if( $id ) {
			$dentists->whereDoesntHave('my_workplace', function ($query) use ($id) {
	            $query->where('user_id', $id);
	        });
		}

        $dentists = $dentists->where('name', 'LIKE', $invitedentist.'%')->take(10)->get();

		$dentist_list = [];
		foreach ($dentists as $dentist) {
			$dentist_list[] = [
				'name' => $dentist->getName(),
				'id' => $dentist->id,
			];
		}

		return Response::json($dentist_list);
	}

}