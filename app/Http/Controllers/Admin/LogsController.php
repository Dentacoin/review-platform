<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

class LogsController extends AdminController
{
    public function list($type=null) {

        if(!empty($type)) {

        	if(request('clear') && $type != 'api_civic' && $type != 'api_withdraw') {
        		file_put_contents( base_path().'\/../'.$type.'/storage/logs/laravel.log', '');

                request()->session()->flash('success-message', 'Errors deleted');
                return redirect('cms/logs/'.$type);
        	}

            return $this->ShowView('logs', array(
                'type' => $type,
            ));
        } else {
            return redirect( url('cms/logs/trp'));
        }
    }
}
