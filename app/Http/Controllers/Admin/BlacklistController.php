<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\UserAction;
use App\Models\Blacklist;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Request;
use Auth;

class BlacklistController extends AdminController {
    
    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'pattern' => array('required', 'string', 'min:5'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/blacklist')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $bl = new Blacklist;
                $bl->pattern = $this->request->input('pattern');
                $bl->field = $this->request->input('field');
                $bl->comments = $this->request->input('comments');
                $bl->save();

                if ($this->request->input('field') == 'email') {
                    $users = User::get();

                    foreach ($users as $u) {
                        if (fnmatch(mb_strtolower($this->request->input('pattern')), mb_strtolower($u->email)) == true) {
                            $action = new UserAction;
                            $action->user_id = $u->id;
                            $action->action = 'deleted';
                            $action->reason = 'Automatically - Blacklisted';
                            $action->actioned_at = Carbon::now();
                            $action->save();
                            
                            $u->deleteActions();
                            User::destroy( $u->id );
                        }
                    }
                }

                $this->request->session()->flash('success-message', 'Item added to the blacklist' );
                return redirect('cms/blacklist');
            }
        }

        $items = Blacklist::get();

        return $this->showView('blacklist', array(
            'items' => $items,
        ));
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        Blacklist::destroy( $id );

        $this->request->session()->flash('success-message', 'Blacklist item deleted' );
        return redirect('cms/blacklist');
    }
}