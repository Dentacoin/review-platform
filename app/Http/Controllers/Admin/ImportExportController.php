<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Question;

use App\Exports\MultipleLangSheetExport;
use App\Exports\Export;
use App\Imports\Import;
use App\Http\Requests;
use Carbon\Carbon;

use Validator;
use Request;
use Config;
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
                
                $dir = storage_path().'/app/public/xls/';
                if(!is_dir($dir)) {
                    mkdir($dir);
                }
                $fname = $dir.'export';

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
                return Excel::download($export, 'other-translations.xls');
            }


            if( Request::input('import') ) {
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

                    Request::session()->flash('success-message', 'Translations save');
                }

                unlink($newName);
                
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