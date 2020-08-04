<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\UserInvite;
use Illuminate\Http\Request;


class InvitesController extends AdminController {

    public function list( ) {

    	return $this->showView('invites', array(
        	'invites' => UserInvite::orderBy('id', 'desc')->take(100)->get(),
        ));
    }

    public function delete( $id ) {
        UserInvite::destroy( $id );

        $this->request->session()->flash('success-message', 'Invite Deleted' );
        return redirect('cms/invites');
    }
}