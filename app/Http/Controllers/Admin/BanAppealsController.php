<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;


use App\Models\DcnTransaction;
use App\Models\UserAction;
use App\Models\BanAppeal;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Request;
use Image;
use Route;

class BanAppealsController extends AdminController {

	public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->types = [
            'deleted' => 'Deleted',
            'bad_ip' => 'Bad IP',
        ];
    }

    public function list() {
    	$items = BanAppeal::orderBy('id', 'desc')->get();

    	return $this->ShowView('ban-appeals', array(
    		'types' => $this->types,
    		'items' => $items,
    		'js' => [
				'../js/lightbox.js',
			],
			'css' => [
				'lightbox.css',
			],
    	));
    }

    public function approve($id) {

        if (!empty(Request::input('approved_reason'))) {

            $item = BanAppeal::find($id);
            $user = $item->user;
            $user->restoreActions();
            $user->restore();

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'restored';
            $action->reason = Request::input('approved_reason');
            $action->actioned_at = Carbon::now();
            $action->save();

            $item->status = 'approved';
            $item->save();

            $this->request->session()->flash('success-message', "Appeal approved" );
        } else {
            $this->request->session()->flash('error-message', "You have to write a reason why this appeal has to be approved" );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');
    }

    public function reject($id) {

        if (!empty(Request::input('rejected_reason'))) {

            $item = BanAppeal::find($id);
            $user = $item->user;

            $user->patient_status = 'deleted';
            $user->save();

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'deleted';
            $action->reason = Request::input('rejected_reason');
            $action->actioned_at = Carbon::now();
            $action->save();

            $user->deleteActions();
            User::destroy( $user->id );

            $item->status = 'rejected';
            $item->save();

            $this->request->session()->flash('success-message', "Appeal rejected" );
        } else {
            $this->request->session()->flash('error-message', "You have to write a reason why this appeal has to be rejected" );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');

    }

}
