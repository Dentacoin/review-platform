<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;
use Request;
use Image;
use Validator;

use App\Models\PageSeo;

class PagesSeoController extends AdminController
{
    public function vox_list() {
    	$pages = PageSeo::where('platform', 'vox')->get();

    	return $this->ShowView('pages-vox', array(
    		'pages' => $pages
    	));
    }

    public function trp_list() {
    	$pages = PageSeo::where('platform', 'trp')->get();

    	return $this->ShowView('pages-trp', array(
    		'pages' => $pages
    	));
    }

    public function add($platform) {

        if(Request::isMethod('post')) {

            $newpage = new PageSeo;
        	$newpage->name = $this->request->input('name');
		    $newpage->url = $this->request->input('url');
		    $newpage->platform = $this->request->input('platform');
	        $newpage->save();

            Request::session()->flash('success-message', 'Page Added');
            return redirect('cms/pages/'.$platform);
        }

        return $this->showView('add-pageseo-form', array(
    		'platforms' => ['vox' => 'vox', 'trp' => 'trp'],
        ));
    }

    public function edit( $id ) {
    	$item = PageSeo::find($id);

        if(!empty($item)) {

	        if(Request::isMethod('post')) {

                $validator = Validator::make($this->request->all(), [
                    'seo-title-en' => array('required'),
                    'seo-description-en' => array('required'),
                ]);

                if ($validator->fails()) {
                    return redirect('cms/pages/'.$item->platform.'/edit/'.$item->id)
                    ->withInput()
                    ->withErrors($validator);
                } else {

    		        foreach ($this->langs as $key => $value) {
    		            if(!empty($this->request->input('seo-title-'.$key))) {
    		                $translation = $item->translateOrNew($key);
    		                $translation->page_seo_id = $item->id;
    		                $translation->seo_title = $this->request->input('seo-title-'.$key);
		            		$translation->seo_description = $this->request->input('seo-description-'.$key);
		    		        $translation->social_title = $this->request->input('social-title-'.$key);
		                    $translation->social_description = $this->request->input('social-description-'.$key);
    		            }

    	                $translation->save();
    		        }

    		        if( Request::file('image') && Request::file('image')->isValid() ) {
		                $item->addImage($img);
		            }

    		        Request::session()->flash('success-message', 'Page Edited');
                	return redirect('cms/pages/'.$item->platform);
                }
	        }

	        return $this->showView('edit-pageseo-form', array(
	            'item' => $item,
	        ));
	    }
        
        return redirect('cms');
    }

    public function removepic( $id ) {
        $item = PageSeo::find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
            $this->request->session()->flash('success-message', 'Image removed');

        	return redirect('cms/pages/edit/'.$item->id);
        }

        return redirect('cms');

    }
}
