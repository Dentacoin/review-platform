<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\ScrapeDentistResult;
use App\Models\ScrapeDentist;
use App\Models\Country;
use App\Models\User;

use App\Exports\Export;

use Validator;
use Request;
use Auth;

class ScrapeGoogleDentistsController extends AdminController {

    public function list( ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	if(Request::isMethod('post')) {

    		$validator = Validator::make($this->request->all(), [
	            'address' =>  array('required', 'string'),
	        ]);

	        if ($validator->fails()) {
	            return redirect('cms/scrape-google-dentists')
	            ->withInput()
	            ->withErrors($validator);
	        } else {
	            
		        $info = \GoogleMaps::load('geocoding')
		        ->setParam ([
		            'address'    => request('address'),
		        ])
		        ->get();
        		$info = json_decode($info);


	            if(empty($info)) {
	                Request::session()->flash('error-message', trans('vox.common.invalid-address'));
                    return redirect( url('cms/scrape-google-dentists'));
	            } else {

	            	//lat - nagore nadolu
	            	//lon - nalqvo nadqsno

	            	$latStart = $info->results[0]->geometry->viewport->southwest->lat;
	            	$latEnd = $info->results[0]->geometry->viewport->northeast->lat;

	            	$lonStart = $info->results[0]->geometry->viewport->southwest->lng;
	            	$lonEnd = $info->results[0]->geometry->viewport->northeast->lng;

	            	list( $latStep, $lonStep ) = $this->geLatLonInDegrees(1, $latStart);
	            	//dd($latStart, $latEnd, $lonStart, $lonEnd );

	            	$latTotal = ceil(($latEnd - $latStart) / $latStep);
	            	$lonTotal = ceil(($lonEnd - $lonStart) / $lonStep);

	            	$scrape = new ScrapeDentist;
	            	$scrape->name = request('address');
	            	$scrape->lat_start = $latStart;
	            	$scrape->lat_end = $latEnd;
	            	$scrape->lon_start = $lonStart;
	            	$scrape->lon_end = $lonEnd;
	            	$scrape->lat_step = $latStep;
	            	$scrape->lon_step = $lonStep;
	            	$scrape->requests = 0;
	            	$scrape->requests_total = $latTotal * $lonTotal;
	            	$scrape->save();

	            }
	        }
    	}

    	$scrapes = ScrapeDentist::orderBy('id', 'desc')->get();

    	$finding_emails = ScrapeDentistResult::whereNull('scrape_email')->count();

    	return $this->showView('scrape-dentists', [
			'countries' => Country::with('translations')->get(),
			'scrapes' => !empty($scrapes) ? $scrapes : null,
			'finding_emails' => !empty($finding_emails) ? $finding_emails : null,
		]);
    }


    public function download( $id ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	$dentists = ScrapeDentistResult::where('scrape_dentists_id', $id)->get();

        if (!empty($dentists)) {

	        $flist = [];
	        $flist[] = [
	            'Name',
	            'Alternative Name',
	            'Email',
	            'Phone',
	            'Website',
	            'Work Hours',
	            'Type(dentist/clinic)',
	            'Country',
	            'Address',
	            'Avatar link',
	            'Google Place ID (do not edit)',
	        ];
	        foreach ($dentists as $dentist) {
	        	$place_id = $dentist['place_id'];
	        	$emails = $dentist['emails'];

	        	$dentist = json_decode($dentist->data, true);

	            $flist[] = [
	                $dentist['name'],
	                '',
	                !empty($emails) ? $emails : '',
	                !empty($dentist['phone']) ? $dentist['phone'] : '',
	                !empty($dentist['website']) ? $dentist['website'] : '',
	                !empty($dentist['work_hours']) ? $dentist['work_hours'] : '',
	                '',
	                $dentist['country_name'],
	                $dentist['address'],
	                '',
	                $place_id
	            ];
	        }

	        $dir = storage_path().'/app/public/xls/';
	        if(!is_dir($dir)) {
	            mkdir($dir);
	        }
	        $fname = $dir.'export';


	        $export = new Export($flist);
            return Excel::download($export, 'scrapes.xls');
	    }
    }

    private function geLatLonInDegrees($km, $lat) {
        $lat = ($km / 6378) * (180 / pi());
        $lon = ($km / 6378) * (180 / pi()) / cos($lat * pi()/180);
        return [$lat, $lon];        
    }
}