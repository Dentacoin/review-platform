<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Models\PageSeo;
use App\Models\User;

class NotFoundController extends FrontController {

	/**
     * 404 page view
     */
	public function home($locale=null) {

		if(!empty($this->user) && $this->user->isBanned('trp')) {
			return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
		}

		$featured = User::where('is_dentist', 1)->whereIn('status', config('dentist-statuses.shown_with_link'))->orderBy('avg_rating', 'DESC')->whereNull('self_deleted');
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

		$seos = PageSeo::find(21);

		$params = array(
			'featured' => $homeDentists,
			'js' => [
				'index.js',
			],
            'css' => [
                'trp-404.css',
            ],
			'gray_footer' => true,
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
        );

		return $this->ShowView('404', $params, 404);	
	}
}