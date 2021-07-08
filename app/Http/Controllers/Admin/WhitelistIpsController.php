<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\WhitelistIp;

use Validator;
use Request;
use Auth;

class WhitelistIpsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'ip' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/whitelist/ips')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $new_whitelist = new WhitelistIp;
                $new_whitelist->ip = $this->request->input('ip');
                $new_whitelist->comment = $this->request->input('comment');
                $new_whitelist->save();

                $this->request->session()->flash('success-message', 'IP added to the whitelist' );
                return redirect('cms/whitelist/ips');
            }

        }

        $items = WhitelistIp::where(function($query) {
            $query->where('for_vpn', '=', 0 )
            ->orWhereNull('for_vpn');
        })->get();

        return $this->showView('whitelist', array(
            'items' => $items,
        ));
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        WhitelistIp::destroy( $id );

        $this->request->session()->flash('success-message', 'Whitelist IP deleted' );
        return redirect('cms/whitelist/ips');
    }

    public function vpnList() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'ip' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/whitelist/vpn-ips')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $new_whitelist = new WhitelistIp;
                $new_whitelist->ip = $this->request->input('ip');
                $new_whitelist->for_vpn = true;
                $new_whitelist->comment = $this->request->input('comment');
                $new_whitelist->save();

                $this->request->session()->flash('success-message', 'VPN IP added to the whitelist' );
                return redirect('cms/whitelist/vpn-ips');
            }

        }

        $items = WhitelistIp::where('for_vpn', 1)->get();

        return $this->showView('whitelist-vpn', array(
            'items' => $items,
        ));
    }

    public function vpnDelete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        WhitelistIp::destroy( $id );

        $this->request->session()->flash('success-message', 'Whitelist VPN IP deleted' );
        return redirect('cms/whitelist/vpn-ips');
    }

}
