<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Reward;

use Validator;
use Request;

class RewardsController extends AdminController {

    public function list( ) {

        if(Request::isMethod('post')) {
        	$rewards = Reward::get();
        	foreach ($rewards as $reward) {
        		if(isset(Request::input('rewards')[$reward->reward_type])) {
        			$reward->amount = Request::input('rewards')[$reward->reward_type];
        			$reward->save();
        		}
        	}
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
        }

    	return $this->showView('rewards', array(
            'rewards' => Reward::get()
        ));
    }

}