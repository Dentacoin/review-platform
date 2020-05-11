<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Input;
use Request;
use Response;
use Route;


class FaqController extends AdminController
{

    public function faq($locale = null) {

        $pathToFile = base_path().'/resources/lang/'.($locale ? $locale : 'en').'/faq-trp.php';
        $content = json_decode( file_get_contents($pathToFile), true );


        if(Request::isMethod('post') && request('faq')) {
            file_put_contents($pathToFile, json_encode(request('faq')));
            $this->request->session()->flash('success-message', 'FAQs are saved!');

            return Response::json( [
                'success' => true
            ] );
        }
            

        return $this->showView('voxes-faq', array(
            'content' => $content
        ));

    }


}