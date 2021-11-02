<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Response;
use Request;
use Auth;

class FaqController extends AdminController {

    public function faq($locale = null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $pathToFile = base_path().'/resources/lang/'.($locale ? $locale : 'en').'/faq-trp.php';
        $content = json_decode( file_get_contents($pathToFile), true );

        if(Request::isMethod('post') && request('faq')) {
            file_put_contents($pathToFile, json_encode(request('faq')));
            $this->request->session()->flash('success-message', 'FAQs are saved!');

            return Response::json( [
                'success' => true
            ]);
        }
        
        return $this->showView('voxes-faq', array(
            'content' => $content
        ));
    }
}