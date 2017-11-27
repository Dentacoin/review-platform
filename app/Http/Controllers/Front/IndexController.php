<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\Country;
use App\Models\City;
use App\Models\User;

use App;

class IndexController extends FrontController
{

	public function home($locale=null) {

		$placeholder = '';
		if($this->city_id) {
			$c = City::find($this->city_id);
			$placeholder = $c->name.', '.$c->country->name;
		} else if($this->country_id) {
			$c = Country::find($this->country_id);
			$placeholder = $c->name;
		}

		return $this->ShowView('index', array(
			'placeholder' => $placeholder,
			'users_count' => User::count(),
			'dentist_count' => User::where('is_dentist', 1)->count(),
        ));	
	}

}