<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Helpers\GeneralHelper;
use App\Helpers\TrpHelper;

use App\Models\Continent;
use App\Models\Country;
use App\Models\PageSeo;
use App\Models\User;

use Request;
use Route;
use App;

class DentistsController extends FrontController {

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        // Argentina - 1
        // Austria - 15
        // Belgium - 22
        // Brazil - 31
        // Canada - 39
        // China - 45
        // Ethiopia - 69
        // Germany - 81
        // India - 101
        // Malaysia - 132
        // Mexico - 141
        // Nigeria - 160
        // Pakistan - 166
        // Russia - 181
        // Switzerland - 212
        // United Arab Emirates - 230
        // USA - 232
        // Venezuela - 237
        $this->countriesWithStates = [1, 15, 22, 31, 39, 45, 69, 81, 101, 132, 141, 160, 166, 181, 212, 230, 232, 237];
    }

    private function getCorrectedQuery($query, $filter) {

        $query = trim(urldecode($query));
        $replacedSymbolsQuery = mb_strtolower(str_replace([',', "'", ' ', '.'], ['', '', '-', ''], $query ));

        if(!empty($filter)) {
            $corrected_query = $replacedSymbolsQuery.'/'.$filter;
        } else {
            $corrected_query = 'dentists/'.$replacedSymbolsQuery;
        }

        return $corrected_query;
    }

    /**
     * search dentists
     */
    public function search($locale=null, $query=null, $filter=null, $page=null, $ajax=null) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        App::setLocale( 'en' );
        $this->current_page = 'dentists';

        if (empty($query)) {
            return redirect( getLangUrl('page-not-found') );
        }

        if($query=='worldwide') {
            return redirect( getLangUrl('dentists/partners/'), 301);
        }
        
        if($query == 'north-macedonia') {
            return redirect( getLangUrl('dentists/macedonia/'), 301);
        }
        
        $corrected_query = $this->getCorrectedQuery($query, $filter);
        $canonical = $corrected_query;
        
        if (urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) {
            return redirect( getLangUrl($corrected_query).($_GET ? '?'.http_build_query($_GET) : ''));
        }

        $formattedAddress = $query;
        $lat = false;
        $lon = false;
        $parsedAddress = null;
        $nonCannonicalUrl = true;
        $dentistsFromDifferentCountry = false;
        $searchCity = Request::input('city') ? trim(explode(',', trim(Request::input('city')))[0]) : '';
        
        $searchCountry = Request::input('country');
        if(!empty($searchCountry)) {
            if(intval($searchCountry) > 0) {
                //get country name from id
                $searchCountry = Country::find($searchCountry);
                $searchCountry = $searchCountry ? $searchCountry->name : '';
            } else {
                $searchCountryQuery = Country::whereHas('translations', function ($query) use ($searchCountry) {
                    $query->where('name', 'LIKE', $searchCountry.'%');
                })->first();

                if(!empty($searchCountryQuery)) {
                    $searchCountry = $searchCountryQuery->name;
                }
            }
        }

        if($query=='partners') {
            // https://reviews.dentacoin.com/en/dentists/partners/

            $items = User::where('is_dentist', 1)
            ->whereIn('status', config('dentist-statuses.shown_with_link'))
            ->whereNull('self_deleted')
            ->where('is_partner', 1);

            $dentistPartners = clone $items;

            if(!empty($searchCountry) || !empty($searchCity)) {
                $this->filterDentistsByCountryCity($items, $lat, $lon, $searchCountry, $searchCity);
            } else {
                $lat = 30;
                $lon = 0;
            }

            $dentistByCityCountry = clone $items;
            $dentistByCityCountry = $dentistByCityCountry->get();

            if($dentistByCityCountry->isEmpty() && $dentistPartners->get()->isNotEmpty()) {
                $items = $dentistPartners;
                $dentistsFromDifferentCountry = true;
            }

        } else if($filter && ($filter == 'all-results' || session('results-url'))) {
            //when there is filter for specializations -> the link is changing, so a session is needed (session('results-url'))
            //filter options -> "all results" and specializations filters checked (https://reviews.dentacoin.com/en/{name_search_field}/all-results/ && https://reviews.dentacoin.com/en/{name_search_field || country/city}/cosmetic-dentists/)


            //search dentist by params - name, country, city, partners
            $searchSplitedUsername = explode('-', $query);
            $dentistByName = TrpHelper::searchDentistsByName($searchSplitedUsername);
            
            $items = clone $dentistByName;

            if(!empty($searchCountry) || !empty($searchCity)) {
                $this->filterDentistsByCountryCity($items, $lat, $lon, $searchCountry, $searchCity);
            }

            //search if there are dentists with search name, but country/city is different
            $dentistByCityCountry = clone $items;
            $dentistByCityCountry = $dentistByCityCountry->get();
            $dentistByNameCollection = clone $dentistByName;

            if($dentistByCityCountry->isEmpty() && $dentistByNameCollection->get()->isNotEmpty()) {
                $items = $dentistByName;
                $dentistsFromDifferentCountry = true;
            }
        } else {
            //search dentists in country/state/city
            $items = User::with(['categories', 'country.translations'])->where('is_dentist', 1)
            ->whereIn('status', config('dentist-statuses.shown_with_link'))
            ->whereNull('self_deleted');

            $query = str_replace('-', ' ', $query);
            
            $this->getFormatedAddress([
                'address' => $query,
            ], $parsedAddress, $formattedAddress, $lat, $lon, $corrected_query, $query, $filter, $setLatLon = true);
            
            if(empty($lat) || empty($lon)) {
                return redirect( getLangUrl('page-not-found') );
            }

            if ((urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) && App::getLocale().'/'.$corrected_query != 'en/dentists/federal-capital-territory-nigeria') {
                $geores = $this->getFormatedAddress([
                    'latlng' => $lat.','.$lon,
                ], $parsedAddress, $formattedAddress, $lat, $lon, $corrected_query, $query, $filter);

                if (urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) {
                    if( $geores->results && $geores->results[0]->place_id ) {
                        $this->getFormatedAddress([
                            'address' => $geores->results[0]->formatted_address,
                        ], $parsedAddress, $formattedAddress, $lat, $lon, $corrected_query, $query, $filter);
                        
                        if (urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) {
                            return redirect( getLangUrl($corrected_query).($_GET ? '?'.http_build_query($_GET) : '') );
                        }
                    } else {
                        return redirect( getLangUrl($corrected_query).($_GET ? '?'.http_build_query($_GET) : '') );
                    }
                }
            }

            if (
                (empty($parsedAddress['city_name']) && empty($parsedAddress['state_name']) && !empty($parsedAddress['country_name'])) 
                || $query == 'ireland' 
                || (!empty($parsedAddress['country_name']) && $parsedAddress['country_name'] == 'North Macedonia')) {
                //search only in country

                $countriesWithDiffNames = [
                    'North Macedonia' => 129,
                    'South Korea' => 116,
                    'North Korea' => 116,
                    'Vietnam' => 238,
                    'Czechia' => 58,
                    'Iran' => 103,
                ];
                
                $country_n = $parsedAddress['country_name'] ?? $query;
                if(array_key_exists($country_n, $countriesWithDiffNames)) {
                    $country = Country::find($countriesWithDiffNames[$country_n]);
                } else {
                    $country = Country::with('translations')->whereHas('translations', function ($query) use ($country_n) {
                        $query->where('name', 'LIKE', $country_n);
                    })->first();
                }

                $items->where('country_id', $country->id);

            } else if(!empty($parsedAddress['city_name']) && !empty($parsedAddress['state_name']) && !empty($parsedAddress['country_name'])) {
                //search for city, state, country
                $isValid = User::where('city_name', 'like', $parsedAddress['city_name'])
                ->where('state_slug', 'like', $parsedAddress['state_slug'])
                ->count();

                $searchOnlyState = false;
                if(!$isValid) {
                    $isValid = User::where('state_slug', 'like', $parsedAddress['state_slug'])->get();

                    if($isValid) {
                        $searchOnlyState = true;
                    }
                }

                if($isValid) {
                    $nonCannonicalUrl = false;

                    if($searchOnlyState) {
                        $items->where('state_slug', 'like', $parsedAddress['state_slug']);
                    } else {
                        $items->where('city_name', 'like', $parsedAddress['city_name'])
                        ->where('state_slug', 'like', $parsedAddress['state_slug']);
                    }
                }
            } else if(!empty($parsedAddress['state_name']) && !empty($parsedAddress['country_name'])) {
                //search for state, country
                $isValid = User::where('state_slug', 'like', $parsedAddress['state_slug'])->count();
                if($isValid) {
                    $items->where('state_slug', 'like', $parsedAddress['state_slug']);

                    $nonCannonicalUrl = false;
                }
            } else {
                //if empty city, state, country
                list($range_lat, $range_lon) = $this->getRadiusInLatLon(50, $lat);
                $items->whereBetween('lat', [$lat-$range_lat, $lat+$range_lat]);
                $items->whereBetween('lon', [$lon-$range_lon, $lon+$range_lon]);
            }
        }

        

        //------ FILTERS ----------
        $requestTypes = Request::input('types');

        if($query == 'partners') {
            if(!empty($requestTypes)) {
                if(!in_array('is_partner', $requestTypes)) {
                    $requestTypes[] = 'is_partner';
                }
            } else {
                $requestTypes = [
                    'is_partner',
                ];
            }
        } else {
            if(empty($requestTypes)) {
                $requestTypes = [
                    'all',
                ];
            }
        }

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
        if( !empty($filter) && $filter!='all-results') {
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
        

        //------ COUNT FILTERS RESULTS ----------
        $dentists = clone $items;
        $dentists = $dentists->get();

        $dentistSpecialications = [];
        $dentistTypes = [
            'all' => $dentists->count(),
            'is_dentist' => 0,
            'is_clinic' => 0,
            'is_partner' => 0,
            'top_dentist_month' => 0,
        ];
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

            if($dentist->is_clinic) {
                $dentistTypes['is_clinic'] += 1;
            } else if($dentist->is_dentist) {
                $dentistTypes['is_dentist'] += 1;
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

        if($filter == 'all-results') {
            //if specializations are checked, then unchecked -> to return to the same link
            session([
                'results-url' => $corrected_query
            ]);
        }

        //set seo & titles
        if (!empty($query) || $filter == 'all-results') {
            $seos = PageSeo::find(26);

            $seo_title = str_replace(':location', $formattedAddress, $seos->seo_title);
            $seo_description = str_replace(':location', $formattedAddress, $seos->seo_description);
            $seo_description = str_replace(':dentists_number', $items->count(), $seo_description);

            $social_title = str_replace(':location', $formattedAddress, $seos->social_title);
            $social_description = str_replace(':location', $formattedAddress, $seos->social_description);
            $social_description = str_replace(':dentists_number', $items->count(), $social_description);

            $social_image = $seos->getImageUrl();

            // $search_title = trans('trp.page.search.location.title', [
            //     'location' => '<span class="mont subtitle">'.$formattedAddress.'</span>',
            // ]);
            if($filter == 'all-results') {
                if($searchCountry && !$dentistsFromDifferentCountry) {
                    $search_title = 'Check all ‘'.$formattedAddress.'’ practices in <span class="mont subtitle">'.ucwords($searchCity ? $searchCity.', ' : '').$searchCountry.'</span>';
                } else if($searchCity && !$dentistsFromDifferentCountry) {
                    $search_title = 'Check all ‘'.$formattedAddress.'’ practices in <span class="mont subtitle">'.$searchCity.'</span>';
                } else {
                    $search_title = 'Check all ‘'.$formattedAddress.'’ practices';
                }
            } else {
                if($formattedAddress == 'partners') {

                    if($searchCountry && !$dentistsFromDifferentCountry) {
                        $search_title = 'Check all Dentacoin Partners in <span class="mont subtitle">'.ucwords($searchCity ? $searchCity.', ' : '').$searchCountry.'</span>';
                    } else if($searchCity && !$dentistsFromDifferentCountry) {
                        $search_title = 'Check all Dentacoin Partners in <span class="mont subtitle">'.$searchCity.'</span>';
                    } else {
                        $search_title = 'Check Dentacoin Partners <span class="mont subtitle">worldwide</span>';
                    }
                } else {
                    $search_title = 'Find The Best Dental Experts in <span class="mont subtitle">'.$formattedAddress.'</span>';
                }
            }
        }

        //set seo & titles
        if (!empty($filter) && $filter != 'all-results') { //with specializations
            $searchCategories = explode('-', $filter);
            foreach($searchCategories as $k => $v) {
                if($v=='implants' || $v=='dentists') {
                    $searchCategories[($k-1)] = $searchCategories[($k-1)].'-'.$v;
                    unset($searchCategories[$k]);
                }
            }
            $categoryNames = [];
            foreach ($searchCategories as $slug) {
                if(array_key_exists($slug, $this->categories_dentists)) {
                    $categoryNames[] = $this->categories_dentists[$slug];
                }
            }

            $seos = PageSeo::find(27);

            $seo_title = str_replace(':location', $formattedAddress, $seos->seo_title);
            $seo_title = str_replace(':category', implode(', ', $categoryNames), $seo_title);

            $seo_description = str_replace(':location', $formattedAddress, $seos->seo_description);
            $seo_description = str_replace(':category', implode(', ', $categoryNames), $seo_description);
            $seo_description = str_replace(':results_number', $items->count(), $seo_description);

            $social_title = str_replace(':location', $formattedAddress, $seos->social_title);
            $social_title = str_replace(':category', implode(', ', $categoryNames), $social_title);

            $social_description = str_replace(':location', $formattedAddress, $seos->social_description);
            $social_description = str_replace(':category', implode(', ', $categoryNames), $social_description);
            $social_description = str_replace(':results_number', $items->count(), $social_description);

            $social_image = $seos->getImageUrl();
            $search_title = 'The Best '.implode(', ', $categoryNames).' in <span class="mont subtitle">'.$formattedAddress.'</span>';

            // $search_title = trans('trp.page.search.location.category.title', [
            //     'location' => '<span class="mont subtitle">'.$formattedAddress.'</span>',
            //     'category' => implode(', ', $categoryNames),
            // ]);
        }

		return $this->ShowView('search-results', [
            'search_title' => !empty($search_title) ? $search_title : null,
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => !empty($social_image) ? $social_image : null,
            
            'dentistSpecialications' => $dentistSpecialications,
            'dentistTypes' => $dentistTypes,
            'requestTypes' => $requestTypes,
            'types' => [
                'all' => 'All',
                'is_dentist' => 'Dentists',
                'is_clinic' => 'Clinics',
                'is_partner' => 'Dentacoin Partners',
                'top_dentist_month' => 'Top Dentists',
            ],
            'dentistRatings' => $dentistRatings,
            'requestRatings' => $requestRatings,
            'ratings' => [
                5 => 'Above 4 stars',
                4 => 'Above 3 stars',
                3 => 'Above 2 stars',
                2 => 'Above 1 stars',
            ],

            'languages' => [
                'en' => 'English',
                'gr' => 'German',
                'it' => 'Italian',
                'es' => 'Spanish',
                'fr' => 'French',
            ],

            'experiences' => [
                'under_five' => 'Less than 5 years',
                'under_ten' => '5-10 years',
                'over_ten' => '10+ years',
            ],
            
            'dentistAvailability' => $dentistAvailability,
            'requestAvailability' => $requestAvailability,
            'availabilities' => [
                'early_morning' => 'Early morning • Starts before 10 am',
                'morning' => 'Morning • Starts before 12 pm',
                'afternoon' => 'Afternoon • Starts after 12 pm',
                'evening' => 'Evening • Starts after 5 pm',
            ],

            'requestOrder' => $requestOrder,
            'orders' => $orders,

            'filter' => $filter,
            'formattedAddress' => $formattedAddress,
            'canonical' => $canonical,
            'dentistsFromDifferentCountry' => $dentistsFromDifferentCountry,
            'searchCountry' => $searchCountry,
            'searchCity' => $searchCity,
            'lat' => !empty($lat) ? $lat : 0,
            'lon' => !empty($lon) ? $lon : 0,
            'query' => $corrected_query,
			'items' => $items,
            'searchCategories' => $searchCategories,
            'noIndex' => $nonCannonicalUrl || !$items->count(),
            'css' => [
                'trp-search-results.css',
            ],
            'js' => [
                'search-results.js',
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
		]);
    }

    private function getFormatedAddress($GMparams, &$parsedAddress, &$formattedAddress, &$lat, &$lon, &$corrected_query, $query, $filter, $setLatLon = false) {
        
        $geores = \GoogleMaps::load('geocoding')->setParam($GMparams)->get();
        $geores = json_decode($geores);
        
        if(!empty($geores->results[0]->geometry->location)) {
            $parsedAddress = GeneralHelper::parseAddress( $geores->results[0]->address_components );

            $withStateInUrl = false;
            if(!empty($parsedAddress['country_name'])) {
                $country_n = $parsedAddress['country_name'];
                $country = Country::with('translations')->whereHas('translations', function ($query) use ($country_n) {
                    $query->where('name', 'LIKE', $country_n);
                })->first();

                if(!empty($country) && in_array($country->id, $this->countriesWithStates)) {
                    $withStateInUrl = true;
                }
            }

            $formattedAddress = !empty($parsedAddress['city_name']) ? $parsedAddress['city_name'] : '';
            if(!empty($parsedAddress['city_name']) && (!empty(!empty($parsedAddress['state_name'])) || !empty($parsedAddress['country_name']))) {
                $formattedAddress .= ', ';
            }
            if($withStateInUrl) {
                $formattedAddress .= !empty($parsedAddress['state_name']) ? $parsedAddress['state_name'] : '';
                if(!empty($parsedAddress['state_name']) && !empty($parsedAddress['country_name'])) {
                    $formattedAddress .= ', ';
                }
            }
            $formattedAddress .= !empty($parsedAddress['country_name']) ? $parsedAddress['country_name'] : '';

            if($setLatLon) {
                $lat = $geores->results[0]->geometry->location->lat;
                $lon = $geores->results[0]->geometry->location->lng;
            }
        }

        if($setLatLon) {
            if(!empty($formattedAddress) && $formattedAddress != 'Ega Denmark ') {
                $firstParam = $formattedAddress;
            } else {
                $firstParam = $query;
            }
        } else {
            $firstParam = $formattedAddress;
        }
        
        $corrected_query = $this->getCorrectedQuery($firstParam, $filter);
        $corrected_query = iconv('UTF-8', 'ASCII//TRANSLIT', $corrected_query);
        $corrected_query = iconv('ASCII', 'UTF-8', $corrected_query);

        return $geores;
    }

    private function filterDentistsByCountryCity(&$items, &$lat, &$lon, $searchCountry, $searchCity) {
        if(!empty($searchCountry)) {
            //search by country
            $items = $items->whereHas('country', function($query2) use ($searchCountry) {
                $query2->where('name', 'LIKE', '%'.$searchCountry.'%');
            });
        }

        if(!empty($searchCity)) {
            //search by city
            $items = $items->where('city_name', 'LIKE', '%'.$searchCity.'%');
        }

        //set map bounds
        $geores = \GoogleMaps::load('geocoding')
        ->setParam(['address' => ($searchCity ?? '').'-'.($searchCountry ?? $searchCountry)])
        ->get();
        $geores = json_decode($geores);
        
        if(!empty($geores->results[0]->geometry->location)) {
            $lat = $geores->results[0]->geometry->location->lat;
            $lon = $geores->results[0]->geometry->location->lng;
        }
    }

    private function getRadiusInLatLon($km, $lat) {
        $lat = ($km / 6378) * (180 / pi());
        $lon = ($km / 6378) * (180 / pi()) / cos($lat * pi()/180);
        return [$lat, $lon];        
    }

    /**
     * search dentist by country
     */
    public function country($locale=null) {

        $continentDentists = [];
		$countriesAlphabetically = [];//create a new array
        $countAllDentists = 0;
        $countries = Country::has('dentists')->with(['translations','dentists'])->get();

        foreach( $countries as $item) {
            $countriesAlphabetically[$item->name[0]][] = [
				'name' => $item->name,
				'slug' => $item->slug,
				'dentist_count' => $item->dentists->count(),
				'id' => $item->id,
				'code' => $item->code,
				'continent' => $item->continent_id,
			];

            if(!isset($continentDentists[$item->continent_id])) {
                $continentDentists[$item->continent_id] = 0; 
            }
            $continentDentists[$item->continent_id] += $item->dentists->count();
            $countAllDentists+=$item->dentists->count();
        }

        $seos = PageSeo::find(28);

        $seo_title = $seos->seo_title;

        $seo_description = str_replace(':countries_number', $countries->count(), $seos->seo_description);
        $seo_description = str_replace(':listings_number', $countAllDentists, $seo_description);

        $social_title = $seos->social_title;
        $social_description = $seos->social_description;

        return $this->ShowView('search-country', array(            
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => $seos->getImageUrl(),
            'continents' => Continent::where('id', '!=', 7)->get(),
            'countriesAlphabetically' => $countriesAlphabetically,
            'continentDentists' => $continentDentists,
            'css' => [
                'trp-search-dentists.css',
            ],
            'js' => [
                'search-dentist-by.js'
            ],
        ));
    }

    /**
     * search dentist by state
     */
    public function state($locale=null, $country_slug) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        $countryWithStates = Country::with('translations')
        ->whereIn('id', $this->countriesWithStates)
        ->where('slug', 'like', $country_slug )
        ->first();

        $country = Country::with('translations')
        ->where('slug', 'like', $country_slug )
        ->first();

        //if country exists
        if(empty($country)) {
            return redirect('page-not-found');
        }

        //if country doesn't have states
        if(empty($countryWithStates)) {
            return $this->city($locale, $country_slug);
        } else {
            
            $usersStates = User::selectRaw('state_name, state_slug, COUNT(state_name) as cnt')
            ->where('is_dentist', 1)
            ->whereNull('self_deleted')
            ->whereIn('status', config('dentist-statuses.shown_with_link'))
            ->where('country_id', $country->id)
            ->whereNotNull('state_name')
            ->whereNotNull('city_name')
            ->groupBy('state_name')
            ->orderBy('state_name', 'asc')
            ->get()
            ->toArray();

            $states = [];
            foreach($usersStates as $state) {
                $states[$state['state_name'][0]][] = $state;
            }
    
            $countryDentistsCount = User::where('is_dentist', 1)
            ->whereNull('self_deleted')
            ->whereIn('status', config('dentist-statuses.shown_with_link'))
            ->where('country_id', $country->id)
            ->whereNotNull('state_name')
            ->whereNotNull('city_name')
            ->count();
    
            $seos = PageSeo::find(29);
            $seo_title = str_replace(':country', $country->name, $seos->seo_title);
            $seo_description = str_replace(':country', $country->name, $seos->seo_description);
            $seo_description = str_replace(':states_number', count($states), $seo_description);
            $social_title = str_replace(':country', $country->name, $seos->social_title);
            $social_description = str_replace(':country', $country->name, $seos->social_description);
            $social_description = str_replace(':results_number', $countryDentistsCount, $social_description);
    
            $main_title = trans('trp.page.search.city-title', [ 'country' => $country->name]);
    
            return $this->ShowView('search-state', array(            
                'seo_title' => !empty($seo_title) ? $seo_title : null,
                'seo_description' => !empty($seo_description) ? $seo_description : null,
                'social_title' => !empty($social_title) ? $social_title : null,
                'social_description' => !empty($social_description) ? $social_description : null,
                'social_image' => $seos->getImageUrl(),
                'countryDentistsCount' => $countryDentistsCount,
                'states' => $states,
                'country' => $country,
                'main_title' => $main_title,
                'noIndex' => !count($states),
                'css' => [
                    'trp-search-dentists.css',
                ],
            ));
        }
    }

    /**
     * search dentist by city
     */
    public function city($locale=null, $country_slug, $state_slug=null) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        $country = Country::where('slug', 'like', $country_slug )->first();

        if(empty($country)) {
            return redirect('page-not-found');
        }

        $userCities = User::selectRaw('city_name, state_name, state_slug, COUNT(city_name) as cnt')
        ->where('is_dentist', 1)
        ->whereNull('self_deleted')
        ->whereIn('status', config('dentist-statuses.shown_with_link'))
        ->where('country_id', $country->id)
        ->whereNotNull('city_name');
        if(!empty($state_slug)) {
            $userCities = $userCities->where('state_slug', 'like', $state_slug);
        }
        $userCities = $userCities->groupBy('city_name')
        ->orderBy('city_name', 'asc')
        ->get();

        $stateName = $userCities->first()->state_name;
        $userCities = $userCities->toArray();

        if(!empty($state_slug)) {
            $countryCount = User::where('is_dentist', 1)
            ->whereNull('self_deleted')
            ->whereIn('status', config('dentist-statuses.shown_with_link'))
            ->where('country_id', $country->id)
            ->where('state_slug', 'like', $state_slug)
            ->whereNotNull('state_name')
            ->whereNotNull('city_name')
            ->count();
        } else {
            $countryCount = $country->dentists->count();
        }

        $cities = [];
        foreach($userCities as $city) {
            $cities[$city['city_name'][0]][] = $city;
        }

        $all_dentists = User::where('is_dentist', 1)
        ->whereNull('self_deleted')
        ->whereIn('status', config('dentist-statuses.shown_with_link'))
        ->where('country_id', $country->id);
        if(!empty($state_slug)) {
            $all_dentists = $all_dentists->where('state_slug', 'like', $state_slug);
        }
        $all_dentists = $all_dentists->whereNotNull('city_name')
        ->count();

        $seos = PageSeo::find(30);
        $seo_title = str_replace(':country', $country->name, $seos->seo_title);
        $seo_description = str_replace(':country', $country->name, $seos->seo_description);
        $seo_description = str_replace(':cities_number', count($cities), $seo_description);
        $social_title = str_replace(':country', $country->name, $seos->social_title);
        $social_description = str_replace(':country', $country->name, $seos->social_description);
        $social_description = str_replace(':results_number', $all_dentists, $social_description);

        return $this->ShowView('search-city', array(
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => $seos->getImageUrl(),
            'cities' => $cities,
            'country' => $country,
            'stateName' => $stateName,
            'state_slug' => $state_slug,
            'countryCount' => $countryCount,
            'noIndex' => !count($cities),
            'css' => [
                'trp-search-dentists.css',
            ],
        ));
    }
}