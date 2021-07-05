<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Auth;

class LogsController extends AdminController {
    
    public function list($type=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(!empty($type)) {

        	if(request('clear') && $type != 'api_withdraw' && $type != 'api-ban-appeals') {
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
