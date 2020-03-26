<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Illuminate\Support\Facades\Input;

use App\Models\UserPhoto;
use App\Models\Country;
use App\Models\PageSeo;
use App\Models\User;
use App\Models\City;

use GoogleMaps;
use Response;
use Request;
use App;

class DentistsController extends FrontController
{
    public function getCorrectedQuery($query, $filter) {
        $query = trim(urldecode($query));
        if(!empty($filter)) {
            $corrected_query = mb_strtolower(str_replace([',', "'", ' ', '.'], ['', '', '-'. ''], $query )).'/'.$filter;
        } else {
            $corrected_query = 'dentists/'.mb_strtolower(str_replace([',',  "'", ' ', '.'], ['', '', '-', ''], $query ));
        }

        return $corrected_query;
    }

    public function paginate($locale=null, $query=null, $filter=null, $page) {
        return $this->list($locale, $query, $filter, $page, true);
    }

    public function search($locale=null, $query=null, $filter=null, $page=null, $ajax=null) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        $this->current_page = 'dentists';

        if (empty($query)) {
            return redirect( getLangUrl('page-not-found') );
        }
        // $corrected_query = mb_strtolower(str_replace([',', ' '], ['', '-'], $query )).(!empty($filter) ? '/'.$filter : '');
        $corrected_query = $this->getCorrectedQuery($query, $filter);

        if (urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) {
            return redirect( getLangUrl($corrected_query) );
        }

        // $noAddress = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->whereNotNull('city_id')->whereNull('lat')->take(300)->get();
        // foreach ($noAddress as $user) {
        //     $query = $user->country->name.', '.$user->city->name.', '.($user->zip ? $user->zip.', ' : null).$user->address;
        //     echo $query.'<br/>';

        //     $geores = \GoogleMaps::load('geocoding')
        //     ->setParam ([
        //         'address'    => $query,
        //     ])
        //     ->get();

        //     $geores = json_decode($geores);
        //     $lat = $lon = null;
        //     if(!empty($geores->results[0]->geometry->location)) {
        //         $lat = $geores->results[0]->geometry->location->lat;
        //         $lon = $geores->results[0]->geometry->location->lng;
        //     }

        //     $user->lat = $lat;
        //     $user->lon = $lon;
        //     $user->save();

        //     echo $lat.' '.$lon.'<br/><br/>';
        // }
        // dd('ok');

        $items = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->whereNull('self_deleted');
        $mode = 'map';
        $formattedAddress = $query;
        $country_search = false;

