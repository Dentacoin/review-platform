<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Secret;

use Request;

class SecretsController extends AdminController
{
    public function list( ) {

    	return $this->showView('secrets', array(
        	'list' => Secret::orderBy('id', 'DESC')->get()
        ));
    }

    public function add( ) {
        $sarr = explode(',', Request::input('secrets'));
        if(!empty($sarr)) {
            foreach ($sarr as $secret) {
                $secret = trim($secret);
                if(!empty($secret)) {
                    $sec = new Secret;
                    $sec->secret = $secret;
                    $sec->used = 0;
                    $sec->save();
                }
            }
        }
        
        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added') );
        return redirect('cms/'.$this->current_page);
    }

    public function delete( $id ) {
        Secret::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

}