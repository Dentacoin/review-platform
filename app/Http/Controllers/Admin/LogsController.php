<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

class LogsController extends AdminController
{
    public function list() {

    	if(request('clear')) {
    		file_put_contents( base_path().'/storage/logs/laravel.log', '');
    		file_put_contents( base_path().'/storage/logs/laravel.log', '');


            request()->session()->flash('success-message', 'Errors deleted');
            return redirect('cms/logs');
    	}

        return $this->ShowView('logs', array(
        ));
    }
}
