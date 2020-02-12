<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;

use App\Models\Question;

use App\Http\Requests;

use Validator;
use Request;
use Config;
use Excel;
use Lang;

class ImportExportController extends AdminController
{

    public function list() {

        if(Request::isMethod('post')) {

            if( Request::input('export') ) {
                $from = Request::input('from');
                $to = Request::input('to');

                $groups['Question'] = [];
                $list = Question::get();
                foreach ($list as $item) {
                    $groups['Question'][$item->id] = [];                 
                    foreach ($item->translatedAttributes as $field) {
                        $groups['Question'][$item->id][$field] = [
                            $item->translateOrNew($from)->$field,
                            $item->translateOrNew($to)->$field,
                        ];    
                    }
                }

                $groups['trans'] = [];
                $groups['trans']['echo'] = 'echo';
                
                $dir = storage_path().'/app/public/xls/';
                if(!is_dir($dir)) {
                    mkdir($dir);
                }
                $fname = $dir.'export';

                $flat = [];

                foreach ($groups as $gname => $glist) {

                	if($gname=='trans') {
                		$title = 'Do not delete this sheet';
	                    $flat[$title][] = 'echo';
	                } else {
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
                }

                Excel::create($fname, function($excel) use ($flat) {
                    foreach ($flat as $sname => $rowlist) {
                        $excel->sheet($sname, function($sheet) use ($rowlist) {

                            $sheet->fromArray($rowlist);
                            //$sheet->setWrapText(true);
                            //$sheet->getStyle('D1:E999')->getAlignment()->setWrapText(true); 

                        });
                    }

                })->export('xls');
            }


            if( Request::input('import') ) {
                $source = Request::input('source');
                $from = Request::input('from');
                $that = $this;

                Excel::load( Input::file('table')->path() , function($reader) use ($from, $source, $that) {

                    $reader->each(function($sheet) use ($from, $source, $that) {
                        $rows = $sheet->toArray();

                        $objects = [];

                        foreach($rows as $row){
                            if( $row[0] ) {
                                $arr = explode('.', $row[0]);
                                if(!isset($objects[$arr[0]])) {
                                    $objects[$arr[0]] = [];
                                }
                                $objects[$arr[0]][$arr[1]] = $row[$from];     

                            }
                        }

                        foreach ($objects as $id => $obj) {
                            $item = Question::find( $id );
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

                    });
                    Request::session()->flash('success-message', 'Translations save');

                });
                
                return redirect('cms/export-import');
            }
        }

        $available_langs = config('langs');
        $attrs = array(
            'langs' => $available_langs,
        );

        return $this->ShowView('import-export', $attrs);
    }
}