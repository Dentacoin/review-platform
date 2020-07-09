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
        $item = BanAppeal::find($id);

        $user = $item->user;

        if($user->patient_status == 'deleted') {

            $action = new UserAction;
            $action->user_id = $user->id;
            $action->action = 'restored';
            $action->reason = 'Restored from Ban Appeal Approvement';
            $action->actioned_at = Carbon::now();
            $action->save();

            $user->restoreActions();
            $user->deleted_at = null;
            $user->save();

        } else {
            $user->restoreActions();
        }

        $item->status = 'approved';
        $item->save();

        return redirect(url('cms/ban_appeals'));

    }

    public function reject($id) {
        $item = BanAppeal::find($id);

        $user = $item->user;

        $user->patient_status = 'deleted';
        $user->deleted_at = Carbon::now();
        $user->save();

        $item->status = 'rejected';
        $item->save();

        return redirect(url('cms/ban_appeals'));
    }

}
