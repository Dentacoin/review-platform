<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use App\Models\IncompleteRegistration;
use CArbon\Carbon;

use App;

class IndexController extends FrontController
{

	public function home($locale=null) {
		if(!empty($this->user) && $this->user->is_dentist) {
			return redirect( $this->user->getLink() );
		}

		$featured = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved'])->orderBy('avg_rating', 'DESC');
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
            'countries' => Country::get(),
			'featured' => $refined,
			'js' => [
				'index.js',
                'search.js',
                'address.js'
			],
			'jscdn' => [
				'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en'
			]
        ));	
	}


	public function unsubscribe ($locale=null, $session_id=null, $hash=null) {
		return $this->dentist($locale, $session_id, $hash, true);
	}

	public function dentist($locale=null, $session_id=null, $hash=null, $unsubscribe = false) {

		if(!empty($this->user)) {
			return redirect( getLangUrl('/') );
		}

		$unsubscribed = false;
		$regData = null;
        if($session_id && $hash) {
        	$regData = IncompleteRegistration::find($session_id);
        	if(!empty($regData) && $hash!=md5($session_id.env('SALT_INVITE'))) {
        		$regData = null;
        	}

        	if($regData && $unsubscribe) {
        		$regData->unsubscribed = true;
        		$regData->save();
        		$regData = null;
        		$unsubscribed = true;
        	}
        }

        if(empty($regData) && session('incomplete-registration')) {
        	$regData = IncompleteRegistration::find(session('incomplete-registration'));
        }


		return $this->ShowView('index-dentist', array(
			'extra_body_class' => 'white-header',
			'js' => [
				'index-dentist.js'
			],
			'regData' => $regData,
			'unsubscribed' => $unsubscribed,
        ));	
	}

	public function gdpr($locale=null) {

		$this->user->gdpr_privacy = true;
		$this->user->save();

		return redirect( getLangUrl('/') );
	}

}