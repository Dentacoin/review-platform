<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

use App\Exports\MultipleLangSheetExport;
use App\Exports\Export;
use App\Imports\Import;
use App\Http\Requests;

use Validator;
use Lang;

class TranslationsController extends AdminController {

	public function add() {

        $validator = Validator::make($this->request->all(), [
            'key' => array('required'), //, 'regex:/[\W.]+/'
            'val' => array('required'),
            'source' => array('required'),
            'target' => array('required'),
        ]);

        if ($validator->fails()) {
            return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/'.$this->request->input('source').'/'.$this->request->input('target'))
            ->withInput()
            ->withErrors($validator);
        } else {
            $oldfile = Lang::get($this->current_subpage, array(), $this->request->input('source'));
            if(!is_array($oldfile)) {
                $oldfile  = array();
            }
            if(!isset($oldfile[$this->request->input('key')])) {
                $oldfile[trim($this->request->input('key'))] = $this->request->input('val');
            }
            $this->translations_save($this->request->input('source'), $oldfile);

            $this->request->session()->flash('warning-message', 'Sentence added. Please wait for reload.');
            return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/'.$this->request->input('source').'/'.$this->request->input('target').'/?reload=1');
        }
	}

	public function update() {

		$validator = Validator::make($this->request->all(), [
            'source' => array('required'),
            'target' => array('required'),
        ]);

        if ($validator->fails()) {
            return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/'.$this->request->input('source').'/'.$this->request->input('target'))
            ->withInput()
            ->withErrors($validator);
        } else {
            $newfile = array();
            $keys = Lang::get($this->current_subpage, array(), $this->request->input('source'));
            foreach (Input::all() as $key => $value) {
                $key = str_replace('|', '.', $key);
                if(isset($keys[$key])) {
                    $newfile[$key] = is_array($keys[$key]) ? json_decode( stripslashes($value), true) : $value;
                }
            }

            $this->translations_save($this->request->input('target'), $newfile);

            $this->request->session()->flash('warning-message', 'Sentence updated. Please wait for reload.');
            return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/'.$this->request->input('source').'/'.$this->request->input('target').'/?reload=1');
        }
	}

    public function export($subpage=null, $source=null, $target=null) {

        $list = Lang::get($this->current_subpage, array(), $source);
        $target_list = Lang::get($this->current_subpage, array(), $target);
        //dd($list);
        $flist = [];
        foreach ($list as $key => $value) {
            $flist[] = [$key, $value, isset($target_list[$key]) ? $target_list[$key] : ''  ];
        }
        //dd($flist);

        $dir = storage_path().'/app/public/xls/';
        if(!is_dir($dir)) {
            mkdir($dir);
        }

        $export = new Export($flist);
        return Excel::download($export, 'translations.xls');
    }

    public function import($subpage=null, $source=null, $target=null) {

        if(!empty(Input::file('table'))) {

            $newName = '/tmp/'.str_replace(' ', '-', Input::file('table')->getClientOriginalName());
            copy( Input::file('table')->path(), $newName );

            $results = Excel::toArray(new Import, $newName );

            if(!empty($results)) {
                // Getting all results
                $proper = [];

                foreach($results[0] as $k => $v) {
                    $key = current($v);

                    next($v);
                    next($v);
                    $text = current($v);

                    if(!empty($text)) {
                        $proper[$key] = str_replace('"', '', $text);
                    }
                }
                //dd($proper);
                $this->translations_save($target, $proper);

                unlink($newName);

                $this->request->session()->flash('success-message', trans('admin.page.translations.imported'));

            }
        } else {
            $this->request->session()->flash('error-message', 'Please first upload a file.');
        }
        
        return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/'.$source.'/'.$source);
    }

	public function delete($subpage=null, $source=null, $target=null, $delid=null) {

        foreach (config('langs') as $lang => $bla) {
            $keysarr = Lang::get($this->current_subpage, array(), $lang);
            if(isset($keysarr[$delid])) {
                unset($keysarr[$delid]);
                $this->translations_save($lang, $keysarr);
            }
        }

        $this->request->session()->flash('warning-message', 'Sentence deleted. Please wait for reload.');
        return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/'.$source.'/'.$target.'/?reload=1');
	}

    public function list($subpage=null, $source=null, $target=null) {

        $available_langs = config('langs');

        if($this->user->role=='translator') {
            $source = $this->user->lang_from;
            $target = $this->user->lang_to;
        } else {
            $source = isset($available_langs[$source]) ? $source : key($available_langs);
            $target = isset($available_langs[$target]) ? $target : key($available_langs);
        }

        $sa = Lang::get($this->current_subpage, array(), $source);
        if(!is_array($sa)) {
            $sa = [];
        }
        $ta = Lang::get($this->current_subpage, array(), $target);
        if(!is_array($ta)) {
            $ta = [];
        }

        ksort($sa);
        $sa_new = [];
        foreach ($sa as $key => $value) {
            $arr = explode('.', $key);
            if($arr[0]=='popup' || $arr[0]=='page' || $arr[0]=='enums') {
                $nk = $arr[0].'.'.$arr[1];
            } else {
                $nk = $arr[0];
            }
            if(!isset($sa_new[$nk])) {
                $sa_new[$nk] = [];
            }
            $sa_new[$nk][$key] = $value;
        }

        $attrs = array(
            'source' => $source,
            'target' => $target,
            'langs' => $available_langs,
            'source_arr' => $sa_new,
            'target_arr' => $ta,
            'reload' => request('reload'),
            'reloaded' => request('reloaded'),
        );

        return $this->ShowView('translations', $attrs);
    }

    private function translations_save($lang, $data) {
    	$output = '<?php

return [
';
		foreach ($data as $key => $value) {
            
            if(is_array($value)) {
                $output .= "'".$key."' => ".var_export($value, true).",
";
            } else {
                $v = str_replace('\\\\', "\\", $value);
                $v = str_replace("\\'", "'", $v);
                $v = str_replace("'", "\\'", $v);
                
                $output .= "'".$key."' => '".$v."',
";
            }

                
		}
		$output .= '
];';
		$pathToFile = base_path().'/resources/lang/'.$lang.'/'.$this->current_subpage.'.php';
		$dirName = dirname($pathToFile);
		if(!is_dir($dirName)) {
			mkdir($dirName);
		}
		file_put_contents($pathToFile, $output);
    }
}
