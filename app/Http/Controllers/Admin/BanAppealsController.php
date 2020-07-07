<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;

use App\Models\BanAppeal;

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
    	$items = BanAppeal::get();

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

        return $this->list();

    }

    public function reject($id) {
        $item = BanAppeal::find($id);

        return $this->list();
    }

}
