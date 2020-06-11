<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\DentistTestimonial;
use App\Exports\Export;
use App\Imports\Import;

use Validator;
use Response;
use Request;
use Image;

class TestimonialSliderController extends AdminController {

    public function list() {

    	$testimonials = DentistTestimonial::orderBy('id', 'desc')->get();

    	return $this->showView('testimonial-slider', [
			'testimonials' => $testimonials,
		]);
    }

    public function add() {

        $validator = Validator::make($this->request->all(), [
            'name-en' => array('required'),
            'image' => array('required'),
        ]);

        if ($validator->fails()) {
            return redirect('cms/trp/testimonials')
            ->withInput()
            ->withErrors($validator);
        } else {
            $newtestimonial = new DentistTestimonial; 
            $newtestimonial->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('name-'.$key))) {
                    $translation = $newtestimonial->translateOrNew($key);
                    $translation->dentist_testimonial_id = $newtestimonial->id;
                    $translation->name = $this->request->input('name-'.$key);
                    $translation->description = $this->request->input('description-'.$key);
                    $translation->job = $this->request->input('job-'.$key);
                }
                $translation->save();
            }

            if( Request::file('image') && Request::file('image')->isValid() ) {
                $img = Image::make( Input::file('image') )->orientate();
                $newtestimonial->addImage($img);
            }

            $this->request->session()->flash('success-message', trans('Testimonial added') );
            return redirect('cms/trp/testimonials');
        }
    }

    public function edit( $id ) {
        $item = DentistTestimonial::find($id);

        if(!empty($item)) {

        	if (Request::isMethod('post')) {
        		$validator = Validator::make($this->request->all(), [
		            'name-en' => array('required'),
	            ]);

	            if ($validator->fails()) {
		            return redirect('cms/trp/testimonials/edit/'.$item->id)
		            ->withInput()
		            ->withErrors($validator);
		        } else {

                    foreach ($this->langs as $key => $value) {
                        if(!empty($this->request->input('name-'.$key))) {
                            $translation = $item->translateOrNew($key);
                            $translation->dentist_testimonial_id = $item->id;
                            $translation->name = $this->request->input('name-'.$key);
                            $translation->description = $this->request->input('description-'.$key);
                            $translation->job = $this->request->input('job-'.$key);
                        }
                        $translation->save();
                    }
		            
		            $item->save();

		            $this->request->session()->flash('success-message', trans('Testimonial edited') );
		            return redirect('cms/trp/testimonials');
		        }
        	}

            return $this->showView('testimonial-slider-edit', array(
                'item' => $item,
            ));
        } else {
            return redirect('cms/trp/testimonials');
        }
    }

    public function add_avatar( $id ) {
        $item = DentistTestimonial::find($id);

        if( Request::file('image') && Request::file('image')->isValid() ) {
            $img = Image::make( Input::file('image') )->orientate();
            $item->addImage($img);

            return Response::json(['success' => true, 'thumb' => $item->getImageUrl(), 'name' => '' ]);
        }
    }

    public function delete( $id ) {
        DentistTestimonial::destroy( $id );

        $this->request->session()->flash('success-message', 'Testimonial deleted' );
        return redirect('cms/trp/testimonials');
    }


    public function export() {


        $from = Request::input('from');
        $to = Request::input('to');

        $groups['DentistTestimonial'] = [];
        $list = DentistTestimonial::get();
        foreach ($list as $item) {
            $groups['DentistTestimonial'][$item->id] = [];                 
            foreach ($item->translatedAttributes as $field) {
                $groups['DentistTestimonial'][$item->id][$field] = [
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
        return Excel::download($export, 'testimonials-translations.xls');
    }


    public function import() {

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
                $item = DentistTestimonial::find( $id );
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
        
        $testimonials = DentistTestimonial::orderBy('id', 'desc')->get();

        return $this->showView('testimonial-slider', [
            'testimonials' => $testimonials,
        ]);
    }

}