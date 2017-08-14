<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Page;
use Request;
use Response;
use Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;


class PagesController extends AdminController
{
    public function list( ) {
        return $this->showView('pages', array(
            'pages_list' => Page::get()
        ));
    }

    public function add( ) {

        if(Request::isMethod('post')) {
            $newpage = new Page;
            $newpage->header = $this->request->input('header');
            $newpage->footer = $this->request->input('footer');
            $newpage->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('slug-'.$key))) {
                    $translation = $newpage->translateOrNew($key);
                    $translation->page_id = $newpage->id;
                    $translation->slug = $this->request->input('slug-'.$key);
                    $translation->title = $this->request->input('title-'.$key);
                    $translation->seo_title = $this->request->input('seo-title-'.$key);
                    $translation->description = $this->request->input('description-'.$key);
                    $translation->content = json_encode($this->request->input('content-'.$key));
                    $translation->save();
                }
            }
            
            //Image
            if( Request::file('image') && Request::file('image')->isValid() ) {
                $img = Image::make( Input::file('image') )->orientate();
                $newpage->addImage($img);
            }
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added'));
            return Response::json(array(
                'success' => true,
                'href' => url('cms/'.$this->current_page.'/edit/'.$newpage->id)
            ));
        }



    	return $this->showView('pages-form', array(
        ));
    }

    public function delete( $id ) {
        Page::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function removepic( $id ) {
        $item = Page::find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.pic-removed') );
        }

        return redirect('cms/'.$this->current_page);
    }
    
    public function edit( $id ) {
        $item = Page::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                $item->header = $this->request->input('header');
                $item->footer = $this->request->input('footer');

                foreach ($this->langs as $key => $value) {
                    if(!empty($this->request->input('slug-'.$key))) {
                        $translation = $item->translateOrNew($key);
                        $translation->page_id = $item->id;
                        $translation->slug = $this->request->input('slug-'.$key);
                        $translation->title = $this->request->input('title-'.$key);
                        $translation->seo_title = $this->request->input('seo-title-'.$key);
                        $translation->description = $this->request->input('description-'.$key);
                        $translation->content = $this->request->input('content-'.$key);
                    }
                }
                $item->save();

                //Image
                if( Request::file('image') && Request::file('image')->isValid() ) {
                    $img = Image::make( Input::file('image') )->orientate();
                    $item->addImage($img);
                }
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return Response::json(array(
                    'success' => true,
                    'href' => url('cms/'.$this->current_page.'/edit/'.$item->id)
                ));
            }

            return $this->showView('pages-form', array(
                'item' => $item,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

}