        if($query=='worldwide') {
            request()->merge(['partner' => 1]);
            $lat = 30;
            $lon = 0;
        } else if($filter == 'all-results') {
            $items = $items->where(function($q) use ($query) {
                $q->where('name', 'LIKE', $query.'%')
                ->orWhere('name_alternative', 'LIKE', $query.'%');
            });
            $mode = 'name';
        } else {

            // if($filter) {
            //     $arr = explode(',', $filter);
            //     if(count($arr)==2 && parseFloat($arr[0]) && parseFloat($arr[1])) {
            //         $lat = parseFloat($arr[0]);
            //         $lon = parseFloat($arr[1]);
            //     }
            // }
            if(empty($lat) || empty($lon)) {
                $query = str_replace('-', ' ', $query);

                $geores = \GoogleMaps::load('geocoding')
                ->setParam ([
                    'address'    => $query,
                ])
                ->get();

                $geores = json_decode($geores);
                if(!empty($geores->results[0]->geometry->location)) {

                    $parsedAddress = User::parseAddress( $geores->results[0]->address_components );

                    $formattedAddress = !empty($parsedAddress['city_name']) ? $parsedAddress['city_name'].' ' : '';
                    $formattedAddress .= !empty($parsedAddress['state_name']) ? $parsedAddress['state_name'].' ' : '';
                    $formattedAddress .= !empty($parsedAddress['country_name']) ? $parsedAddress['country_name'].' ' : '';

                    $lat = $geores->results[0]->geometry->location->lat;
                    $lon = $geores->results[0]->geometry->location->lng;
                }
            }

            if(empty($lat) || empty($lon)) {
                return redirect( getLangUrl('page-not-found') );
            }

            $corrected_query = $this->getCorrectedQuery(!empty($formattedAddress) && $formattedAddress != 'Ega Denmark ' ? $formattedAddress : $query, $filter);
            $corrected_query = iconv('UTF-8', 'ASCII//TRANSLIT', $corrected_query);
            $corrected_query = iconv('ASCII', 'UTF-8', $corrected_query);

            if ((urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) && App::getLocale().'/'.$corrected_query != 'en/dentists/federal-capital-territory-nigeria' ) {
                $geores = \GoogleMaps::load('geocoding')
                ->setParam ([
                    'latlng'    => $lat.','.$lon,
                ])
                ->get();

                $geores = json_decode($geores);
                if(!empty($geores->results[0]->geometry->location)) {

                    $parsedAddress = User::parseAddress( $geores->results[0]->address_components );
                    $formattedAddress = !empty($parsedAddress['city_name']) ? $parsedAddress['city_name'].' ' : '';
                    $formattedAddress .= !empty($parsedAddress['state_name']) ? $parsedAddress['state_name'].' ' : '';
                    $formattedAddress .= !empty($parsedAddress['country_name']) ? $parsedAddress['country_name'].' ' : '';
                }

                $corrected_query = $this->getCorrectedQuery($formattedAddress, $filter);
                $corrected_query = iconv('UTF-8', 'ASCII//TRANSLIT', $corrected_query);
                $corrected_query = iconv('ASCII', 'UTF-8', $corrected_query);
                if (urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) {
                    
                    if( $geores->results && $geores->results[0]->place_id ) {

                        $geores = \GoogleMaps::load('geocoding')
                        ->setParam ([
                            'address'    => $geores->results[0]->formatted_address,
                        ])
                        ->get();

                        $geores = json_decode($geores);
                        if(!empty($geores->results[0]->geometry->location)) {

                            $parsedAddress = User::parseAddress( $geores->results[0]->address_components );
                            $formattedAddress = !empty($parsedAddress['city_name']) ? $parsedAddress['city_name'].' ' : '';
                            $formattedAddress .= !empty($parsedAddress['state_name']) ? $parsedAddress['state_name'].' ' : '';
                            $formattedAddress .= !empty($parsedAddress['country_name']) ? $parsedAddress['country_name'].' ' : '';
                        }

                        $corrected_query = $this->getCorrectedQuery($formattedAddress, $filter);
                        $corrected_query = iconv('UTF-8', 'ASCII//TRANSLIT', $corrected_query);
                        $corrected_query = iconv('ASCII', 'UTF-8', $corrected_query);
                        if (urldecode(Request::path()) != App::getLocale().'/'.$corrected_query) {
                            return redirect( getLangUrl($corrected_query) );
                        }
                    } else {
                        return redirect( getLangUrl($corrected_query) );
                    }
                }
            }

            if ((empty($parsedAddress['city_name']) && empty($parsedAddress['state_name']) && !empty($parsedAddress['country_name'])) || $query == 'ireland') {
                $country_n = !empty($parsedAddress['country_name']) ? $parsedAddress['country_name'] : $query;
                if ($country_n == 'Vietnam') {
                    $country = Country::find(238);
                } else if($country_n == 'South Korea' || $country_n == 'North Korea') {
                    $country = Country::find(116);
                } else if($country_n == 'Iran') {
                    $country = Country::find(103);
                } else if($country_n == 'Czechia') {
                    $country = Country::find(58);
                } else {
                    $country = Country::with('translations')->whereHas('translations', function ($query) use ($country_n) {
                        $query->where('name', 'LIKE', $country_n);
                    })->first();
                }


                // if (!empty($country) && !empty($country->id)) {
                // }

                $items->where('country_id', $country->id);

                $country_search = true;
                
                
            } else {
                list($range_lat, $range_lon) = $this->getRadiusInLatLon(50, $lat);
                $items->whereBetween('lat', [$lat-$range_lat, $lat+$range_lat]);
                $items->whereBetween('lon', [$lon-$range_lon, $lon+$range_lon]);
            }
        }

        // dd($parsedAddress);
        $nonCannonicalUrl = true;


        if( !empty($parsedAddress['city_name']) && !empty($parsedAddress['state_name']) && !empty($parsedAddress['country_name']) && !$country_search) {

            $isValid = User::where('city_name', 'like', $parsedAddress['city_name'])
            ->where('state_slug', 'like', $parsedAddress['state_slug'])
            ->count();

            if($isValid) {
                $nonCannonicalUrl = false;
            }

        }

        $page = max(1, $page);
        $ppp = 12;
        $sort = 'rating';
        $stars = null;
        $searchCategories = null;
        $partner = null;
        $orders = [
            'rating',
            'reviews',
        ];
        $order_to_field = [
            'rating' => 'avg_rating',
            'reviews' => 'ratings',
        ];
        if( Request::input('sort') && in_array( Request::input('sort'), $orders ) ) {
            $sort = Request::input('sort');
        } else {
            $sort = 'rating';
        }
        $items = $items->orderBy($order_to_field[$sort], 'DESC');


