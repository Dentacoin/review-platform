<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Admin;
use Illuminate\Http\Request;


class AdminsController extends AdminController
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->langslist = ['' => '-'];
        foreach(config('langs') as $k=> $v) {
            $this->langslist[$k] = $v['name'];
        }

        $this->roles = [
            'admin' => trans('admin.page.'.$this->current_page.'.role-admin'),
            'translator' => trans('admin.page.'.$this->current_page.'.role-translator'),
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
    	return $this->showView('admins', array(
        	'admins_list' => Admin::get(),
            'roles' => $this->roles,
        ));
    }

    public function add( ) {
        $validator = Validator::make($this->request->all(), [
            'username' => array('required', 'unique:admins,username', 'min:3'),
            'password' => array('required', 'min:6'),
            'email' => array('required', 'email', 'unique:admins,email')
        ]);

        if ($validator->fails()) {
            return redirect('cms/admins')
            ->withInput()
            ->withErrors($validator);
        } else {
            
            $newadmin = new Admin;
            $newadmin->username = $this->request->input('username');
            $newadmin->password = bcrypt($this->request->input('password'));
            $newadmin->email = $this->request->input('email');
            $newadmin->role = $this->request->input('role');
            $newadmin->save();

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added') );
            return redirect('cms/admins');
        }
    }

    public function delete( $id ) {
        Admin::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/admins');
    }

    public function edit( $id ) {
        $item = Admin::find($id);

        if(!empty($item)) {

            return $this->showView('admins-edit', array(
                'item' => $item,
                'langslist' => $this->langslist,
                'roles' => $this->roles,
                'domainlist' => $this->domainlist,
            ));
        } else {
            return redirect('cms/admins');
        }
    }

    public function update( $id ) {
        $item = Admin::find($id);

        if(!empty($item)) {
        	$validator = Validator::make($this->request->all(), [
                'username' => array('required', 'string'),
                'email' => array('email'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/admins/edit/'.$item->id)
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
                $item->save();

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated') );
                return redirect('cms/admins');
            }
        } else {
            return redirect('cms/admins');
        }
    }
}