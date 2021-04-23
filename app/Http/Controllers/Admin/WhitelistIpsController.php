<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\WhitelistIp;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Request;
use DB;

class WhitelistIpsController extends AdminController {

    public function list() {

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'ip' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/whitelist')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $new_whitelist = new WhitelistIp;
                $new_whitelist->ip = $this->request->input('ip');
                $new_whitelist->comment = $this->request->input('comment');
                $new_whitelist->save();

                $this->request->session()->flash('success-message', 'IP added to the whitelist' );
                return redirect('cms/whitelist');
            }

        }

        $items = WhitelistIp::get();

        return $this->showView('whitelist', array(
            'items' => $items,
        ));
    }

    public function delete( $id ) {
        WhitelistIp::destroy( $id );

        $this->request->session()->flash('success-message', 'Whitelist IP deleted' );
        return redirect('cms/whitelist');
    }

}
