<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use App\Models\VoxAnswer;
use App\Models\Country;
use App\Models\User;
use App\Models\City;

use Response;
use Request;

class CitiesController extends BaseController {

	public function searchDentists() {

		$ret = [];

		$forForm = Request::input('submit-form') ?? false;

		if($forForm) {
			$searchUserName = trim(Request::input('dentist-name'));
			$searchSplitedUsername = preg_split('/\s+/', $searchUserName, -1, PREG_SPLIT_NO_EMPTY);
			$searchCountryID = Request::input('search-country-id');
			$searchCountryName = Request::input('dentist-country');
			$searchCity = Request::input('dentist-city');
			$searchForPartner = Request::input('partner');
		} else {
			$searchUserName = trim(Request::input('username'));
			$searchSplitedUsername = preg_split('/\s+/', $searchUserName, -1, PREG_SPLIT_NO_EMPTY);
			$searchCountryID = Request::input('country_id');
			$searchCountryName = Request::input('country_name');
			$searchCity = Request::input('city');
			$searchForPartner = Request::input('is_partner');
		}
		
		//remove dr from search query, because dr is in another field
		if ($this->searchWordInString('dr. ', $searchUserName) || $this->searchWordInString('dr ', $searchUserName)) {

			$wordToBeRemoved = $this->searchWordInString('dr. ', $searchUserName) ? 'dr. ' : ($this->searchWordInString('dr. ', $searchUserName) ? 'dr ' : '');
			$convertedUsername = str_replace($wordToBeRemoved, '', mb_strtolower($searchUserName));
			
			$newUsername = [];
			foreach(explode(' ',$convertedUsername) as $convertedWord) {
				$newUsername[] = ucfirst($convertedWord);
			}
			
			request()->merge([
				'username' => implode(' ', $newUsername)
			]);
		}

		//first search for dentists & clinics with exact name field
		$firstSearch = User::where('is_dentist', true)
		->where(function($query) use ($searchUserName) {
			$query->where('name', 'like', $searchUserName.'%')
			->orWhere('name_alternative', 'like', $searchUserName.'%');
		})->whereIn('status', config('dentist-statuses.shown'))
		->orderBy('is_partner', 'desc')
		->whereNull('self_deleted');
		if($searchForPartner) {
			$firstSearch->where('is_partner', 1);
		}
		$firstSearch = $firstSearch->take(20)->get();

		$users = $firstSearch;

		if($users->count() < 20) {

			//second search for dentists & clinics with name that starts with the field
			$secondSearch = User::where('is_dentist', true)
			->whereNotIn('id', $users->pluck('id')->toArray())
			->where(function($query) use ($searchUserName) {
				$query->where('name', 'like', '%'.$searchUserName.'%')
				->orWhere('name_alternative', 'like', '%'.$searchUserName.'%');
			})->whereIn('status', config('dentist-statuses.shown'))
			->orderBy('is_partner', 'desc')
			->whereNull('self_deleted');
			if($searchForPartner) {
				$secondSearch->where('is_partner', 1);
			}		
			$users = $users->concat($secondSearch->take(20-$users->count())->get());
			
			if($users->count() < 20) {

				//then search for dentists & clinics with similar name field
				$dentists = User::where('is_dentist', true)
				->whereNotIn('id', $users->pluck('id')->toArray())
				->where(function($query) use ($searchSplitedUsername) {
					foreach ($searchSplitedUsername as $value) {
						$query->where(function($q) use ($value) {
							$q->orWhere('name', 'like', "%{$value}%")
							->orWhere('name_alternative', 'like', "%{$value}%");
						});
					}
				})->whereIn('status', config('dentist-statuses.shown'))
				->whereNull('self_deleted')
				->orderBy('is_partner', 'desc');
				if($searchForPartner) {
					$dentists->where('is_partner', 1);
				}
				$users = $users->concat($dentists->take(20-$users->count())->get());
			}
		}

		$dentistsAndClinics = User::where('is_dentist', true)
		->where(function($query) use ($searchSplitedUsername) {
			foreach ($searchSplitedUsername as $value) {
				$query->where(function($q) use ($value) {
					$q->orWhere('name', 'like', "%{$value}%")
					->orWhere('name_alternative', 'like', "%{$value}%");
				});
			}
		})->whereIn('status', config('dentist-statuses.shown'))
		->whereNull('self_deleted')
		->orderBy('is_partner', 'desc');
		
		if($searchForPartner) {
			$dentistsAndClinics->where('is_partner', 1);
		}

		$cityQuery = trim(explode(',', trim($searchCity))[0]);

		if(!empty($searchCountryID) || !empty($searchCountryName)) { //search dentists in country
			if(!empty($searchCountryID)) {
				$countryName = Country::find($searchCountryID)->name;
				$countryUsers = $dentistsAndClinics->where('country_id', $searchCountryID);
				$searchCityCountry = !empty($searchCity) ? '<b>'.$searchCity.'</b>' : (in_array($searchCountryID, [232, 230])  ? 'the <b>'.$countryName.'</b>' : '<b>'.$countryName.'</b>');
			} else {
				$countryUsers = $dentistsAndClinics->whereHas('country', function($query) use ($searchCountryName) {
					$query->where('name', '%LIKE%', $searchCountryName);
				});
				$searchCityCountry = '<b>'.(!empty($searchCity) ? $searchCity : $searchCountryName).'</b>';
			}
			
			if(!empty($searchCity)) { //search dentists in country AND in city
				$countryUsers = $countryUsers->where('city_name', 'LIKE', '%'.$cityQuery.'%')
				->take(20)
				->get();
			} else {
				$countryUsers = $countryUsers->take(20)->get();
			}

			if($countryUsers->isEmpty()) {
				if($users->isNotEmpty()) {
					$ret['alert'] = 'We couldn’t find a dental provider matching your search in '.$searchCityCountry.', but we found following results:';
				}
			} else {
				$users = $countryUsers;
			}
		} else if(!empty($searchCity)) { //search dentists in city
			$cityUsers = $dentistsAndClinics->where('city_name', 'LIKE', '%'.$searchCity.'%')
			->take(20)
			->get();

			if($cityUsers->isEmpty()) {
				if($users->isNotEmpty()) {
					$ret['alert'] = 'We couldn’t find a dental provider matching your search in <b>'.$cityQuery.'</b>, but we found following results:';
				}
			} else {
				$users = $cityUsers;
			}
		}

		$user_list = [];

		$teamMembersCount = 0;
		
		if($users->isNotEmpty()) {
			foreach ($users as $user) {
				$name = $user->getNames().( $user->name_alternative && mb_strtolower($user->name)!=mb_strtolower($user->name_alternative) ? ' / '.$user->name_alternative : '');
				$mainClinic = $user->status=='dentist_no_email' ? User::find($user->invited_by) : '';
				$highlited_name = $name;
				foreach($searchSplitedUsername as $splitedName) {
					$highlited_name = str_ireplace( $splitedName , '<span>'.$splitedName.'</span>', $highlited_name);
				}

				if($user->status=='dentist_no_email') {
					$teamMembersCount++;
				}

				$user_list[] = [
					'id' => $user->id,
					'name' => $highlited_name,
					'pure_name' => $name,
					'link' => $mainClinic ? $mainClinic->getLink() : (!empty($user->slug) ? $user->getLink() : ''),
					'avatar' => $user->getImageUrl(true),
					'is_partner' => $user->is_partner,
					'location' => !empty($user->country) ? $user->city_name.', '.$user->country->name : '',
					'status' => $user->status,
					'team_clinic_name' => $mainClinic ? $mainClinic->getNames() : '',
					'team_clinic_location' => $mainClinic ? (!empty($mainClinic->country) ? $mainClinic->city_name.', '.$mainClinic->country->name : '') : '',
					'team_clinic_avatar' => $mainClinic ? $mainClinic->getImageUrl(true) : '',
				];
			}

			$ret['dentists'] = $user_list;

			if($teamMembersCount == 1 && $users->count() == 1) {
				$ret['alert'] = 'We found a dentist with this name at:';
			}
		} else {
			$ret['alert'] = 'Sorry, we couldn’t find any matches. Check your search query for typos and try again.';
		}

		return Response::json($ret);
	}

