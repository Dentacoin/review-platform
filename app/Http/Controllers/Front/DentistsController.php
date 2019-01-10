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


class DentistsController extends FrontController
{

    public function paginate($locale=null, $query=null, $latlon=null, $page) {
        return $this->list($locale, $query, $latlon, $page, true);
    }

    public function search($locale=null, $query=null, $latlon=null, $page=null, $ajax=null) {

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

        if(request('byname')) {
            $items = $items->where('name', 'like', $query.'%');
            $mode = 'name';
        } else {

            if($latlon) {
                $arr = explode(',', $latlon);
                if(count($arr)==2 && parseFloat($arr[0]) && parseFloat($arr[1])) {
                    $lat = parseFloat($arr[0]);
                    $lon = parseFloat($arr[1]);
                }
            }

            if(!$lat || !$lon) {

                $geores = \GoogleMaps::load('geocoding')
                ->setParam ([
                    'address'    => $query,
                ])
                ->get();

                $geores = json_decode($geores);
                if(!empty($geores->results[0]->geometry->location)) {
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
            'reviews' => 'reviews',
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
        if( Request::input('searchCategories') && is_array(Request::input('searchCategories')) ) {
            $searchCategories = Request::input('searchCategories');
            foreach (Request::input('searchCategories') as $cat) {
                $items = $items->whereHas('categories', function ($q) use ($cat) {
                    $q->where('category_id', $cat);
                });
            }
        }
        //dd($categories);
        if( Request::input('partner') ) {
            $partner = true;
            $items = $items->where('is_partner', true);
        }

        $items = $items->take(100)->get(); //->take($ppp)->skip( ($page-1)*$ppp )

        $staticmap = 'https://maps.googleapis.com/maps/api/staticmap?center='.$lat.','.$lon.'&zoom=13&size=670x188&maptype=roadmap&key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk';
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
       

		return $this->ShowView('search', [
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


}