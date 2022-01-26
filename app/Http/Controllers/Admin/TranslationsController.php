<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\MultipleLangSheetExport;
use App\Exports\Export;
use App\Imports\Import;

use Validator;
use Lang;
use Auth;

class TranslationsController extends AdminController {

	public function add() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $list = Lang::get($this->current_subpage, array(), $source);
        $target_list = Lang::get($this->current_subpage, array(), $target);
        $flist = [];
        foreach ($list as $key => $value) {
            $flist[] = [$key, $value, !empty($target_list[$key]) ? $target_list[$key] : ''  ];
        }

        $dir = storage_path().'/app/public/xls/';
        if(!is_dir($dir)) {
            mkdir($dir);
        }

        $export = new Export($flist);
        $file_to_export = Excel::download($export, 'translations.xls');
        ob_end_clean();
        return $file_to_export;
    }

    public function export_missing($subpage=null, $source=null, $target=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $list = Lang::get($this->current_subpage, array(), $source);
        $target_list = Lang::get($this->current_subpage, array(), $target);
        $flist = [];
        foreach ($list as $key => $value) {
            if(empty($target_list[$key])) {
                $flist[] = [$key, $value, ''];
            }
        }

        $dir = storage_path().'/app/public/xls/';
        if(!is_dir($dir)) {
            mkdir($dir);
        }

        $export = new Export($flist);
        $file_to_export = Excel::download($export, 'missing-translations.xls');
        ob_end_clean();
        return $file_to_export;
    }

    public function import($subpage=null, $source=null, $target=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        foreach (config('langs')['admin'] as $lang => $bla) {
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'translator'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $available_langs = config('langs')['admin'];

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
        
        $translations_count_arr = [];

        foreach($this->langs as $key => $lang_info) {
            $i = 0;

            foreach (Lang::get($this->current_subpage, array(), $key) as $k => $v) {
                if(!empty($v)) {
                    $i++;
                }
            }

            $translations_count_arr[$key] = $i;
        }

        $all_translations_count = 0;
        foreach ($translations_count_arr as $key => $value) {
            if($all_translations_count < $value ) {
                $all_translations_count = $value;
            }
        }

        $attrs = array(
            'source' => $source,
            'target' => $target,
            'langs' => $available_langs,
            'source_arr' => $sa_new,
            'target_arr' => $ta,
            'reload' => request('reload'),
            'reloaded' => request('reloaded'),
            'translations_count_arr' => $translations_count_arr,
            'all_translations_count' => $all_translations_count
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