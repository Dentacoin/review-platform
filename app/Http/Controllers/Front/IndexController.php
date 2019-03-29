<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use App\Models\IncompleteRegistration;

use App;

class IndexController extends FrontController
{

	public function home($locale=null) {
		if(!empty($this->user) && $this->user->is_dentist) {
			return redirect( $this->user->getLink() );
		}

		$featured = User::where('is_dentist', 1)->where('status', 'approved')->orderBy('avg_rating', 'DESC');
		$refined = clone $featured;
		if( !empty($this->user) ) {
			if( $this->user->country_id ) {
				$refined->where('country_id', $this->user->country_id);
				if( $this->user->city_id ) {
					$refined->where('city_id', $this->user->city_id);
				}
			}
		} else {
			if( $this->country_id ) {
				$refined->where('country_id', $this->country_id);
				if( $this->city_id ) {
					$refined->where('city_id', $this->city_id);
				}
			}
		}

		$refined = $refined->take(12)->get();

		if($refined->isEmpty()) {
			$refined = clone $featured;
			if( !empty($this->user) ) {
				if( $this->user->country_id ) {
					$refined->where('country_id', $this->user->country_id);
				}
			} else {
				if( $this->country_id ) {
					$refined->where('country_id', $this->country_id);
				}
			}
			$refined = $refined->take(12)->get();
		}


		if($refined->isEmpty()) {
			$refined = clone $featured;
			$refined = $refined->take(12)->get();
		}

		return $this->ShowView('index', array(
			'featured' => $refined,
			'js' => [
				'index.js',
                'search.js'
			],
			'jscdn' => [
				'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
			]
        ));	
	}

	public function dentist($locale=null) {
		if(!empty($this->user)) {
			return redirect( getLangUrl('/') );
		}

        if(session('incomplete-registration')) {
        	$regData = IncompleteRegistration::find(session('incomplete-registration'));
        }

		return $this->ShowView('index-dentist', array(
			'extra_body_class' => 'white-header',
			'js' => [
				'index-dentist.js'
			],
			'regData' => $regData
        ));	
	}

	public function gdpr($locale=null) {

		$this->user->gdpr_privacy = true;
		$this->user->save();

		return redirect( getLangUrl('/') );
	}

}