	private function searchWordInString($word, $string) {
		return mb_strpos(mb_strtolower($string), $word) === 0;
	}

	public function getCities($id, $empty=false) {

		$country = Country::find($id);
		if(!empty($country)) {
			
			$arr = $country->cities->pluck('name', 'id')->toArray();
			if($empty) {
				$arr = ['' => '-'] + $arr;
			}
			return Response::json([
				'cities' => $arr,
				'code' => '+'.$country->phone_code
			]);
		}
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
        $dcn_price = @file_get_contents('/tmp/dcn_price');
        $dcn_change = @file_get_contents('/tmp/dcn_change');

		$ret = [
			'question_count' => number_format( VoxAnswer::getCount() , 0, '', ' '),
			'dcn_price' => sprintf('%.6F', $dcn_price),
			'header_price' => sprintf('%.3F', 1000 * $dcn_price),
			'dcn_price_full' => sprintf('%.10F', $dcn_price),
			'dcn_change' => $dcn_change,
		];
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
		$clinics = $clinics->where('name', 'LIKE', $joinclinic.'%')
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNull('self_deleted')
		->take(10)
		->get();

		$clinic_list = [];
		foreach ($clinics as $clinic) {
			$clinic_list[] = [
				'name' => $clinic->getNames(),
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

        $dentists = $dentists->where('name', 'LIKE', $invitedentist.'%')
		->whereIn('status', config('dentist-statuses.shown_with_link'))
		->whereNull('self_deleted')
		->take(10)
		->get();

		$dentist_list = [];
		foreach ($dentists as $dentist) {
			$dentist_list[] = [
				'name' => $dentist->getNames(),
				'id' => $dentist->id,
			];
		}

		return Response::json($dentist_list);
	}
}