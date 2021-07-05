<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Http\Request as Requestt;

use App\Models\AdminIp;
use App\Models\Admin;

use Validator;
use Request;
use Auth;

class AdminsController extends AdminController {

    public function __construct(Requestt $request) {
        parent::__construct($request);
        $this->langslist = ['' => '-'];
        foreach(config('langs') as $k=> $v) {
            $this->langslist[$k] = $v['name'];
        }

        $this->roles = [
            'admin' => 'Admin',
            'translator' => 'Translator',
            'voxer' => 'Voxer',
            'support' => 'Support',
        ];

        $this->domainlist = [];
        foreach( config('admin.pages.translations.subpages') as $k => $sp) {
            $this->domainlist[$k] = trans('admin.page.translations.'.$k.'.title');
        }

        foreach(config('langs') as $k=> $v) {
            $this->langslist[$k] = $v['name'];
        }
    }

    public function list( ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	return $this->showView('admins', array(
        	'admins_list' => Admin::get(),
            'roles' => $this->roles,
        ));
    }

    public function add( ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $validator = Validator::make($this->request->all(), [
            'username' => array('required', 'unique:admins,username', 'min:3'),
            'password' => array('required', 'min:6'),
            'email' => array('required', 'email', 'unique:admins,email')
        ]);

        if ($validator->fails()) {
            return redirect('cms/admins/admins')
            ->withInput()
            ->withErrors($validator);
        } else {
            
            $newadmin = new Admin;
            $newadmin->username = $this->request->input('username');
            $newadmin->password = bcrypt($this->request->input('password'));
            $newadmin->email = $this->request->input('email');
            $newadmin->role = $this->request->input('role');
            $newadmin->user_id = $this->request->input('user_id');
            $newadmin->save();

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added') );
            return redirect('cms/admins/admins');
        }
    }

    public function delete( $id ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        Admin::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/admins/admins');
    }

    public function edit( $id ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Admin::find($id);

        if(!empty($item)) {

            return $this->showView('admins-edit', array(
                'item' => $item,
                'langslist' => $this->langslist,
                'roles' => $this->roles,
                'domainlist' => $this->domainlist,
            ));
        } else {
            return redirect('cms/admins/admins');
        }
    }

    public function update( $id ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $item = Admin::find($id);

        if(!empty($item)) {
        	$validator = Validator::make($this->request->all(), [
                'username' => array('required', 'string'),
                'email' => array('email'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/admins/admins/edit/'.$item->id)
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $item->username = $this->request->input('username');
                if(!empty( $this->request->input('password') )) {
                    $item->password = bcrypt($this->request->input('password'));
                }
                $item->email = $this->request->input('email');
                $item->comments = $this->request->input('comments');
                $item->role = $this->request->input('role');
                $item->lang_from = $this->request->input('lang_from');
                $item->lang_to = $this->request->input('lang_to');
                $item->text_domain = !empty($this->request->input('text_domain')) ? implode(',', $this->request->input('text_domain')) : '';
                $item->user_id = $this->request->input('user_id');
                $item->save();

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated') );
                return redirect('cms/admins/admins');
            }
        } else {
            return redirect('cms/admins/admins');
        }
    }

    public function listIps() {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'ip' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/admins/ips')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $new_whitelist = new AdminIp;
                $new_whitelist->ip = $this->request->input('ip');
                $new_whitelist->comment = $this->request->input('comment');
                $new_whitelist->save();

                $this->request->session()->flash('success-message', 'IP added' );
                return redirect('cms/admins/ips');
            }

        }

        $items = AdminIp::get();

        return $this->showView('admin-ips', array(
            'items' => $items,
        ));
    }

    public function deleteIp( $id ) {

        if( Auth::guard('admin')->user()->role!='admin') {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        AdminIp::destroy( $id );

        $this->request->session()->flash('success-message', 'IP deleted' );
        return redirect('cms/admins/ips');
    }
}