<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\City;
use App\Models\Country;
use GoogleMaps;
use App;


class DentistsController extends FrontController
{

    public function paginate($locale=null, $query=null, $filter=null, $page) {
        return $this->list($locale, $query, $filter, $page, true);
    }

    public function search($locale=null, $query=null, $filter=null, $page=null, $ajax=null) {
        $this->current_page = 'dentists';

        $corrected_query = mb_strtolower(str_replace([',', ' '], ['', '-'], $query )).(!empty($filter) ? '/'.$filter : '');

        // dd(Request::path());
        if (Request::path() != App::getLocale().'/'.$corrected_query) {

            return redirect( getLangUrl($corrected_query) );
        }


        // $noAddress = User::where('is_dentist', 1)->where('status', 'approved')->whereNotNull('city_id')->whereNull('lat')->take(300)->get();
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

        $items = User::where('is_dentist', 1)->where('status', 'approved');
        $mode = 'map';
        $formattedAddress = $query;

        if($query=='worldwide') {
            request()->merge(['partner' => 1]);
            $lat = 30;
            $lon = 0;
        } else if($filter == 'all-results') {
            $items = $items->where('name', 'like', $query.'%');
            $mode = 'name';
        } else {

            // if($filter) {
            //     $arr = explode(',', $filter);
            //     if(count($arr)==2 && parseFloat($arr[0]) && parseFloat($arr[1])) {
            //         $lat = parseFloat($arr[0]);
            //         $lon = parseFloat($arr[1]);
            //     }
            // }

            if(!$lat || !$lon) {

                $geores = \GoogleMaps::load('geocoding')
                ->setParam ([
                    'address'    => $query,
                ])
                ->get();

                $geores = json_decode($geores);
                if(!empty($geores->results[0]->geometry->location)) {
                    $formattedAddress = $geores->results[0]->formatted_address;
                    $lat = $geores->results[0]->geometry->location->lat;
                    $lon = $geores->results[0]->geometry->location->lng;
                }
            }

            if(!$lat || !$lon) {
                return redirect( getLangUrl('/') );
            }

            list($range_lat, $range_lon) = $this->getRadiusInLatLon(50, $lat);
            $items->whereBetween('lat', [$lat-$range_lat, $lat+$range_lat]);
            $items->whereBetween('lon', [$lon-$range_lon, $lon+$range_lon]);

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

        $items = $items->take(100)->get(); //->take($ppp)->skip( ($page-1)*$ppp )

        $zoom = $query=='worldwide' ? 1 : 13;
        $size = $query=='worldwide' ? '670x288' : '670x188';

        $staticmap = 'https://maps.googleapis.com/maps/api/staticmap?center='.$lat.','.$lon.'&zoom='.$zoom.'&size='.$size.'&maptype=roadmap&key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk';
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



        if (!empty($query)) {
            $seo_title = trans('trp.seo.location.title', [
                'location' => $formattedAddress,
                'dentists_number' => $items->count(),
            ]);
            $seo_description = trans('trp.seo.location.description', [
                'location' => $formattedAddress,
                'dentists_number' => $items->count(),
            ]);
            $social_title = trans('trp.social.location.title', [
                'location' => $formattedAddress,
                'dentists_number' => $items->count(),
            ]);
            $social_description = trans('trp.social.location.description', [
                'location' => $formattedAddress,
                'dentists_number' => $items->count(),
            ]);

            $search_title = trans('trp.page.search.location.title', [
                'location' => $formattedAddress,
            ]);
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
                    $categoryNames[] = $this->categories[$slug];
                }

                // dd(implode(', ', $categoryNames));
                // implode(', ', $categoryNames)

                $seo_title = trans('trp.seo.location.category.title', [
                    'location' => $formattedAddress,
                    'category' => implode(', ', $categoryNames),
                ]);
                $seo_description = trans('trp.seo.location.category.description', [
                    'location' => $formattedAddress,
                    'category' => implode(', ', $categoryNames),
                    'results_number' => $items->count(),
                ]);
                $social_title = trans('trp.social.location.category.title', [
                    'location' => $formattedAddress,
                    'category' => implode(', ', $categoryNames),
                ]);
                $social_description = trans('trp.social.location.category.description', [
                    'location' => $formattedAddress,
                    'category' => implode(', ', $categoryNames),
                    'results_number' => $items->count(),
                ]);

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
            'formattedAddress' => $formattedAddress,
            'canonical' => getLangUrl($query.(!empty($filter) ? '/'.$filter : '')),
            'worldwide' => $query=='worldwide',
            'zoom' => $query=='worldwide' ? 2 : 13,
            'mode' => $mode,
            'staticImageUrl' => $staticmap,
            'query' => $query,
            'lat' => $lat,
            'lon' => $lon,
			'items' => $items,
            'searchCategories' => $searchCategories,
            'stars' => $stars,
            'sort' => $sort,
            'partner' => $partner,
            'ppp' => $ppp,
            'page_num' => $page,
            'orders' => $orders,
            'is_ajax' => $ajax,
            'js' => [
                'search.js'
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

        $dentists = User::where('is_dentist', 1)->whereNotNull('country_id')->whereNotNull('city_name')->groupBy('country_id')->get()->pluck('country_id');

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

        return $this->ShowView('country', array(
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

    public function city($locale=null, $country_slug) {

        $country = Country::where('slug', 'like', $country_slug )->first();

        if(empty($country)) {
            return redirect('/');
        }

        $cities_name = User::where('is_dentist', 1)->where('country_id', $country->id)->whereNotNull('city_name')->groupBy('city_name')->get()->pluck('city_name');
        // dd($cities_name);

        $cities_groups = [];
        $letter = null;
        $letters = [];
        $total_rows = 0;

        foreach ($cities_name as $city) {
            $letter = $city[0];
            if(empty( $letters[$letter] )) {
                $total_rows++;
                $total_rows++;
                $letters[$letter] = true;
                $cities_groups[$total_rows] = $letter;

            }

            $total_rows++;
            $cities_groups[$total_rows] = $city;
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

        $seo_title = trans('trp.seo.country-cities.title', [
            'country' => $country->name,
        ]);
        $seo_description = trans('trp.seo.country-cities.description', [
            'country' => $country->name,
            'results_number' => count($cities_name),
        ]);
        $social_title = trans('trp.social.country-cities.title', [
            'country' => $country->name,
        ]);
        $social_description = trans('trp.social.country-cities.description', [
            'country' => $country->name,
            'results_number' => count($cities_name),
        ]);


        return $this->ShowView('city', array(
            'seo_title' => !empty($seo_title) ? $seo_title : null,
            'seo_description' => !empty($seo_description) ? $seo_description : null,
            'social_title' => !empty($social_title) ? $social_title : null,
            'social_description' => !empty($social_description) ? $social_description : null,
            'cities_name' => $cities_groups,
            'breakpoints' => $breakpoints,
            'country' => $country,
            'total_rows' => $total_rows,
            'js' => [
                'search.js'
            ],
            'jscdn' => [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
            ]
        ));
    }


}