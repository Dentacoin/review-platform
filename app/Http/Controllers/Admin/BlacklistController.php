<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\User;
use App\Models\Blacklist;
use Carbon\Carbon;

use DB;
use Request;
use Validator;

class BlacklistController extends AdminController
{
    public function list() {

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
        Blacklist::destroy( $id );

        $this->request->session()->flash('success-message', 'Blacklist item deleted' );
        return redirect('cms/blacklist');
    }

}
