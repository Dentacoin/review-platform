<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Vox;
use App\Models\VoxCategory;
use App\Models\VoxQuestion;
use App\Models\VoxToCategory;
use App\Models\VoxScale;
use App\Models\VoxBadge;
use Illuminate\Support\Facades\Input;
use Image;
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
        $this->stat_types = [
            '' => 'No',
            'standard' => 'Yes',
            'dependency' => 'Yes + Relationship',
        ];
    }

    public function list( ) {

    	return $this->showView('voxes', array(
            'voxes' => Vox::orderBy('type', 'DESC')->orderBy('id', 'DESC')->get(),
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
            'stat_types' => $this->stat_types,
        ));
    }

    public function delete( $id ) {
        Vox::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function delpic( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $item->hasimage = false;
            $item->save();
        }

        $this->request->session()->flash('success-message', 'Photo deleted!' );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function edit_field( $id, $field, $value ) {
        $item = Vox::find($id);

        if(!empty($item)) {
            if($field=='featured') {
                $item->$field = $value=='0' ? 0 : 1;
            }
            if($field=='type') {
                $item->$field = $value=='0' ? 'hidden' : 'normal';
            }
            $item->save();
        }
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
            foreach ($item->questions as $q) {
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
                'stat_types' => $this->stat_types,
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
            $item->checkComplex();
        
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

                if($q->order>=$question->order) {
                    break;
                }
                
                if ($q->question_trigger) {
                    $trigger_list = explode(';', $q->question_trigger);
                    $first_triger = explode(':', $trigger_list[0]);
                    $trigger_question_id = $first_triger[0];
                    $trigger_valid_answers = !empty($first_triger[1]) ? $first_triger[1] : null;
                }
            }


            if(empty( $trigger_question_id )) {
                $prev_question = VoxQuestion::where('vox_id', $id)->where('order', '<', intVal($question->order) )->orderBy('order', 'DESC')->first();
                $trigger_question_id = $prev_question ? $prev_question->id : $question->id;
                $trigger_valid_answers = null;
            }

            if(Request::isMethod('post')) {

                $this->saveOrUpdateQuestion($question);
                $question->vox->checkComplex();
            

                if(request('used_for_stats')=='standard' && !request('stats_fields')) {
                    Request::session()->flash('success-message', 'Please, select the demographic details which should be used for the statistics.');
                    return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question_id);
                } else {
                    Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-updated'));
                    return redirect('cms/'.$this->current_page.'/edit/'.$id);
                }
            }

            return $this->showView('voxes-form-question', array(
                'question' => $question,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
                'item' => $question->vox,
                'question_types' => $this->question_types,
                'stat_types' => $this->stat_types,
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
            $question->vox->checkComplex();

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

    public function reorder($id) {

        $list = Request::input('list');
        $i=1;
        foreach ($list as $qid) {
            $question = VoxQuestion::find($qid);
            if($question->vox_id==$id) {
                $question->order = $i;
                $question->save();
                $i++;
            }
        }

        return Response::json( ['success' => true] );
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
        $item->featured = $this->request->input('featured');
        $item->stats_featured = $this->request->input('stats_featured');
        $item->has_stats = $this->request->input('has_stats');
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
                $translation->stats_description = $this->request->input('stats_description-'.$key);
                
                $translation->save();
            }
        }
        $item->save();

        if( Input::file('photo') ) {
            $img = Image::make( Input::file('photo') )->orientate();
            $item->addImage($img);
        }
        if( Input::file('photo-social') ) {
            $img = Image::make( Input::file('photo-social') )->orientate();
            $item->addSocialImage($img);
        }


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
        $question->order = $data['order'];
        $question->stats_featured = !empty($data['stats_featured']);
        $question->stats_fields = !empty($data['stats_fields']) ? $data['stats_fields'] : [];
        $question->vox_scale_id = !empty($data['question_scale']) ? $data['question_scale'] : null;;
        if( !empty($data['trigger_type']) ) {
            $question->trigger_type = $data['trigger_type'];
        }

        $question->used_for_stats = !empty($data['used_for_stats']) ? $data['used_for_stats'] : null;;
        $question->stats_relation_id = $question->used_for_stats=='dependency' ? $data['stats_relation_id'] : null;
        $question->stats_answer_id = $question->used_for_stats=='dependency' ? $data['stats_answer_id'] : null;

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
                if(!empty( $data['stats_title-'.$key] )) {
                    $translation->stats_title = $data['stats_title-'.$key];                    
                }

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

    public function faq() {

        $pathToFile = base_path().'/resources/lang/en/faq.php';
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



    public function badges() {
            
        if( Input::file('photo') && request('id') ) {
            $item = VoxBadge::find( request('id') );
            if($item) {
                $img = Image::make( Input::file('photo') )->orientate();
                $item->addImage($img);

                $voxes = Vox::whereNotNull('hasimage_social')->get();
                foreach ($voxes as $v) {
                    $v->regenerateSocialImages();
                }
            }
        }


        return $this->showView('voxes-badges', array(
            'items' => VoxBadge::get()
        ));

    }


    public function delbadge($id) {
            
        $item = VoxBadge::find( $id );
        if($item) {
            $item->delImage();

            $voxes = Vox::whereNotNull('hasimage_social')->get();
            foreach ($voxes as $v) {
                $v->regenerateSocialImages();
            }
        }

        Request::session()->flash('success-message', 'Badge deleted');
        return redirect('cms/'.$this->current_page.'/badges');

    }



}