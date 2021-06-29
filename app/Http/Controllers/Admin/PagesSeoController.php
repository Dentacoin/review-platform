<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\PageSeo;

use App\Exports\Export;
use App\Imports\Import;

use Validator;
use Request;
use Image;
use Auth;

class PagesSeoController extends AdminController {

    public function vox_list() {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $pages = PageSeo::where('platform', 'vox')->get();

        return $this->ShowView('pages-vox', array(
            'pages' => $pages
        ));
    }

    public function trp_list() {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $pages = PageSeo::where('platform', 'trp')->get();

        return $this->ShowView('pages-trp', array(
            'pages' => $pages
        ));
    }

    public function add($platform) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = PageSeo::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                $validator = Validator::make($this->request->all(), [
                    'seo-title-en' => array('required'),
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
                        $img = Image::make( Input::file('image') )->orientate();
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

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = PageSeo::find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
            $this->request->session()->flash('success-message', 'Image removed');

            return redirect('cms/pages/edit/'.$item->id);
        }

        return redirect('cms');
    }


    public function export($platform) {
        
        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $from = Request::input('from');
        $to = Request::input('to');

        $groups['Seo'] = [];
        $list = PageSeo::where('platform', $platform)->get();
        foreach ($list as $item) {
            $groups['Seo'][$item->id] = [];                 
            foreach ($item->translatedAttributes as $field) {
                $groups['Seo'][$item->id][$field] = [
                    $item->translateOrNew($from)->$field,
                    $item->translateOrNew($to)->$field,
                ];    
            }
        }
        
        $dir = storage_path().'/app/public/xls/';
        if(!is_dir($dir)) {
            mkdir($dir);
        }

        $flat = [];

        foreach ($groups as $gname => $glist) {

            $title = 'Reviews Questions';
            $flat[$title] = [];
            
            foreach ($glist as $item_id => $grow) {
                $found = false;

                foreach ($grow as $key => $value) {
                    if(!empty($value[0]) || !empty($value[1]) ) {
                        $found = true;
                        break;
                    }
                }

                if($found) {
                    foreach ($grow as $key => $value) {
                        $flat[$title][] = [$item_id.'.'.$key, $from => $value[0], ($to == $from ? $to.'-1': $to) =>  empty($value[1]) ? '' : $value[1] ];
                    }
                    $flat[$title][] = ['', '' , ''];
                }

            }
        }


        $export = new Export($flat);
        return Excel::download($export, $platform.'-seo-translations.xls');
    }


    public function import($platform) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $source = Request::input('source');
        $from = Request::input('from');
        $that = $this;

        $newName = '/tmp/'.str_replace(' ', '-', Input::file('table')->getClientOriginalName());
        copy( Input::file('table')->path(), $newName );

        $rows = Excel::toArray(new Import, $newName );

        if(!empty($rows)) {

            $objects = [];

            foreach($rows[0] as $row){
                if( $row[0] ) {
                    $arr = explode('.', $row[0]);
                    if(!isset($objects[$arr[0]])) {
                        $objects[$arr[0]] = [];
                    }
                    $objects[$arr[0]][$arr[1]] = $row[2];     

                }
            }

            foreach ($objects as $id => $obj) {
                $item = PageSeo::find( $id );
                if( $item ) {
                    $translation = $item->translateOrNew($from);
                    $column_names = Schema::getColumnListing($translation->getTable());
                    $rel_field = $column_names[1];
                    $translation->$rel_field = $item->id;

                    foreach ($obj as $key => $value) {
                        $translation->$key = $value; 
                    }
                    $translation->save();                                    
                }
            }

            Request::session()->flash('success-message', 'Translations save');
        }

        unlink($newName);
        
        $pages = PageSeo::where('platform', $platform)->get();

        return $this->ShowView('pages-'.$platform, array(
            'pages' => $pages
        ));
    }
}
