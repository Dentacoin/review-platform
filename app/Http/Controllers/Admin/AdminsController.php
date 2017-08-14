<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Admin;


class AdminsController extends AdminController
{
    public function list( ) {
        $roles_list = [];
        foreach ($this->roles as $role) {
            $roles_list[$role] = trans('admin.page.'.$this->current_page.'.role-'.$role);
        }

    	return $this->showView('admins', array(
        	'admins_list' => Admin::get(),
            'roles' => $roles_list
        ));
    }

    public $roles = [
        'admin',
        'editor',
    ];

    public function add( ) {
        $validator = Validator::make($this->request->all(), [
            'role' => array('required', 'in:'.implode(',' , $this->roles)),
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
            $newadmin->role = $this->request->input('role');
            $newadmin->username = $this->request->input('username');
            $newadmin->password = bcrypt($this->request->input('password'));
            $newadmin->email = $this->request->input('email');
            $newadmin->comments = $this->request->input('comments');
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
            $roles_list = [];
            foreach ($this->roles as $role) {
                $roles_list[$role] = trans('admin.page.'.$this->current_page.'.role-'.$role);
            }

            return $this->showView('admins-edit', array(
                'item' => $item,
                'roles' => $roles_list,
            ));
        } else {
            return redirect('cms/admins');
        }
    }

    public function update( $id ) {
        $item = Admin::find($id);

        if(!empty($item)) {
        	$validator = Validator::make($this->request->all(), [
                'role' => array('required', 'in:'.implode(',' , $this->roles)),
                'username' => array('required', 'string'),
                'email' => array('email'),
                'comments' => array('string'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/admins')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $item->username = $this->request->input('username');
                $item->role = $this->request->input('role');
                if(!empty( $this->request->input('password') )) {
                    $item->password = bcrypt($this->request->input('password'));
                }
                $item->email = $this->request->input('email');
                $item->comments = $this->request->input('comments');
                $item->save();

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated') );
                return redirect('cms/admins');
            }
        } else {
            return redirect('cms/admins');
        }
    }
}