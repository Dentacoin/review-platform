<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\UserBranch;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Request;
use Auth;
use DB;

class BranchesController extends AdminController {

    public function clinicBranches() {

        if( Auth::guard('admin')->user()->role!='admin' && Auth::guard('admin')->user()->role!='support' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	$items = User::where('email', '!=', '');
    	
        if(!empty(request('search-id'))) {
            $items = $items->where('id', request('search-id'))->get();

            $inBranches = UserBranch::where('branch_clinic_id', request('search-id'))->get();

            if($inBranches->isNotEmpty()) {
            	foreach($inBranches as $inBranch) {
            		if($inBranch->clinic->email) {
            			if( !$items->contains('id', $inBranch->clinic_id)) {

            				$items->push(User::find($inBranch->clinic_id));
            			}
            		}
            	}
            }
        } else {
        	$items = $items->has('branches')->get();
        }

        return $this->showView('clinic-branches', array(
            'items' => $items,
            'search_id' => request('search-id')
        ));
    }

    public function addClinicBranch() {

        if( Auth::guard('admin')->user()->role!='admin' && Auth::guard('admin')->user()->role!='support' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	if(Request::isMethod('post')) {

    		if(!empty(request('main_clinic_id')) && !empty(request('branch_clinic_id'))) {
    			$main_clinic = User::find(request('main_clinic_id'));
    			$branch_clinic = User::find(request('branch_clinic_id'));

    			if(!empty($main_clinic) && !empty($branch_clinic)) {

    				$existing_branch = UserBranch::where('clinic_id', $main_clinic->id)->where('branch_clinic_id', $branch_clinic->id)->first();

    				if($existing_branch) {
    					$this->request->session()->flash('error-message', 'Branch already exists!');
    				} else {

    					if($main_clinic->branches->isNotEmpty()) {
		                    foreach($main_clinic->branches as $branch) {
		                        $newbranch = new UserBranch;
		                        $newbranch->clinic_id = $branch_clinic->id;
		                        $newbranch->branch_clinic_id = $branch->branch_clinic_id;
		                        $newbranch->save();

		                        $newbranch = new UserBranch;
		                        $newbranch->clinic_id = $branch->branch_clinic_id;
		                        $newbranch->branch_clinic_id = $branch_clinic->id;
		                        $newbranch->save();
		                    }
		                }

		                $newbranch = new UserBranch;
		                $newbranch->clinic_id = $main_clinic->id;
		                $newbranch->branch_clinic_id = $branch_clinic->id;
		                $newbranch->save();

		                $newbranch = new UserBranch;
		                $newbranch->clinic_id = $branch_clinic->id;
		                $newbranch->branch_clinic_id = $main_clinic->id;
		                $newbranch->save();

                        $branch_clinic->main_branch_clinic_id = $main_clinic->id;
                        $branch_clinic->save();
                        if(empty($main_clinic->main_branch_clinic_id)) {
                            $main_clinic->main_branch_clinic_id = $main_clinic->id;
                            $main_clinic->save();
                        }
    				}

    				$this->request->session()->flash('success-message', 'Branch added!');

    			} else {
    				if(empty($main_clinic)) {
    					$this->request->session()->flash('error-message', 'Main clinic doesn\'t exists!');
    				}

    				if(empty($branch_clinic_id)) {
    					$this->request->session()->flash('error-message', 'Branch clinic doesn\'t exists!');
    				}
    			}
    		} else {
    			$this->request->session()->flash('error-message', 'Fill all fields!');
    		}

    		return redirect(url('cms/trp/clinic-branches'));

    	}

    	return $this->showView('clinic-branches-add');
    }

}
