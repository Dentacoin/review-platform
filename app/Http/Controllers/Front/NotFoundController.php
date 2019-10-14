<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use CArbon\Carbon;

use App;
use Mail;
use Response;
use Request;
use Cookie;
use Validator;

class NotFoundController extends FrontController
{

	public function home($locale=null) {
		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		$featured = User::where('is_dentist', 1)->whereIn('status', ['approved','added_approved','admin_imported'])->orderBy('avg_rating', 'DESC');
		$homeDentists = collect();

		if( !empty($this->user) ) {
			if( $homeDentists->count() < 12 && $this->user->city_name ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('city_name', 'LIKE', $this->user->city_name)->take( 12 - $homeDentists->count() )->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 && $this->user->state_name ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('state_name', 'LIKE', $this->user->state_name)->take( 12 - $homeDentists->count() )->whereNotIn('id', $homeDentists->pluck('id')->toArray())->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 && $this->user->country_id ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('country_id', 'LIKE', $this->user->country_id)->take( 12 - $homeDentists->count() )->whereNotIn('id', $homeDentists->pluck('id')->toArray())->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

		} else {

			if( $homeDentists->count() < 12 && $this->city_id ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('city_id', 'LIKE', $this->city_id)->take( 12 - $homeDentists->count() )->get();
				$homeDentists = $homeDentists->concat($addMore);
			}

			if( $homeDentists->count() < 12 && $this->country_id ) {
				$addMore = clone $featured;
				$addMore = $addMore->where('country_id', 'LIKE', $this->country_id)->take( 12 - $homeDentists->count() )->get();
				$homeDentists = $homeDentists->concat($addMore);				
			}
		}

		if( $homeDentists->count() <= 2) {
			$addMore = clone $featured;
			$addMore = $addMore->take( 12 - $homeDentists->count() )->get();
			$homeDentists = $homeDentists->concat($addMore);	
		}

		$params = array(
			'featured' => $homeDentists,
			'js' => [
				'index.js',
			],
			'gray_footer' => true
        );

		return $this->ShowView('404', $params);	
	}
}