<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\VoxToCategory;
use App\Models\VoxCategory;
use App\Models\VoxQuestion;
use App\Models\VoxRelated;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\VoxBadge;
use App\Models\User;
use App\Models\Vox;

use App\Exports\MultipleLangSheetExport;
use App\Exports\Export;
use App\Imports\Import;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Image;
use Route;
use DB;

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
        $this->stat_top_answers = [
            '' => '-',
            'top_3' => 'TOP 3',
            'top_5' => 'TOP 5',
            'top_10' => 'TOP 10',
        ];
    }

    public function list( ) {

        $error = false;
        $error_arr = [];

        $voxes_stats = Vox::where('has_stats', 1)->where('type', '!=', 'hidden')->get();

        foreach ($voxes_stats as $voxes_stat) {

            if(empty($voxes_stat->stats_description)) {
                $error_arr[] = [
                    'error' => 'Missing stats description',
                    'link' => 'https://dentavox.dentacoin.com/cms/vox/edit/'.$voxes_stat->id,
                ];

                $error = true;
            }

            if(empty($voxes_stat->stats_questions)) {
                $error_arr[] = [
                    'error' => 'Missing stats questions',
                    'link' => 'https://dentavox.dentacoin.com/cms/vox/edit/'.$voxes_stat->id,
                ];

                $error = true;
            } else {

                foreach ($voxes_stat->stats_questions as $stat) {
                    if(empty($stat->stats_title_question) && empty($stat->stats_title) && empty($stat->stats_title_question)) {
                        $error_arr[] = [
                            'error' => 'Missing stats question title',
                            'link' => 'https://dentavox.dentacoin.com/cms/vox/edit/'.$voxes_stat->id.'/question/'.$stat->id.'/',
                        ];
                        $error = true;
                    }
                    if(empty($stat->stats_fields) && $stat->used_for_stats != 'dependency') {
                        $error_arr[] = [
                            'error' => 'Missing stats question demographics',
                            'link' => 'https://dentavox.dentacoin.com/cms/vox/edit/'.$voxes_stat->id.'/question/'.$stat->id.'/',
                        ];
                        $error = true;
                    }
                }
            }
        }

    	return $this->showView('voxes', array(
            'voxes' => Vox::orderBy('sort_order', 'ASC')->get(),
            'active_voxes_count' => Vox::where('type', '!=', 'hidden')->count(),
            'hidden_voxes_count' => Vox::where('type', 'hidden')->count(),
            'error_arr' => $error_arr,
            'error' => $error,
        ));
    }

    public function reorderVoxes() {

        $list = Request::input('list');
        $i=1;
        foreach ($list as $qid) {
            $vox = Vox::find($qid);
            if( $vox ) {
                $vox->sort_order = $i;
                $vox->save();
                $i++;
            }
        }

        return Response::json( ['success' => true] );
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
            'stat_top_answers' => $this->stat_top_answers,
            'all_voxes' => Vox::orderBy('sort_order', 'ASC')->get(),
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
                $item->last_count_at = null;
            }
            if($field=='has_stats') {
                $item->$field = $value=='0' ? 0 : 1;
            }
            if($field=='stats_featured') {
                $item->$field = $value=='0' ? 0 : 1;
            }
            $item->save();
        }
    }

    public function edit( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $triggers = [];
            $linked_triggers = [];

            foreach($item->questions as $question) {
                $triggers[$question->id] = '';
                if ($question->question_trigger) {

                    foreach (explode(';', $question->question_trigger) as $v) {
                        $question_id = explode(':',$v)[0];

                        if($question_id==-1) {
                            $triggers[$question->id] .= 'Same as previous<br/>';
                            $linked_triggers[] = $question->id;
                        } else if(!is_numeric($question_id)) {
                            $triggers[$question->id] .= ($question_id == 'age_groups' ? 'Age groups' : ($question_id == 'gender' ? 'Gender' : config('vox.details_fields.'.$question_id)['label'])).' : '.explode(':',$v)[1];
                        } else {

                            $q = VoxQuestion::find($question_id);

                            if(!empty($q)) {
                                if (!empty(explode(':',$v)[1])) {
                                    $answ = explode(':',$v)[1];
                                    $triggers[$question->id] .= $q->question.': '.$answ.'<br/>';
                                } else {
                                    $triggers[$question->id] .= $q->question.'<br/>';
                                }                            
                            }
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


            $q_triggers_arr = [];
            $q_trigger_obj = [];

            if ($item->questions->isNotEmpty()) {
                foreach ($item->questions as $iq) {
                    if (!empty($iq->question_trigger) && $iq->question_trigger != '-1') {
                        $trgs = explode(';', $iq->question_trigger);

                        foreach ($trgs as $trg) {
                            if(!in_array(explode(':', $trg)[0], $q_triggers_arr)) {
                                $q_triggers_arr[] = explode(':', $trg)[0];
                            }
                            
                        }
                    }
                }
            }

            if (!empty($q_triggers_arr)) {
                foreach ($q_triggers_arr as $q_trigger) {
                    $q_trigger_obj[] = is_numeric($q_trigger) ? VoxQuestion::find($q_trigger) : $q_trigger ;
                }
            }

            $slist = VoxScale::get();
            $scales = [];
            foreach ($slist as $sitem) {
                $scales[$sitem->id] = $sitem;
            }


            $error = false;
            $error_arr = [];

            if($item->has_stats) {

                if(empty($item->stats_description)) {
                    $error_arr[] = [
                        'error' => 'Missing stats description',
                    ];

                    $error = true;
                }

                if(empty($item->stats_questions)) {
                    $error_arr[] = [
                        'error' => 'Missing stats questions',
                    ];

                    $error = true;
                } else {

                    foreach ($item->stats_questions as $stat) {
                        if(empty($stat->stats_title_question) && empty($stat->stats_title) && empty($stat->stats_title_question)) {
                            $error_arr[] = [
                                'error' => 'Missing stats question title',
                                'link' => 'https://dentavox.dentacoin.com/cms/vox/edit/'.$item->id.'/question/'.$stat->id.'/',
                            ];
                            $error = true;
                        }
                        if(empty($stat->stats_fields) && $stat->used_for_stats != 'dependency') {
                            $error_arr[] = [
                                'error' => 'Missing stats question demographics',
                                'link' => 'https://dentavox.dentacoin.com/cms/vox/edit/'.$item->id.'/question/'.$stat->id.'/',
                            ];
                            $error = true;
                        }
                    }
                }
            }

            return $this->showView('voxes-form', array(
                'types' => $this->types,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
                'question_types' => $this->question_types,
                'stat_types' => $this->stat_types,
                'stat_top_answers' => $this->stat_top_answers,
                'scales_arr' => $scales,
                'item' => $item,
                'category_list' => VoxCategory::get(),
                'triggers' => $triggers,
                'linked_triggers' => $linked_triggers,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers,
                'all_voxes' => Vox::orderBy('sort_order', 'ASC')->get(),
                'q_trigger_obj' => $q_trigger_obj,
                'error_arr' => $error_arr,
                'error' => $error,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function export( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            ini_set('max_execution_time', 0);
            set_time_limit(0);
            ini_set('memory_limit','1024M');

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

            return (new MultipleLangSheetExport($flist))->download($item->title.'-translations.xlsx');

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }


    public function import( $id ) {

        return 'This doesn\'t work. Tell the developer about it';

        $item = Vox::find($id);

        if(!empty($item)) {

            $that = $this;

            $newName = '/tmp/'.str_replace(' ', '-', Input::file('table')->getClientOriginalName());
            copy( Input::file('table')->path(), $newName );

            $results = Excel::toArray(new Import, $newName );

            dd($results);

            if(!empty($results)) {
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
                        //problemut e tuk, che $lang ne e ezik a chislo
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
            }

            unlink($newName);

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.imported'));
            
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function import_quick( $id ) {
        $item = Vox::find($id);

        if(!empty($item) && Input::file('table')) {

            session()->pull('brackets');

            global $i;
            $i = $item->questions->last() ? intval($item->questions->last()->order)+1 : 1;

            $that = $this;

            $newName = '/tmp/'.str_replace(' ', '-', Input::file('table')->getClientOriginalName());
            copy( Input::file('table')->path(), $newName );

            $results = Excel::toArray(new Import, $newName );

            if(!empty($results)) {
                if(is_array($results[0]) && count($results[0])>10) {
                    $results = $results[0];
                }
                $q = null;
                $a = [];
                foreach ($results as $row) {
                    $text = current($row);

                    if(empty($text) && $text != '0') {
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

            unlink($newName);
            
            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.imported'));
            
            
            if (!empty(session('brackets'))) {
                if (!empty(session('brackets')['q_br'])) {
                    Request::session()->flash('warning-message', 'Missing or more than necessary question/s tooltip brackets: '.implode(' ;     ', session('brackets')['q_br'] ));
                }
                if (!empty(session('brackets')['a_br'])) {
                    Request::session()->flash('error-message', 'Missing or more than necessary answers/s tooltip brackets: '.implode(' ;     ', session('brackets')['a_br'] ));
                }
            }
            
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

            if(request('used_for_stats')=='standard' && !request('stats_fields')) {
                Request::session()->flash('error-message', 'Please, select the demographic details which should be used for the statistics.');
                return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question->id);
            } else if ($question->type == 'scale' && request('used_for_stats')=='standard' && !request('stats_fields') && !request('stats_scale_answers')) {
                Request::session()->flash('error-message', 'Please, select the demographic details and scale answers which should be used for the statistics.');
                return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question->id);
            } else if ($question->type == 'scale' && !request('question_scale')) {
                Request::session()->flash('error-message', 'Please, pick a scale.');
                return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question->id);
            } else if(!empty(request('used_for_stats')) && empty(request('stats_title_question')) && empty(request('stats_title-en'))) {
                Request::session()->flash('error-message', 'Stats title required' );
                return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question->id);
            } else {
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-added'));
                return redirect('cms/'.$this->current_page.'/edit/'.$id);
            }
        

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function edit_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox_id==$id) {

            $trigger_question_id = null;
            $trigger_valid_answers = null;

            $triggers_ids = [];
            $trigger_type = null;
            $invert_trigger_logic = null;

            foreach ($question->vox->questions as $q) {

                if($q->order>=$question->order) {
                    break;
                }

                
                if ($q->question_trigger) {
                    if($q->question_trigger!='-1') {
                        $triggers_ids = [];
                        $trigger_list = explode(';', $q->question_trigger);
                        $first_triger = explode(':', $trigger_list[0]);
                        $trigger_question_id = $first_triger[0];
                        $trigger_valid_answers = !empty($first_triger[1]) ? $first_triger[1] : null;

                        foreach (explode(';', $q->question_trigger) as $va) {
                            if(!empty(explode(':', $va)[0])) {
                                $triggers_ids[explode(':', $va)[0]] = !empty(explode(':', $va)[1]) ? explode(':', $va)[1] : '';
                            }                            
                        }
                        $trigger_type = $q->trigger_type;
                        $invert_trigger_logic = $q->invert_trigger_logic;
                    }
                }
            }

            if(empty( $trigger_question_id )) {
                $prev_question = VoxQuestion::where('vox_id', $id)->where('order', '<', intVal($question->order) )->orderBy('order', 'DESC')->first();
                $trigger_question_id = $prev_question ? $prev_question->id : '';
                $trigger_valid_answers = null;
            }

            if(Request::isMethod('post')) {

                $this->saveOrUpdateQuestion($question);
                $question->vox->checkComplex();

            
                if(request('used_for_stats')=='standard' && !request('stats_fields')) {
                    Request::session()->flash('error-message', 'Please, select the demographic details which should be used for the statistics.');
                    return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question_id);
                } else if ($question->type == 'scale' && request('used_for_stats')=='standard' && !request('stats_fields') && !request('stats_scale_answers')) {
                    Request::session()->flash('error-message', 'Please, select the demographic details and scale answers which should be used for the statistics.');
                    return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question_id);
                } else if ($question->type == 'scale' && !request('question_scale')) {
                    Request::session()->flash('error-message', 'Please, pick a scale.');
                    return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question_id);
                } else if(!empty(request('used_for_stats')) && empty(request('stats_title_question')) && empty(request('stats_title-en'))) {
                    Request::session()->flash('error-message', 'Stats title required' );
                    return redirect('cms/'.$this->current_page.'/edit/'.$id.'/question/'.$question_id);
                } else {
                    Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-updated'));
                    return redirect('cms/'.$this->current_page.'/edit/'.$id);
                }
            }

            $question_answers_count = DB::table('vox_answers')
            ->join('users', 'users.id', '=', 'vox_answers.user_id')
            ->whereNull('users.deleted_at')
            ->whereNull('vox_answers.deleted_at')
            ->where('vox_id', $id )
            ->where('question_id', $question_id)
            ->where('is_completed', 1)
            ->where('is_skipped', 0)
            ->where('answer', '!=', 0)
            ->select('answer', DB::raw('count(*) as total'))
            ->groupBy('answer')
            ->get()
            ->pluck('total', 'answer')
            ->toArray();

            $error = false;
            $error_arr = [];

            if($question->used_for_stats) {

                if(empty($question->stats_title_question) && empty($question->stats_title) && empty($question->stats_title_question)) {
                    $error_arr[] = [
                        'error' => 'Missing stats question title',
                    ];
                    $error = true;
                }
                if(empty($question->stats_fields) && $question->used_for_stats != 'dependency') {
                    $error_arr[] = [
                        'error' => 'Missing stats question demographics',
                    ];
                    $error = true;
                }
            }

            return $this->showView('voxes-form-question', array(
                'error' => $error,
                'error_arr' => $error_arr,
                'question' => $question,
                'question_answers_count' => $question_answers_count,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
                'item' => $question->vox,
                'question_types' => $this->question_types,
                'stat_top_answers' => $this->stat_top_answers,
                'stat_types' => $this->stat_types,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers,
                'triggers_ids' => $triggers_ids,
                'trigger_type' => $trigger_type,
                'invert_trigger_logic' => $invert_trigger_logic,
            ));

        } else {
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }
    }

    public function delete_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox_id==$id) {

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

        if(!empty($question) && $question->vox_id==$id) {
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

        if(!empty($question) && $question->vox_id==$id) {
            $translation = $question->translateOrNew('en');
            $translation->question = Request::input('val');
            $translation->save();
            return Response::json( ['success' => true] );
        } else {
            return Response::json( ['success' => false] );
        }
    }

    private function saveOrUpdate($item) {

        if ($this->request->input('type') == 'normal' && $item->type == 'hidden') {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_URL => 'https://hub-app-api.dentacoin.com/internal-api/push-notification/',
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POSTFIELDS => array(
                    'data' => User::encrypt(json_encode(array('type' => 'new-survey')))
                )
            ));
             
            $resp = json_decode(curl_exec($curl));
            curl_close($curl);
        }

        $item->type = $this->request->input('type');
        $item->featured = $this->request->input('featured');
        $item->stats_featured = $this->request->input('stats_featured');
        $item->has_stats = $this->request->input('has_stats');
        $item->sort_order = $this->request->input('sort_order');

        $item->gender = $this->request->input('gender');
        $item->marital_status = $this->request->input('marital_status');
        $item->children = $this->request->input('children');
        $item->household_children = $this->request->input('household_children');
        $item->education = $this->request->input('education');
        $item->employment = $this->request->input('employment');
        $item->job = $this->request->input('job');
        $item->job_title = $this->request->input('job_title');
        $item->income = $this->request->input('income');
        $item->age = $this->request->input('age');
        $item->countries_ids = $this->request->input('countries_ids');
        $item->country_percentage = $this->request->input('country_percentage');
        $item->dentists_patients = $this->request->input('dentists_patients');
        $item->manually_calc_reward = !empty($this->request->input('manually_calc_reward')) ? 1 : null;
        $item->last_count_at = null;

        if (!empty($this->request->input('count_dcn_questions'))) {
            $trigger_qs = [];
            foreach ($this->request->input('count_dcn_questions') as $k => $v) {
                $trigger_qs[$this->request->input('count_dcn_questions')[$k]] = $this->request->input('count_dcn_answers')[$k];
            }

            $item->dcn_questions_triggers = $trigger_qs;
            $item->getLongestPath();
        }

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

        $cur_related = VoxRelated::where('vox_id', $item->id)->delete();
        if( Request::input('related_vox_id') ) {
            foreach (Request::input('related_vox_id') as $i => $ri) {
                if (!empty($this->request->input('related_vox_id')[$i])) {
                    $vr = new VoxRelated;
                    $vr->vox_id = $item->id;
                    $vr->related_vox_id = $this->request->input('related_vox_id')[$i];
                    $vr->save();
                }
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
        if( Input::file('photo-stats') ) {
            $img = Image::make( Input::file('photo-stats') )->orientate();
            $item->addSocialImage($img, 'for-stats');
        }


    }

    private function saveOrUpdateQuestion($question, $data = null, $justCopy = false ) {
        if(empty($data)) {
            $data = $this->request->input();
        }

        if(!empty($data['is_control_prev'])) {
            $question->is_control = $data['is_control_prev'];
        } else {
            $question->is_control = $data['is_control'];
        }
        
        $question->cross_check = !empty($data['cross_check']) ? $data['cross_check'] : null;
        $question->type = $data['type'];
        $question->order = $data['order'];
        $question->stats_featured = !empty($data['stats_featured']);
        $question->stats_top_answers = !empty($data['stats_top_answers']) ? $data['stats_top_answers'] : null;
        $question->stats_fields = !empty($data['stats_fields']) ? $data['stats_fields'] : [];
        $question->stats_scale_answers = !empty($data['stats_scale_answers']) ? json_encode($data['stats_scale_answers']) : '';
        $question->vox_scale_id = !empty($data['question_scale']) ? $data['question_scale'] : null;
        $question->dont_randomize_answers = !empty($data['dont_randomize_answers']) ? $data['dont_randomize_answers'] : null;
        if( !empty($data['trigger_type']) ) {
            $question->trigger_type = $data['trigger_type'];
        }

        $question->used_for_stats = !empty($data['used_for_stats']) ? $data['used_for_stats'] : null;
        $question->stats_relation_id = $question->used_for_stats=='dependency' ? $data['stats_relation_id'] : null;
        $question->stats_answer_id = $question->used_for_stats=='dependency' ? $data['stats_answer_id'] : null;
        $question->stats_title_question = !empty($data['stats_title_question']) ? $data['stats_title_question'] : null;


        if( $justCopy ) {
            $question->question_trigger = $data['question_trigger'];
        } else {
            if(!empty( $data['triggers'] )) {
                $help_array = [];
                foreach($data['triggers'] as $i => $trg) {
                    if(!empty($trg)) {
                        $help_array[] = $trg.( !empty( $data['answers-number'][$i] ) ? ':'.$data['answers-number'][$i] : '' );
                    }
                }

                if($question->question_trigger != implode(';', $help_array)) {
                    $q_vox = Vox::find($question->vox_id);
                    $q_vox->manually_calc_reward = null;
                    $q_vox->save();
                }
                $question->question_trigger = implode(';', $help_array);
            } else {
                $question->question_trigger = '';
            }
        }

        $question->invert_trigger_logic = $data['invert_trigger_logic'] ?? null;
        
        $question->save();

        if (!empty(session('brackets'))) {
            $sess = session('brackets');
        } else {                     
            $sess = [
                'q_br' => [],
                'a_br' => [],
            ];

            session([
                'brackets' => $sess
            ]);
        }

        foreach ($this->langs as $key => $value) {
            if(!empty($data['question-'.$key])) {
                $translation = $question->translateOrNew($key);
                $translation->vox_question_id = $question->id;
                if (strpos($data['question-'.$key], '[')) {
                    $first_bracket_q = substr_count($data['question-'.$key],"[");
                    $second_bracket_q = substr_count($data['question-'.$key],"]");
                    if ($first_bracket_q != 2 || $second_bracket_q != 2) {
                        $sess['q_br'][] = $data['question-'.$key];
                        //dd($sess);
                        //Request::session()->flash('warning-message', 'Missing or more than necessary question/s tooltip brackets');
                    }
                }
                $translation->question = $data['question-'.$key];
                if(!empty( $data['stats_title-'.$key] )) {
                    $translation->stats_title = $data['stats_title-'.$key];
                }
                if(!empty( $data['stats_subtitle-'.$key] )) {
                    $translation->stats_subtitle = $data['stats_subtitle-'.$key];
                } else {
                    $translation->stats_subtitle = '';
                }

                if(!empty( $data['answers-'.$key] )) {

                    foreach ($data['answers-'.$key] as $answ) {
                        if (strpos($answ, '[')) {
                            $first_bracket = substr_count($answ,"[");
                            $second_bracket = substr_count($answ,"]");
                            if ($first_bracket != 2 || $second_bracket != 2) {
                                $sess['a_br'][] = $answ;
                                //Request::session()->flash('error-message', 'Missing or more than necessary answer/s tooltip brackets');
                            }
                        }
                    }

                    $translation->answers = json_encode( $data['answers-'.$key] );

                } else {
                    $translation->answers = '';                            
                }

                $translation->save();
            }
        }
        $question->save();

        session([
            'brackets' => $sess
        ]);
    }

    public function categories( ) {

        return $this->showView('vox-categories', array(
            'categories' => VoxCategory::orderBy('id', 'ASC')->get()
        ));
    }

    public function add_category( ) {

        if(Request::isMethod('post')) {
            $item = new VoxCategory;
            $item->color = $this->request->input('color');
            $item->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('category-name-'.$key))) {
                    $translation = $item->translateOrNew($key);
                    $translation->vox_category_id = $item->id;
                    $translation->name = $this->request->input('category-name-'.$key);
                    $translation->save();
                }
            }


            if( Input::file('icon') ) {
                $img = Image::make( Input::file('icon') )->orientate();
                $item->addImage($img);
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

    public function delete_cat_image( $id ) {
        $item = VoxCategory::find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
        }

        $this->request->session()->flash('success-message', trans('Image Deleted') );
        return redirect('cms/vox/categories/edit/'.$id);
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
                $item->color = $this->request->input('color');
                $item->save();

                if( Input::file('icon') ) {
                    $img = Image::make( Input::file('icon') )->orientate();
                    $item->addImage($img);
                }
            
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

    public function explorer($vox_id=null,$question_id=null) {

        if($vox_id) {

            $show_pagination = true;

            $question = '';
            if ($question_id) {
               $question = VoxQuestion::find($question_id);
            }

            $vox = Vox::find($vox_id);

            $page = request('page');
            $page = max(1,intval($page));
            
            $ppp = request()->input( 'show_all' ) ? 1000 : 25;
            $respondents_shown = request()->input( 'show_all' ) ? '1000' : '25';
            $adjacents = 2;


            if (!empty($question_id)) {

                if (request()->input( 'country' )) {
                    $items_count = VoxAnswer::whereNull('is_admin')
                    ->where('question_id',$question_id )
                    ->select('vox_answers.*')
                    ->where('is_completed', 1)
                    ->where('is_skipped', 0)
                    ->where('answer', '!=', 0)
                    ->has('user')
                    ->join('users', 'vox_answers.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->count();
                } else {
                    $items_count = VoxAnswer::whereNull('is_admin')
                    ->where('question_id',$question_id )
                    ->where('is_completed', 1)
                    ->where('is_skipped', 0)
                    ->where('answer', '!=', 0)
                    ->has('user')
                    ->count();
                }
                
            } else {
                if (request()->input( 'country' )) {
                    $items_count = DcnReward::where('reference_id',$vox_id )
                    ->where('type', 'survey')
                    ->where('platform', 'vox')
                    ->select('dcn_rewards.*')
                    ->has('user')
                    ->join('users', 'dcn_rewards.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->count();
                } else {
                    $items_count = DcnReward::where('reference_id',$vox_id )
                    ->where('platform', 'vox')
                    ->where('type', 'survey')
                    ->has('user')->count();
                }
            }


            $show_button = true;
            if (request()->input( 'show_all' ) || $items_count <= 1000) {
                $show_button = false;
            }

            $show_all_button = false;
            if ($items_count <= 1000) {
                $show_all_button = true;
            }
       
            if (!empty($question_id)) {
                $question_respondents = VoxAnswer::whereNull('is_admin')
                ->where('question_id',$question_id )
                ->where('is_completed', 1)
                ->where('is_skipped', 0)
                ->where('answer', '!=', 0)
                ->has('user')
                ->select('vox_answers.*');

                if (request()->input( 'country' )) {
                    $order = request()->input( 'country' );
                    $question_respondents = $question_respondents
                    ->join('users', 'vox_answers.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->orderBy('countries.name', $order);
                } else if (request()->input( 'name' )) {
                    $order = request()->input( 'name' );
                    $question_respondents = $question_respondents
                    ->join('users', 'vox_answers.user_id', '=', 'users.id')
                    ->orderBy('users.name', $order);
                } else if (request()->input( 'taken' )) {
                    $order = request()->input( 'taken' );
                    $question_respondents = $question_respondents
                    ->orderBy('created_at', $order);
                } else if (request()->input( 'type' )) {
                    $order = request()->input( 'type' );
                    $question_respondents = $question_respondents
                    ->join('users', 'vox_answers.user_id', '=', 'users.id')
                    ->orderBy('users.is_dentist', $order)
                    ->orderBy('users.is_clinic', $order);
                } else {
                    $question_respondents = $question_respondents
                    ->orderBy('created_at', 'desc');
                }

                if (request()->input( 'show-more' )) {
                    $question_respondents = $question_respondents->get();
                    $show_button = false;
                    $show_all_button = false;
                    $show_pagination = false;
                    $respondents_shown = $items_count;
                } else {
                    $question_respondents = $question_respondents->skip( ($page-1)*$ppp )->take($ppp)->get();
                }                

                $respondents = '';

            } else {
                $respondents = DcnReward::where('reference_id',$vox_id )->where('platform', 'vox')->where('type', 'survey')->has('user')->select('dcn_rewards.*');
                if (request()->input( 'country' )) {
                    $order = request()->input( 'country' );
                    $respondents = $respondents
                    ->join('users', 'dcn_rewards.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->orderBy('countries.name', $order);
                } else if (request()->input( 'name' )) {
                    $order = request()->input( 'name' );
                    $respondents = $respondents
                    ->join('users', 'dcn_rewards.user_id', '=', 'users.id')
                    ->orderBy('users.name', $order);
                } else if (request()->input( 'taken' )) {
                    $order = request()->input( 'taken' );
                    $respondents = $respondents
                    ->orderBy('created_at', $order);
                } else if (request()->input( 'type' )) {
                    $order = request()->input( 'type' );
                    $respondents = $respondents
                    ->join('users', 'dcn_rewards.user_id', '=', 'users.id')
                    ->orderBy('users.is_dentist', $order)
                    ->orderBy('users.is_clinic', $order);
                } else {
                    $respondents = $respondents
                    ->orderBy('created_at', 'desc');
                }

                if (request()->input( 'show-more' )) {
                    $respondents = $respondents->get();
                    $show_button = false;
                    $show_all_button = false;
                    $show_pagination = false;
                    $respondents_shown = $items_count;
                } else {
                    $respondents = $respondents->skip( ($page-1)*$ppp )->take($ppp)->get();
                }                

                $question_respondents = '';
            }

            $total_count = $items_count;
            $total_pages = ceil($total_count/$ppp);

            //Here we generates the range of the page numbers which will display.
            if($total_pages <= (1+($adjacents * 2))) {
              $start = 1;
              $end   = $total_pages;
            } else {
              if(($page - $adjacents) > 1) { 
                if(($page + $adjacents) < $total_pages) { 
                  $start = ($page - $adjacents);            
                  $end   = ($page + $adjacents);         
                } else {             
                  $start = ($total_pages - (1+($adjacents*2)));  
                  $end   = $total_pages;               
                }
              } else {               
                $start = 1;                                
                $end   = (1+($adjacents * 2));             
              }
            }

            //If you want to display all page links in the pagination then
            //uncomment the following two lines
            //and comment out the whole if condition just above it.
            /*$start = 1;
            $end = $total_pages;*/

            $current_url = url('cms/vox/explorer/'.$vox_id.($question_id ? '/'.$question_id : '') );

            $pagination_link = (!empty(request()->input('show_all')) ? '&show_all='.request()->input( 'show_all' ) : '').(!empty(request()->input('country')) ? '&country='.request()->input( 'country' ) : '').(!empty(request()->input('name')) ? '&name='.request()->input( 'name' ) : '').(!empty(request()->input('taken')) ? '&taken='.request()->input( 'taken' ) : '').(!empty(request()->input('type')) ? '&type='.request()->input( 'type' ) : '');

            //dd( request()->input('country') );

            $viewParams = [
                'show_pagination' => $show_pagination,
                'question_respondents' => $question_respondents,
                'question' => $question,
                'vox_id' => $vox_id,
                'respondents' => $respondents,
                'vox' => $vox,
                'voxes' => Vox::orderBy('sort_order', 'asc')->get(),
                'count' =>($page - 1)*$ppp ,
                'start' => $start,
                'end' => $end,
                'total_pages' => $total_pages,
                'page' => $page,
                'current_url' => $current_url,
                'total_count' => $total_count,
                'show_button' => $show_button,
                'pagination_link' => $pagination_link,
                'show_all_button' => $show_all_button,
                'respondents_shown' => $respondents_shown,
            ];
        } else {
            $viewParams = [
                'voxes' => Vox::orderBy('sort_order', 'asc')->get(),
            ];
        }

        return $this->showView('voxes-explorer', $viewParams);
    }

    public function export_survey_data() {

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        if(Request::isMethod('post')) {

            $cols = [
                'Respondent ID',
                'Survey Date',
                'Country',
                'Age',
                'Sex',
            ];

            $cols2 = [
                '',
                '',
                '',
                '',
                '',
            ];

            $cols3 = [
                '',
                '',
                '',
                '',
                '',
            ];

            if(!empty(Request::input('demographics'))) {
                foreach(Request::input('demographics') as $dem) {
                    $cols[] = config('vox.stats_scales')[$dem];
                    $cols2[] = '';
                    $cols3[] = '';
                }
            }

            $vox = Vox::find( request('survey') );
            $slist = VoxScale::get();
            $scales = [];
            foreach ($slist as $sitem) {
                $scales[$sitem->id] = $sitem;
            }

            foreach( $vox->questions as $question ) {
                if( $question->type == 'single_choice' ) {
                    $cols[] = $question->question;
                    $cols2[] = '';
                } else if( $question->type == 'scale' ) {
                    $list = json_decode($question->answers, true);
                    foreach ($list as $l) {
                        $cols[] = $question->question;
                        $cols2[] = $l;
                    }
                } else if( $question->type == 'multiple_choice' ) {
                    $list = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true);
                    foreach ($list as $l) {
                        $cols[] = $question->question;
                        $cols2[] = mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l;
                    }
                }

                if($question->question_trigger) {

                    if($question->question_trigger == -1) {
                        $cols3[] = 'Triggers: SAME AS BEFORE';
                    } else {

                        $trigger_qs = [];

                        foreach (explode(';', $question->question_trigger) as $v)  {
                            $trigger_qs[] = explode(':', $v)[0];
                        }

                        $trigger_ans = [];
                        foreach (explode(';', $question->question_trigger) as $triggers)  {
                            if(isset(explode(':', $triggers)[1])) {

                                list($triggerId, $triggerAnswers) = explode(':', $triggers);

                                if(mb_strpos($triggerAnswers, '-')!==false) {
                                    list($from, $to) = explode('-', $triggerAnswers);

                                    $allowedAnswers = [];
                                    for ($i=$from; $i <= $to ; $i++) { 
                                        $allowedAnswers[] = json_decode(VoxQuestion::find($triggerId)->answers, true)[intval($i)-1];
                                    }

                                } else {
                                    $answer_names = [];
                                    foreach (explode(',', $triggerAnswers) as $value) {
                                        $answer_names[] = json_decode(VoxQuestion::find($triggerId)->answers, true)[intval($value)-1];
                                    }

                                    $allowedAnswers = $answer_names;
                                }

                                $trigger_ans[$triggerId] = $allowedAnswers;
                            }
                        }

                        if($trigger_qs) {

                            if(!empty($trigger_ans)) {
                                $triggers = [];

                                foreach ($trigger_qs as $tq) {
                                    if(isset($trigger_ans[$tq])) {
                                        $triggers[] = VoxQuestion::find($tq)->question.' - '.($question->invert_trigger_logic ? '(NOT) ' : '').implode(',', $trigger_ans[$tq]);
                                        
                                    } else {
                                        $triggers[] = VoxQuestion::find($tq)->question ? VoxQuestion::find($value)->question : '';
                                    }                                
                                }

                                $trg = implode('; ', $triggers);
                                
                            } else {
                                $q_titles = [];
                                foreach ($trigger_qs as $key => $value) {
                                    $q_titles = VoxQuestion::find($value) ? VoxQuestion::find($value)->question : '';
                                }
                                if($q_titles) {

                                    $trg = implode('; ', $q_titles);
                                } else {
                                    $trg = '';
                                }
                            }

                            $trg_logic = $question->trigger_type == 'or' ? 'ANY' : 'ALL';

                            $cols3[] = 'Triggers: (trigger logic '.$trg_logic.') '.$trg;
                        } else {
                            $cols3[] = '';
                        }

                    }

                } else {
                    $cols3[] = '';
                }
            }

            $rows = [
                $cols,
                $cols2,
                $cols3
            ];


            $users = DcnReward::where('reference_id',$vox->id )->where('platform', 'vox')->where('type', 'survey')->with('user');
            if( request('date-from') ) {
                $users->where('created_at', '>=', new Carbon(request('date-from')));
            }
            if( request('date-to') ) {
                $users->where('created_at', '<=', new Carbon(request('date-to')));
            }
            if( request('country_id') ) {
                $country_id = request('country_id');
                $users->whereHas('user', function ($query) use ($country_id) {
                    $query->whereIn('country_id', $country_id);
                });
            }

            $users = $users->get();

            foreach ($users as $user) {
                if(!$user->user) {
                    continue;
                }
                $row = [
                    $user->user->id,
                    $user->created_at->format('d.m.Y'),
                    $user->user->country ? $user->user->country->name : '',
                    $user->user->birthyear ? ( date('Y') - $user->user->birthyear ) : '',
                    $user->user->gender ? ($user->user->gender=='m' ? 'Male' : 'Female') : '',
                ];

                if(!empty(Request::input('demographics'))) {
                    foreach(Request::input('demographics') as $dem) {
                        $row[] = $user->user->$dem ? config('vox.details_fields.'.$dem.'.values')[$user->user->$dem] : '';
                    }
                }

                $answers = VoxAnswer::whereNull('is_admin')
                ->where('user_id', $user->user->id)
                ->where('vox_id', $vox->id)
                ->get();

                foreach ($vox->questions as $question) {
                    $qid = $question->id;
                    $qanswers = $answers->filter( function($item) use ($qid) {
                        return $qid == $item->question_id;
                    } );

                    if( $question->type == 'single_choice' ) {
                        $answerwords = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true);
                        $row[] = $qanswers->last() && $qanswers->last()->answer && isset( $answerwords[ ($qanswers->last()->answer)-1 ] ) ? $answerwords[ ($qanswers->last()->answer)-1 ] : '';
                    } else if( $question->type == 'scale' ) {
                        $list = json_decode($question->answers, true);
                        $i=1;
                        $answerwords = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true);
                        foreach ($list as $l) {
                            $thisanswer = $qanswers->filter( function($item) use ($i) {
                                return $i == $item->answer;
                            } );

                            $row[] = $thisanswer->count() && $thisanswer->first()->scale && isset( $answerwords[ ($thisanswer->first()->scale)-1 ] ) ? $answerwords[ ($thisanswer->first()->scale)-1 ] : '';
                            $i++;
                        }

                    } else if( $question->type == 'multiple_choice' ) {
                        $list = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true);
                        $i=1;
                        foreach ($list as $l) {
                            $thisanswer = $qanswers->filter( function($item) use ($i) {
                                return $i == $item->answer;
                            } );
                            $row[] = $thisanswer->count() ? '1' : '';
                            $i++;
                        }
                    }
                }

                $rows[] = $row;
            }
            
            $fname = $vox->title;

            $export = new Export($rows);
            return Excel::download($export, $fname.'.xlsx');
        }

        return $this->showView('voxes-export-survey-data', array(
            'voxes' => Vox::orderBy('sort_order', 'ASC')->get()
        ));
    }

    public function duplicate_question() {
        $qObj = VoxQuestion::find($this->request->input('d-question'));
        $q = $qObj->toArray();

        foreach ($this->langs as $key => $value) {
            $translation = $qObj->translateOrNew($key);
            $q['question-'.$key] = $translation->question;
            $q['answers-'.$key] = json_decode(stripcslashes($translation->answers));
        }
        $q['question_scale'] = $qObj->vox_scale_id;

        $item = Vox::find($this->request->input('duplicate-question-vox'));

        if(!empty($item)) {
            if ($this->request->input('current-vox') == $this->request->input('duplicate-question-vox')) {
                VoxQuestion::where('vox_id', $item->id)->where('order', '>', $q['order'])->update([
                    'order' => DB::raw('`order`+1')
                ]);
            }

            $question = new VoxQuestion;
            $question->vox_id = $item->id;
            $this->saveOrUpdateQuestion($question, $q, true);
            if ($this->request->input('current-vox') == $this->request->input('duplicate-question-vox')) {
                $question->order++;
            } else {
                $question->order = 1000;
            }
            $question->save();
            $item->checkComplex();
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }


    public function getTitle() {

        $title = trim(Request::input('title'));

        $test_surveys_ids = [48,80];

        $voxes = Vox::with('translations')->whereNotIn('id', $test_surveys_ids)->whereHas('translations', function ($query) use ($title) {
            $query->where('title', 'LIKE', '%'.$title.'%')->where('locale', 'LIKE', 'en');
        })->get();

        $list = [];

        if($voxes->isNotEmpty()) {
            foreach ($voxes as $vox) {

                $list[$vox->id] = [
                    'name' => $vox->title,
                    'link' => url('cms/vox/edit/'.$vox->id),
                    'questions' => [], 
                ];
            }
        }

        $questions = VoxQuestion::has('vox')->whereNotIn('vox_id', $test_surveys_ids)->whereHas('translations', function ($query) use ($title) {
            $query->where('question', 'LIKE', '%'.$title.'%')->where('locale', 'LIKE', 'en');
        })->get();

        if($questions->isNotEmpty()) {

            foreach ($questions as $question) {

                if(!isset($list[$question->vox->id])) {
                    $list[$question->vox->id] = [
                        'name' => $question->vox->title,
                        'link' => url('cms/vox/edit/'.$question->vox->id),
                        'questions' => [], 
                    ];
                }

                $list[$question->vox->id]['questions'][] = [
                    'name' => $question->question,
                    'link' => url('cms/vox/edit/'.$question->vox->id.'/question/'.$question->id),
                ];
            }
        }

        return Response::json($list);
    }

    public function massdelete() {

        if( Request::input('ids') ) {

            $delqs = VoxQuestion::whereIn('id', Request::input('ids'))->get();

            foreach ($delqs as $dq) {
                $dq->delete();
            }
        }

        $this->request->session()->flash('success-message', 'All selected questions are deleted' );
        return redirect(url()->previous());
    }

}