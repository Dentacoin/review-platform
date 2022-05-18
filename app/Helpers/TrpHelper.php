<?php

namespace App\Helpers;

use App\Models\User;

use Request;

class TrpHelper {

    public static function searchDentistsByName($searchSplitedUsername) {

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

        return $dentistsAndClinics;
    }

    public static function detectWebsitePlatform($url) {

		foreach(config('trp.social_network') as $k => $v) {
			if(str_contains(strtolower($url), $k.'.com')) {
				return $k;
			}
		}
		
        if(str_contains(strtolower($url), 'vk.com')) {
			return 'vkontakte';
		}

		return false;
        
    }

	public static function filterDentists(&$items, $requestTypes, $filter, $forBranch=false) {

		//search for type
		if(!empty($requestTypes)) {
			foreach($requestTypes as $requestType) {
				if($requestType == 'top_dentist_month') {
					$items = $items->whereNotNull($requestType);
				} else if(in_array($requestType, ['is_dentist', 'is_clinic']) && in_array('is_dentist', $requestTypes) && in_array('is_clinic', $requestTypes)) {
					//show dentists and clinics
					$items = $items->where('is_dentist', 1);
				} else if($requestType == 'is_dentist' && !in_array('is_clinic', $requestTypes)) {
					//show only clinics
					$items = $items->where($requestType, 1)->where('is_clinic', 0);
				} else if($requestType != 'all') {
					$items = $items->where($requestType, 1);
				}
			}
		}

		$requestRatings = Request::input('ratings');
		//search for rating
		if(!empty($requestRatings)) {
			$stars = 5;
			foreach($requestRatings as $requestRating) {
				if($requestRating < $stars) {
					$stars = $requestRating;
				}
			}
			$items = $items->where('avg_rating', '>=', $stars);
		}

		$orders = [
			'name_asc' => 'Name (A-Z)',
			'name_desc' => 'Name (Z-A)',
			'avg_rating_desc' => 'Stars (highest first)',
			'avg_rating_asc' => 'Stars (lowest first)',
			'ratings_desc' => 'Most reviews',
			'ratings_asc' => 'Least reviews',
		];

		//order dentists by
		$requestOrder = Request::input('order');

		if($requestOrder && array_key_exists($requestOrder, $orders)) {
			$field = explode('_', $requestOrder);
			if(count($field) > 2) {
				$items = $items->orderBy('avg_rating', $field[2]);
			} else {
				$items = $items->orderBy($field[0], $field[1]);
			}
		} else {
			$requestOrder = 'ratings_desc';
			$items = $items->orderBy('ratings', 'DESC');
		}


		$searchCategories = null;
		if($forBranch) {
			$searchCategories = Request::input('specs');
			
			if($searchCategories) {
				foreach ($searchCategories as $cat) {
					$cat_id = array_search($cat, config('categories'));
					$items = $items->whereHas('categories', function ($q) use ($cat_id) {
						$q->where('category_id', $cat_id);
					});
				}
			}
		} else {
			if(!empty($filter) && $filter!='all-results') {
				$searchCategories = explode('-', $filter);
	
				foreach($searchCategories as $k => $v) {
					if($v=='implants' || $v=='dentists') {
						$searchCategories[($k-1)] = $searchCategories[($k-1)].'-'.$v;
						unset($searchCategories[$k]);
					}
				}
	
				foreach ($searchCategories as $cat) {
					$cat_id = array_search($cat, config('categories'));
					$items = $items->whereHas('categories', function ($q) use ($cat_id) {
						$q->where('category_id', $cat_id);
					});
				}
			}
		}		
		

		//------ COUNT FILTERS RESULTS ----------
		$dentists = clone $items;
		$dentists = $dentists->get();

		$dentistSpecialications = [];
		$dentistTypes = [
			'all' => $dentists->count(),
		];
		if(!$forBranch) {
			$dentistTypes['is_dentist'] = 0;
			$dentistTypes['is_clinic'] = 0;
		}

		$dentistTypes['is_partner'] = 0;
		$dentistTypes['top_dentist_month'] = 0;

		$types = [
			'all' => 'All',
		];

		if(!$forBranch) {
			$types['is_dentist'] = 'Dentists';
			$types['is_clinic'] = 'Clinics';
		}

		$types['is_partner'] = 'Dentacoin Partners';
		$types['top_dentist_month'] = 'Top Dentists';

		// dd($dentistTypes);

		$dentistRatings = [
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
			0 => 0,
		];
		$dentistAvailability = [
			'early_morning' => 0,
			'morning' => 0,
			'afternoon' => 0,
			'evening' => 0,
		];
		
		foreach($dentists as $dentist) {
			if($dentist->categories->isNotEmpty()) {
				foreach($dentist->categories as $cat) {

					if(!isset($dentistSpecialications[$cat->category_id])) {
						$dentistSpecialications[$cat->category_id] = 0;
					}
					$dentistSpecialications[$cat->category_id] += 1;
				}
			}

			if(!$forBranch) {
				if($dentist->is_clinic) {
					$dentistTypes['is_clinic'] += 1;
				} else if($dentist->is_dentist) {
					$dentistTypes['is_dentist'] += 1;
				}
			}

			if($dentist->is_partner) {
				$dentistTypes['is_partner'] += 1;
			}
			if($dentist->top_dentist_month) {
				$dentistTypes['top_dentist_month'] += 1;
			}

			foreach($dentistRatings as $k => $r) {
				$d_rating = $dentist->avg_rating;
				if($d_rating >= $k) {
					$dentistRatings[$k] += 1;
				}
			}

			$workHours = is_array($dentist->work_hours) ? $dentist->work_hours : json_decode($dentist->work_hours, true);

			if($workHours) {
				foreach($dentistAvailability as $k => $availability ) {

					foreach($workHours as $workHour) {
						if($k == 'early_morning') {
							if(intval($workHour[0]) < 10) {
								$dentistAvailability[$k] += 1;
								break;
							}
						} else if($k == 'morning') {
							if(intval($workHour[0]) < 12) {
								$dentistAvailability[$k] += 1;
								break;
							}
						} else if($k == 'afternoon') {
							if(intval($workHour[0]) > 12) {
								$dentistAvailability[$k] += 1;
								break;
							}
						} else if($k == 'evening') {
							if(intval($workHour[0]) > 17) {
								$dentistAvailability[$k] += 1;
								break;
							}
						}
					}
				}
			}
		}

		//get results after filters
		$items = $items->get();


		//filter work hours
		$requestAvailability = Request::input('availability');

		if(!empty($requestAvailability)) {
			$itemsWithAvailability = collect();

			foreach($items as $item) {
				$workHours = is_array($item->work_hours) ? $item->work_hours : json_decode($item->work_hours, true);

				if($workHours) {
					foreach($requestAvailability as $availability ) {

						foreach($workHours as $workHour) {
							if($availability == 'early_morning') {
								if(intval($workHour[0]) < 10) {
									$itemsWithAvailability[] = $item;
									break;
								}
							} else if($availability == 'morning') {
								if(intval($workHour[0]) < 12) {
									$itemsWithAvailability[] = $item;
									break;
								}
							} else if($availability == 'afternoon') {
								if(intval($workHour[0]) > 12) {
									$itemsWithAvailability[] = $item;
									break;
								}
							} else if($availability == 'evening') {
								if(intval($workHour[0]) > 17) {
									$itemsWithAvailability[] = $item;
									break;
								}
							}
						}
					}
				}
			}
			
			$items = collect();
			$items = $items->concat((object)$itemsWithAvailability);
		}

		return [
			'dentistSpecialications' => $dentistSpecialications,
			'dentistTypes' => $dentistTypes,
			'requestRatings' => $requestRatings,
			'dentistRatings' => $dentistRatings,
			'dentistAvailability' => $dentistAvailability,
			'requestAvailability' => $requestAvailability,
			'requestOrder' => $requestOrder,
			'searchCategories' => $searchCategories,
			'orders' => $orders,
			'types' => $types,
		];
	}
}