        if( Request::input('stars') ) {
            $stars = Request::input('stars');
            $items = $items->where('avg_rating', '>=', Request::input('stars'));
        }
        if( !empty($filter) && $filter != 'all-results') {
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


        // dd($searchCategories);
        //dd($categories);
        if( Request::input('partner') ) {
            $partner = true;
            $items = $items->where('is_partner', true);
        }

        $items = $items->with('country.translations')->get(); //->take($ppp)->skip( ($page-1)*$ppp )

        $zoom = $country_search ? 5 : ($query=='worldwide' ? 1 : 13);
        $size = $query=='worldwide' ? '670x288' : '670x188';

        $bounds_lon = null;
        $bounds_lat = null;
        $bounds_zoom = null;

        if (!$country_search && $query!='worldwide' ) {
            $max_lon = -300;
            $min_lon = 999;
            $max_lat = -300;
            $min_lat = 999;

            foreach ($items as $val) {
                if ($max_lon < $val->lon ) {
                    $max_lon = $val->lon;
                }
                if ($min_lon > $val->lon ) {
                    $min_lon = $val->lon;
                }
                if ($max_lat < $val->lat ) {
                    $max_lat = $val->lat;
                }
                if ($min_lat > $val->lat ) {
                    $min_lat = $val->lat;
                }
            }

            if (!empty($lon) && $max_lon < $lon ) {
                $max_lon = $lon;
            }
            if (!empty($lon) && $min_lon > $lon ) {
                $min_lon = $lon;
            }
            if (!empty($lat) && $max_lat < $lat ) {
                $max_lat = $lat;
            }
            if (!empty($lat) && $min_lat > $lat ) {
                $min_lat = $lat;
            }

            $bounds_lon = ($max_lon + $min_lon) / 2;
            $bounds_lat = ($max_lat + $min_lat) / 2;

            $bounds_zoom = 8;
            //dd($lonRange, $latRange);
        }

        // dd($lat, $lon);

        $staticmap = 'https://maps.googleapis.com/maps/api/staticmap?center='.($bounds_lat ? $bounds_lat : $lat).','.($bounds_lon ? $bounds_lon : $lon).'&zoom='.($bounds_zoom ? $bounds_zoom : $zoom).'&size='.$size.'&maptype=roadmap&key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk';
        $i=1;
        $foundOnMap = false;
        foreach ($items->where('address', '!=', '')->slice(0, 10) as $item) {
            $foundOnMap = true;
            $staticmap .= '&markers=color:blue%7Clabel:'.($i).'%7C'.$item->lat.','.$item->lon;
            $i++;
        }
        if(!$foundOnMap) {
            $staticmap = null;
        }

        $social_image = '';

        $search_title = '';
        if (!empty($query)) {
                //dd('with filter eg worldwide, gen dentists');

            $search_title = trans('trp.page.search.all-results.title', [
                'name' => $formattedAddress,
            ]);

            $seos = PageSeo::find(26);

            $seo_title = str_replace(':location', $formattedAddress, $seos->seo_title);

            $seo_description = str_replace(':location', $formattedAddress, $seos->seo_description);
            $seo_description = str_replace(':dentists_number', $items->count(), $seo_description);

            $social_title = str_replace(':location', $formattedAddress, $seos->social_title);

            $social_description = str_replace(':location', $formattedAddress, $seos->social_description);
            $social_description = str_replace(':dentists_number', $items->count(), $social_description);

            $social_image = $seos->getImageUrl();

            if($query=='worldwide') {
                $search_title = trans('trp.page.search.location.title-worldwide', [
                    'location' => $formattedAddress,
                ]);
            } else {
                $search_title = trans('trp.page.search.location.title', [
                    'location' => $formattedAddress,
                ]);
            }
        }

        if (!empty($filter)) {
            if($filter == 'all-results') {

                $seo_title = trans('trp.seo.all-results.title', [
                    'name' => $formattedAddress,
                ]);
                $seo_description = trans('trp.seo.all-results.description', [
                    'name' => $formattedAddress,
                    'results_number' => $items->count(),
                ]);
                $social_title = trans('trp.social.all-results.title', [
                    'name' => $formattedAddress,
                ]);
                $social_description = trans('trp.social.all-results.description', [
                    'name' => $formattedAddress,
                    'results_number' => $items->count(),
                ]);

                $search_title = trans('trp.page.search.all-results.title', [
                    'name' => $formattedAddress,
                ]);
            } else {
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

                // dd(implode(', ', $categoryNames));
                // implode(', ', $categoryNames)

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

                $search_title = trans('trp.page.search.location.category.title', [
                    'location' => $formattedAddress,
                    'category' => implode(', ', $categoryNames),
                ]);
            }
        }
       
		return $this->ShowView('search', [
            'search_title' => !empty($search_title) ? $search_title : null,
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => !empty($social_image) ? $social_image : null,
            'formattedAddress' => $formattedAddress,
            'canonical' => getLangUrl((empty($filter) ? 'dentists/' : '').str_replace([' ', "'"], ['-', ''], $query).(!empty($filter) ? '/'.$filter : '')),
            'worldwide' => $query=='worldwide',
            'zoom' => $query=='worldwide' ? 2 : 13,
            'mode' => $mode,
            'staticImageUrl' => $staticmap,
            'query' => $query,
            'lat' => !empty($lat) ? $lat : 0,
            'lon' => !empty($lon) ? $lon : 0,
			'items' => $items,
            'searchCategories' => $searchCategories,
            'stars' => $stars,
            'sort' => $sort,
            'partner' => $partner,
            'ppp' => $ppp,
            'page_num' => $page,
            'orders' => $orders,
            'is_ajax' => $ajax,
            'noIndex' => $nonCannonicalUrl || !$items->count(),
            'js' => [
                'search.js',
                'address.js'
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
		]);
    }


    private function getRadiusInLatLon($km, $lat) {
        $lat = ($km / 6378) * (180 / pi());
        $lon = ($km / 6378) * (180 / pi()) / cos($lat * pi()/180);
        return [$lat, $lon];        
    }

    function signMapsUrl($my_url_to_sign) {
        //parse the url
        $url = parse_url($my_url_to_sign);
         
        $secret = '0iMvsc024fwadkGXq1w4-7tCqQs=';
         
        $urlToSign =  $url['path'] . "?" . $url['query'];
                  
        // Decode the private key into its binary format
        $decodedKey = base64_decode(str_replace(array('-', '_'), array('+', '/'), $privatekey));
         
        // Create a signature using the private key and the URL-encoded
        // string using HMAC SHA1. This signature will be binary.
        $signature = hash_hmac("sha1", $urlToSign, $decodedKey,true);
         
        //make encode Signature and make it URL Safe
        $encodedSignature = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
         
        $originalUrl = $url['scheme'] . "://" . $url['host'] . $url['path'] . "?" . $url['query'];
        //print("Full URL: " . $originalUrl . "&signature=" . $encodedSignature);
         
        return $originalUrl.'&signature='.$encodedSignature;
    }


    public function country($locale=null) {

        
        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        $dentists = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->whereNotNull('country_id')->whereNotNull('city_name')->groupBy('country_id')->get()->pluck('country_id');

        $dentist_countries = Country::whereIn('id', $dentists )->get();

        $countries_groups = [];
        $letter = null;
        $letters = [];
        $total_rows = 0;

        foreach ($dentist_countries as $country) {
            $letter = $country->name[0];
            if(empty( $letters[$letter] )) {
                $total_rows++;
                $total_rows++;
                $letters[$letter] = true;
                $countries_groups[$total_rows] = $letter;
            }

            $total_rows++;
            $countries_groups[$total_rows] = $country;
        }

        $row_length = ceil($total_rows / 4);
        $breakpoints = [];
        $p=1;
        foreach ($countries_groups as $key => $dc) {
            //echo  $key.' - '.$row_length*$p.'<br/>'; 
            if($key > $row_length*$p ) {
                $breakpoints[] = $key;
                $p++;   
            }
        }

        $all_dentists = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->whereNotNull('country_id')->whereNotNull('city_name')->get();

        $seos = PageSeo::find(28);

        $seo_title = $seos->seo_title;

        $seo_description = str_replace(':countries_number', count($dentist_countries), $seos->seo_description);
        $seo_description = str_replace(':listings_number', count($all_dentists), $seo_description);

        $social_title = $seos->social_title;
        $social_description = $seos->social_description;

        return $this->ShowView('search-country', array(            
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => $seos->getImageUrl(),
            'countries_groups' => $countries_groups,
            'breakpoints' => $breakpoints,
            'js' => [
                'search.js'
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
        ));

    }

    public function city($locale=null, $country_slug, $state_slug) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        // $user = User::find(1592);

        // $info = User::validateAddress($user->country->name, $user->address);
        // if(is_array($info)) {
        //     echo 'country: '.$user->country->name.' | address: '.$user->address.' | city: '.$user->city_name.'<br/>';
        //     foreach ($info as $key => $value) {
        //         echo 'key-> '.$key.' | value-> '.$value.'<br/>';
        //         $user->$key = $value;
        //     }
        // }

        $country = Country::where('slug', 'like', $country_slug )->first();

        if(empty($country)) {
            return redirect('page-not-found');
        }

        $cities_name = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->where('country_id', $country->id)->where('state_slug', 'like', $state_slug)->whereNotNull('city_name')->groupBy('city_name')->orderBy('city_name', 'asc')->get();


        $all_dentists = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->where('country_id', $country->id)->where('state_slug', 'like', $state_slug)->whereNotNull('city_name')->count();

        $cities_groups = [];
        $letter = null;
        $letters = [];
        $total_rows = 0;

        foreach ($cities_name as $user) {
            $letter = $user->city_name[0];
            if(empty( $letters[$letter] )) {
                $total_rows++;
                $total_rows++;
                $letters[$letter] = true;
                $cities_groups[$total_rows] = $letter;

            }

            $total_rows++;
            $cities_groups[$total_rows] = $user;
        }

        $row_length = ceil($total_rows / 4); //19
        $breakpoints = [];
        $p=1;

        // echo 'Total: '.$total_rows.'<br/>';
        // var_dump($cities_groups);
        foreach ($cities_groups as $key => $dc) {
//             echo  $key.' - '.$row_length*$p.'
// '; 
            if($key >= $row_length*$p ) {
                $breakpoints[] = $key;
                $p++;   
            }
        }

        // var_dump($breakpoints);

        $seos = PageSeo::find(30);

        $seo_title = str_replace(':country', $country->name, $seos->seo_title);

        $seo_description = str_replace(':country', $country->name, $seos->seo_description);
        $seo_description = str_replace(':cities_number', count($cities_name), $seo_description);

        $social_title = str_replace(':country', $country->name, $seos->social_title);

        $social_description = str_replace(':country', $country->name, $seos->social_description);
        $social_description = str_replace(':results_number', $all_dentists, $social_description);

        return $this->ShowView('search-city', array(
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => $seos->getImageUrl(),
            'all_cities' => $cities_name,
            'cities_name' => $cities_groups,
            'breakpoints' => $breakpoints,
            'country' => $country,
            'total_rows' => $total_rows,
            'noIndex' => !count($cities_groups),
            'js' => [
                'search.js'
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
        ));
    }

    public function state($locale=null, $country_slug) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        // $user = User::find(68738);

        // $user->address = $user->address.'';
        // exit;

        $country = Country::where('slug', 'like', $country_slug )->first();

        if(empty($country)) {
            return redirect('page-not-found');
        }

        $states = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->where('country_id', $country->id)->whereNotNull('city_name')->groupBy('state_name')->orderBy('state_name', 'asc')->get();

        $all_dentists = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported','added_by_clinic_claimed','added_by_clinic_unclaimed'])->where('country_id', $country->id)->whereNotNull('city_name')->count();


        $states_groups = [];
        $letter = null;
        $letters = [];
        $total_rows = 0;

        foreach ($states as $user) {
            $letter = $user->state_name[0];
            if(empty( $letters[$letter] )) {
                $total_rows++;
                $total_rows++;
                $letters[$letter] = true;
                $states_groups[$total_rows] = $letter;

            }

            $total_rows++;
            $states_groups[$total_rows] = $user;
        }

        $row_length = ceil($total_rows / 4); //19
        $breakpoints = [];
        $p=1;

        foreach ($states_groups as $key => $dc) {

            if($key >= $row_length*$p ) {
                $breakpoints[] = $key;
                $p++;   
            }
        }

        $seos = PageSeo::find(29);

        $seo_title = str_replace(':country', $country->name, $seos->seo_title);

        $seo_description = str_replace(':country', $country->name, $seos->seo_description);
        $seo_description = str_replace(':states_number', count($states), $seo_description);

        $social_title = str_replace(':country', $country->name, $seos->social_title);

        $social_description = str_replace(':country', $country->name, $seos->social_description);
        $social_description = str_replace(':results_number', $all_dentists, $social_description);

        return $this->ShowView('search-state', array(            
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'social_image' => $seos->getImageUrl(),
            'states_name' => $states_groups,
            'breakpoints' => $breakpoints,
            'country' => $country,
            'total_rows' => $total_rows,
            'noIndex' => !count($states_groups),
            'js' => [
                'search.js'
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
        ));
    }


}