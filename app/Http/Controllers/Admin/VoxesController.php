<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Vox;
use App\Models\VoxIdea;
use App\Models\VoxCategory;
use App\Models\VoxQuestion;
use App\Models\VoxToCategory;
use App\Models\VoxScale;
use Illuminate\Support\Facades\Input;
use Request;
use Response;
use Route;
use Excel;


class VoxesController extends AdminController
{

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->types = [
            'hidden' => trans('admin.enums.type.hidden'),
            'normal' => trans('admin.enums.type.normal'),
            'home' => trans('admin.enums.type.home'),
            'user_details' => trans('admin.enums.type.user_details'),
        ];

        $this->question_types = [
            'single_choice' => trans('admin.enums.question-type.single_choice'),
            'multiple_choice' => trans('admin.enums.question-type.multiple_choice'),
            'scale' => trans('admin.enums.question-type.scale'),
        ];
    }

    public function list( ) {

    	return $this->showView('voxes', array(
            'voxes' => Vox::orderBy('type', 'DESC')->orderBy('id', 'DESC')->get()
        ));
    }

    public function add( ) {

        if(Request::isMethod('post')) {

            $newvox = new Vox;
            $this->saveOrUpdate($newvox);


            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$newvox->id);
        }

        return $this->showView('voxes-form', array(
            'types' => $this->types,
            'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
            'category_list' => VoxCategory::get(),
            'question_types' => $this->question_types,
        ));
    }

    public function delete( $id ) {
        Vox::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function edit( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $triggers = [];

            foreach($item->questions as $question) {
                $triggers[$question->id] = '';
                if ($question->question_trigger) {

                    foreach (explode(';', $question->question_trigger) as $v) {
                        $question_id = explode(':',$v)[0];

                        $q = VoxQuestion::find($question_id);

                        if (!empty(explode(':',$v)[1])) {
                            $answ = explode(':',$v)[1];
                            $triggers[$question->id] .= $q->question.': '.$answ.'<br/>';
                        } else {
                            $triggers[$question->id] .= $q->question.'<br/>';
                        }
                        
                    }
                }
            }

            if(Request::isMethod('post')) {

                $this->saveOrUpdate($item);
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/'.$this->current_page.'/edit/'.$item->id);
            }


            $trigger_question_id = null;
            $trigger_valid_answers = null;
            foreach ($question->vox->questions as $q) {
                if ($q->question_trigger) {
                    $trigger_list = explode(';', $q->question_trigger);
                    $first_triger = explode(':', $trigger_list[0]);
                    $trigger_question_id = $first_triger[0];
                    $trigger_valid_answers = !empty($first_triger[1]) ? $first_triger[1] : null;
                }
            }



            return $this->showView('voxes-form', array(
                'types' => $this->types,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
                'question_types' => $this->question_types,
                'item' => $item,
                'category_list' => VoxCategory::get(),
                'triggers' => $triggers,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function export( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $flist = [];

            foreach(config('langs') as $code => $lang_info) {
                $flist[$code] = [];

                if($item->questions->isNotEmpty()) {
                    foreach($item->questions as $question) {
                        $frow = [];
                        $frow['Number'] = $question->order;
                        $frow['Type'] = $question->type;
                        $frow['Question'] = $question->{'question:'.$code};
                        $frow['Valid answer'] = $question->is_control;
                        $frow['Go back to'] = $question->go_back;
                        $a = json_decode($question->{'answers:'.$code});
                        foreach ($a as $i => $ans) {
                            $frow['Answer '.($i+1)] = $ans;
                        }
                        $flist[$code][] = $frow;
                    }                    
                } else {
                    $flist[$code][] = [
                        'Number' => 1,
                        'Type' => 'single_choice',
                        'Question' => '',
                        'Valid answer' => '',
                        'Go back to' => '',
                        'Answer 1' => '',
                        'Answer 2' => '',
                        'Answer 3' => '',
                        'Answer 4' => '',
                    ];
                }

                $maxlen = 0;
                foreach ($flist[$code] as $r) {
                    if(count($r)>$maxlen) {
                        $maxlen = count($r);
                    }
                }
                foreach ($flist[$code] as $k => $r) {
                    if(count($flist[$code][$k])<$maxlen) {
                        $toadd = $maxlen - count($flist[$code][$k]);
                        for($i=0; $i < $toadd; $i++) {
                            $flist[$code][$k][] = '';
                        }
                    }
                }
            }


            Excel::create($item->title, function($excel) use ($flist) {
                foreach ($flist as $lang => $list) {
                    //dd($list);

                    $excel->sheet($lang, function($sheet) use ($list) {
                        $sheet->fromArray($list);

                    });
                }
            })->export('xls');

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }


    public function import( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $that = $this;

            Excel::load( Input::file('table')->path() , function($reader) use ($item, $that)  { //

                // Getting all results
                global $results;
                $results = [];
                $reader->each(function($sheet) {
                    global $results;
                    $results[$sheet->getTitle()] = $sheet->toArray();
                });

                $maxlen = 0;
                foreach ($results as $r) {
                    if(count($r)>$maxlen) {
                        $maxlen = count($r);
                    }
                }
                for($i=0;$i<$maxlen;$i++) {
                    $qdata = [
                        'order' => intval(current($results)[$i]['number']),
                        'type' => intval(current($results)[$i]['type']),
                        'is_control' => current($results)[$i]['valid_answer'],
                        'go_back' => current($results)[$i]['go_back_to'],
                        'question_scale' => null,
                        'question_trigger' => null,
                    ];
                    foreach ($results as $lang => $list) {
                        $qdata['question-'.$lang] = !empty($list[$i]['question']) ? $list[$i]['question'] : null;
                        $qdata['answers-'.$lang] = [];
                        for($q=1;$q<=10;$q++) {
                            if(!empty($list[$i]['answer_'.$q])) {
                                $qdata['answers-'.$lang][] = $list[$i]['answer_'.$q];
                            } else {
                                break;
                            }
                        }
                    }
                    if(!empty($item->questions[$i])) {
                        $qobj = $item->questions[$i];
                    } else {
                        $qobj = new VoxQuestion;
                        $qobj->vox_id = $item->id;
                    }

                    $that->saveOrUpdateQuestion($qobj, $qdata);
                }

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.imported'));

            });
            
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function import_quick( $id ) {
        $item = Vox::find($id);

        if(!empty($item) && Input::file('table')) {

            global $i;
            $i = $item->questions->last() ? intval($item->questions->last()->order)+1 : 1;

            $that = $this;

            Excel::load( Input::file('table')->path() , function($reader) use ($item, $that)  { //

                // Getting all results
                global $results, $i;
                $results = [];
                $reader->each(function($sheet) {
                    global $results;
                    $results[] = $sheet->toArray();
                });

                if(!empty($results)) {
                    $q = null;
                    $a = [];
                    foreach ($results as $row) {
                        $text = current($row);
                        if(empty($text)) {
                            if($q && !empty($a)) {
                                $qdata = [
                                    'order' => $i,
                                    'type' => 'single_choice',
                                    'is_control' => null,
                                    'go_back' => null,
                                    'question_scale' => null,
                                    'question_trigger' => null,
                                    'question-en' => $q,
                                    'answers-en' => $a,
                                ];

                                $qobj = new VoxQuestion;
                                $qobj->vox_id = $item->id;
                                $that->saveOrUpdateQuestion($qobj, $qdata);

                                //var_dump($qdata);

                                $q=null;
                                $a=[];
                                $i++;
                            }
                        } else {
                            if(empty($q)) {
                                $q = $text;
                            } else {
                                $a[] = $text;
                            }
                        }
                    }                    
                }

                //exit;

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.imported'));

            });
            
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function add_question( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $question = new VoxQuestion;
            $question->vox_id = $item->id;
            $this->saveOrUpdateQuestion($question);
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function edit_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox->id==$id) {

            $trigger_question_id = null;
            $trigger_valid_answers = null;

            foreach ($question->vox->questions as $q) {
                if ($q->question_trigger) {
                    $trigger_list = explode(';', $q->question_trigger);
                    $first_triger = explode(':', $trigger_list[0]);
                    $trigger_question_id = $first_triger[0];
                    $trigger_valid_answers = !empty($first_triger[1]) ? $first_triger[1] : null;
                }

                if($q->order>=$question->order) {
                    break;
                }
            }


            if(empty( $trigger_question_id )) {
                $prev_question = VoxQuestion::where('vox_id', $id)->where('order', '<', intVal($question->order) )->orderBy('order', 'DESC')->first();
                $trigger_question_id = $prev_question->id;
                $trigger_valid_answers = null;
            }

            if(Request::isMethod('post')) {

                $this->saveOrUpdateQuestion($question);
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-updated'));
                return redirect('cms/'.$this->current_page.'/edit/'.$id);
            }

            return $this->showView('voxes-form-question', array(
                'question' => $question,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
                'item' => $question->vox,
                'question_types' => $this->question_types,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers
            ));

        } else {
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }
    }

    public function delete_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox->id==$id) {

            $question->delete();

            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-deleted'));
            return redirect('cms/'.$this->current_page.'/edit/'.$id);

        } else {
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }
    }

    public function order_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox->id==$id) {
            $question->order = Request::input('val');
            $question->save();
            return Response::json( ['success' => true] );
        } else {
            return Response::json( ['success' => false] );
        }
    }

    public function change_question_text( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox->id==$id) {
            $translation = $question->translateOrNew('en');
            $translation->question = Request::input('val');
            $translation->save();
            return Response::json( ['success' => true] );
        } else {
            return Response::json( ['success' => false] );
        }
    }

    private function saveOrUpdate($item) {
        $item->type = $this->request->input('type');
        $item->save();

        VoxToCategory::where('vox_id', $item->id)->delete();
        if( !empty( Request::input('categories') )) {
            foreach(Request::input('categories') as $cat_id) {
                $vc = new VoxToCategory();
                $vc->vox_id = $item->id;
                $vc->vox_category_id = $cat_id;
                $vc->save();
            }   
        }

        foreach ($this->langs as $key => $value) {
            if(!empty($this->request->input('title-'.$key))) {
                $translation = $item->translateOrNew($key);
                $translation->vox_id = $item->id;
                $translation->slug = $this->request->input('slug-'.$key);
                $translation->title = $this->request->input('title-'.$key);
                $translation->description = $this->request->input('description-'.$key);
                $translation->seo_title = $this->request->input('seo_title-'.$key);
                $translation->seo_description = $this->request->input('seo_description-'.$key);
                $translation->save();
            }
        }
        $item->save();

    }

    private function saveOrUpdateQuestion($question, $data = null) {
        if(empty($data)) {
            $data = $this->request->input();
        }

        if(!empty($data['is_control_prev'])) {
            $question->is_control = $data['is_control_prev'];
        } else {
            $question->is_control = $data['is_control'];
        }
        
        $question->type = $data['type'];
        $question->go_back = $data['go_back'];
        $question->order = $data['order'];
        $question->vox_scale_id = $data['question_scale'];

        if(!empty( $data['triggers'] )) {
            $help_array = [];
            foreach($data['triggers'] as $i => $trg) {
                $help_array[] = $trg.( !empty( $data['answers-number'][$i] ) ? ':'.$data['answers-number'][$i] : '' );
            }
            $question->question_trigger = implode(';', $help_array);
        } else {
            $question->question_trigger = '';
        }
        
        $question->save();

        foreach ($this->langs as $key => $value) {
            if(!empty($data['question-'.$key])) {
                $translation = $question->translateOrNew($key);
                $translation->vox_question_id = $question->id;
                $translation->question = $data['question-'.$key];

                if(!empty( $data['answers-'.$key] )) {
                    $translation->answers = json_encode( $data['answers-'.$key] );
                } else {
                    $translation->answers = '';                            
                }

                $translation->save();
            }
        }
        $question->save();

    }

    public function ideas( ) {

        return $this->showView('vox-ideas', array(
            'ideas' => VoxIdea::orderBy('id', 'DESC')->get()
        ));
    }

    public function categories( ) {

        return $this->showView('vox-categories', array(
            'categories' => VoxCategory::orderBy('id', 'ASC')->get()
        ));
    }

    public function add_category( ) {

        if(Request::isMethod('post')) {
            $item = new VoxCategory;
            $item->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('category-name-'.$key))) {
                    $translation = $item->translateOrNew($key);
                    $translation->vox_category_id = $item->id;
                    $translation->name = $this->request->input('category-name-'.$key);
                    $translation->save();
                }
            }
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.category.added'));
            return redirect('cms/vox/categories');
        }

        return $this->showView('vox-categories-form');
    }

    public function delete_category( $id ) {
        VoxCategory::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.category.deleted') );
        return redirect('cms/vox/categories');
    }

    public function edit_category( $id ) {
        $item = VoxCategory::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                foreach ($this->langs as $key => $value) {
                    if(!empty($this->request->input('category-name-'.$key))) {
                        $translation = $item->translateOrNew($key);
                        $translation->vox_category_id = $item->id;
                        $translation->name = $this->request->input('category-name-'.$key);
                        $translation->save();
                    }
                }
                $item->save();
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.category.updated'));
                return redirect('cms/vox/categories');
            }

            return $this->showView('vox-categories-form', array(
                'item' => $item,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function scales() {

        return $this->showView('vox-scales', array(
            'scales' => VoxScale::orderBy('id', 'DESC')->get()
        ));
    }

    public function add_scale( ) {

        if(Request::isMethod('post')) {

            $ns = new VoxScale;
            $this->saveOrUpdateScale($ns);


            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.'.$this->current_subpage.'.added'));
            return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/edit/'.$ns->id);
        }

        return $this->showView('voxes-scale-form', array(
            'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
        ));
    }


    private function saveOrUpdateScale($item) {
        $item->title = $this->request->input('title');
        $item->save();

        foreach ($this->langs as $key => $value) {
            if(!empty($this->request->input('answers-'.$key))) {
                $translation = $item->translateOrNew($key);
                $translation->vox_scale_id = $item->id;
                $translation->answers = $this->request->input('answers-'.$key);
                $translation->save();
            }
        }
        $item->save();

    }

    public function edit_scale( $id ) {

        $item = VoxScale::find($id);

        if( $item ) {

            if(Request::isMethod('post')) {

                $this->saveOrUpdateScale($item);


                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.'.$this->current_subpage.'.updated'));
                return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/edit/'.$item->id);
            }

            return $this->showView('voxes-scale-form', array(
                'item' => $item,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
            ));

        }

    }


}