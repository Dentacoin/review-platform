<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Carbon\Carbon;
use Auth;

class LogsController extends AdminController {
    
    public function list($type=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(!empty($type)) {

        	if(request('clear') && $type != 'api_withdraw' && $type != 'api-ban-appeals') {
        		file_put_contents( base_path().'\/../'.$type.'/storage/logs/laravel'.($type == 'trp' ? '-'.(request('date') ?? date('Y-m-d')) : '').'.log', '');

                request()->session()->flash('success-message', 'Errors deleted');
                return redirect('cms/logs/'.$type);
        	}

            $logDates = [];

            $previousDay = Carbon::now()->addDays(-1);
            for($i=0; $i<5; $i++) {
                $logDates[] = $previousDay;
                $previousDay = Carbon::parse($previousDay)->addDays(-1);
            }

            return $this->ShowView('logs', array(
                'type' => $type,
                'logDates' => $logDates
            ));
        } else {
            return redirect( url('cms/logs/trp'));
        }
    }
}