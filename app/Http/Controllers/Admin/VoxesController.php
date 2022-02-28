<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MultipleLangSheetExport;
use App\Exports\MultipleStatSheetExport;

use App\Models\VoxCronjobLang;
use App\Models\VoxToCategory;
use App\Models\VoxAnswerOld;
use App\Models\VoxCategory;
use App\Models\VoxQuestion;
use App\Models\UserDevice;
use App\Models\VoxRelated;
use App\Models\VoxHistory;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\VoxError;
use App\Models\Country;
use App\Models\Admin;
use App\Models\User;
use App\Models\Vox;

use App\Helpers\AdminHelper;
use App\Helpers\VoxHelper;
use App\Exports\Export;
use App\Imports\Import;
use Carbon\Carbon;

use Response;
use Request;
use Image;
use Route;
use Auth;
use DB;

class VoxesController extends AdminController {

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
            'number' => 'Number',
            'rank' => 'Rank',
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

        $this->scales_arr = VoxScale::orderBy('title')
        ->get()
        ->pluck('title', 'id')
        ->toArray();
    }

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        if(Request::isMethod('post')) {
            if(!empty(request('ids'))) {
                foreach(request('ids') as $vox_id) {
                    foreach(request('languages') as $lang) {
                        $vox_cronjob_lang = new VoxCronjobLang;
                        $vox_cronjob_lang->vox_id = $vox_id;
                        $vox_cronjob_lang->lang_code = $lang;
                        $vox_cronjob_lang->save();
                    }
                }

                $this->request->session()->flash('success-message', 'The chosen voxes are sended for translations' );
                return redirect('cms/vox/list');
            }

            $this->request->session()->flash('error-message', 'Please, choose surveys' );
            return redirect('cms/vox/list');
        }

        $table_fields = [
            'selector'          => array('format' => 'selector'),
            // 'sort_order'        => array('label' => 'Sort'),
            'id'                => array(),
            'title'             => array(),
            'category'          => array('template' => 'admin.parts.table-voxes-category', 'label' => 'Cat'),
            'count'             => array('template' => 'admin.parts.table-voxes-count'),
            'reward'            => array('template' => 'admin.parts.table-voxs-reward'),
            'duration'          => array('template' => 'admin.parts.table-voxs-duration'),
            'respondents'       => array('template' => 'admin.parts.table-voxs-respondents'),
            'featured'          => array('template' => 'admin.parts.table-voxes-featured', 'label' => 'Featured'),
            'type'              => array('template' => 'admin.parts.table-voxes-type'),
            'stats'             => array('template' => 'admin.parts.table-voxes-stats'),
            'stats_featured'    => array('template' => 'admin.parts.table-voxes-stats-featured'),
            'langs'             => array('template' => 'admin.parts.table-voxes-langs', 'label' => 'Langs'),
            'date'              => array('template' => 'admin.parts.table-voxes-date'),
            'launched_date'     => array('template' => 'admin.parts.table-voxes-launched-date'),
            // 'updated_date'      => array('template' => 'admin.parts.table-voxes-updated-date'),
        ];

        if(Auth::guard('admin')->user()->role != 'support') {
            $table_fields['update'] = array('template' => 'admin.parts.table-voxes-edit');
            $table_fields['delete'] = array('template' => 'admin.parts.table-voxes-delete');
        } else {
            $table_fields['update'] = array('template' => 'admin.parts.table-voxes-edit', 'label' => 'View');
        }

        $voxes_with_launched = Vox::with(['translations', 'categories.category', 'categories.category.translations', 'questions'])
        ->whereNotNull('launched_at')
        ->orderBy('launched_at', 'desc')
        ->get();

        $voxes_without_launched = Vox::with(['translations', 'categories.category', 'categories.category.translations', 'questions'])
        ->whereNull('launched_at')
        ->orderBy('id', 'desc')
        ->whereNotIn('id', [48,80])
        ->get();

        $voxes = collect();
		$voxes = $voxes_without_launched->concat($voxes_with_launched);

    	return $this->showView('voxes', array(
            'voxes' => $voxes,
            'active_voxes_count' => Vox::where('type', '!=', 'hidden')->count(),
            'hidden_voxes_count' => Vox::where('type', 'hidden')->count(),
            'are_all_results_shown' => session('vox-show-all-results') ? true : false,
            'table_fields' => $table_fields,
            'vox_errors' => VoxError::where('is_read', 0)->first(),
        ));
    }

    public function reorderVoxes() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        return Response::json([
            'success' => true
        ]);
    }

    public function add() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $newvox = new Vox;
            $this->saveOrUpdate($newvox);

            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$newvox->id);
        }

        $countries = Country::with('translations')->get();

        return $this->showView('voxes-form', array(
            'types' => $this->types,
            'scales' => $this->scales_arr,
            'category_list' => VoxCategory::get(),
            'question_types' => $this->question_types,
            'stat_types' => $this->stat_types,
            'stat_top_answers' => $this->stat_top_answers,
            'countries' => $countries,
            'countriesArray' => $countries->pluck('name', 'id')->toArray(),
            'all_voxes' => Vox::orderBy('launched_at', 'desc')->get(),
        ));
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Vox::find($id)->type == 'normal' && Auth::guard('admin')->user()->role=='voxer') {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/'.$this->current_page);
        }
        
        if(!in_array($id, [11, 34])) {
            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $id;
            $vox_history->info = 'Vox deleted';
            $vox_history->save();

            Vox::destroy( $id );

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        } else {
            $this->request->session()->flash('error-message', 'You can\'t delete this vox!' );
        }

        return redirect('cms/'.$this->current_page);
    }

    public function delpic( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Vox::find($id);

        if($item->type == 'normal' && Auth::guard('admin')->user()->role=='voxer') {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();

            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $id;
            $vox_history->info = 'Vox Image Deleted';
            $vox_history->save();
        }

        $this->request->session()->flash('success-message', 'Photo deleted!' );
        return redirect('cms/'.$this->current_page.'/edit/'.$id);
    }

    public function edit_field( $id, $field, $value ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Vox::find($id);

        $message_error = false;

        if(!empty($item)) {
            if($field=='featured') {

                if ($value=='1' && $item->featured != 1 && $item->type == 'normal' && Request::getHost() != 'urgent.dentavox.dentacoin.com' && Request::getHost() != 'urgent.reviews.dentacoin.com') {
                    UserDevice::sendPush('Now:', 'Double rewards for "'.$item->title.'" survey!', [
                        'page' => '/paid-dental-surveys/'.$item->slug,
                    ]);
                }

                if($value != $item->featured) {
                    $history_info = 'OLD Featured: '.$item->featured.'<br/>';
                    $history_info.= 'NEW Featured: '.$value.'<br/>';

                    $vox_history = new VoxHistory;
                    $vox_history->admin_id = $this->user->id;
                    $vox_history->vox_id = $id;
                    $vox_history->info = $history_info;
                    $vox_history->save();
                }

                $item->$field = $value=='0' ? 0 : 1;                
            }

            if($field=='type') {
                if($value != $item->type) {
                    $history_info = 'OLD Type: '.$item->type.'<br/>';
                    $history_info.= 'NEW Type: '.$value.'<br/>';

                    $vox_history = new VoxHistory;
                    $vox_history->admin_id = $this->user->id;
                    $vox_history->vox_id = $id;
                    $vox_history->info = $history_info;
                    $vox_history->save();
                }

                $item->$field = $value=='0' ? 'hidden' : 'normal';
                $item->last_count_at = null;

                if ($value=='1' && $item->type == 'hidden' && Request::getHost() != 'urgent.dentavox.dentacoin.com' && Request::getHost() != 'urgent.reviews.dentacoin.com') {
                    $item->activeVox();
                }
            }

            if($field=='has_stats') {
                if($item->stats_questions->isEmpty()) {
                    $message_error = 'Missing stats questions';
                } else {
                    if($value != $item->has_stats) {
                        $history_info = 'OLD Has stats: '.$item->has_stats.'<br/>';
                        $history_info.= 'NEW Has stats: '.$value.'<br/>';

                        $vox_history = new VoxHistory;
                        $vox_history->admin_id = $this->user->id;
                        $vox_history->vox_id = $id;
                        $vox_history->info = $history_info;
                        $vox_history->save();
                    }
                    $item->$field = $value=='0' ? 0 : 1;
                }
            }

            if($field=='stats_featured') {
                if($value != $item->stats_featured) {
                    $history_info = 'OLD Stats featured: '.$item->stats_featured.'<br/>';
                    $history_info.= 'NEW Stats featured: '.$value.'<br/>';

                    $vox_history = new VoxHistory;
                    $vox_history->admin_id = $this->user->id;
                    $vox_history->vox_id = $id;
                    $vox_history->info = $history_info;
                    $vox_history->save();
                }
                $item->$field = $value=='0' ? 0 : 1;
            }

            $item->save();
        }

        return Response::json([
            'message' => $message_error
        ]);
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');
        }

        if(Request::isMethod('post') && Auth::guard('admin')->user()->role == 'support') {
            return redirect('cms/home');
        }

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
                                    $triggers[$question->id] .= $q->order.'. '.$q->question.': '.$answ.'<br/>';
                                } else {
                                    $triggers[$question->id] .= $q->order.'. '.$q->question.'<br/>';
                                }                            
                            }
                        }                        
                    }
                }
            }

            if(Request::isMethod('post')) {

                $this->saveOrUpdate($item);

                $slug = $this->request->input('slug-en');
                $vox_with_same_slug = Vox::where('id', '!=', $item->id)
                ->whereHas('translations', function ($queryy) use ($slug) {
                    $queryy->where('slug', 'LIKE', $slug);
                })->first();

                if($vox_with_same_slug) {
                    Request::session()->flash('error-message', 'Slug duplicated!!!!!');
                } else if($item->has_stats && $item->stats_questions->isEmpty()) {
                    Request::session()->flash('error-message', 'Missing stats questions');
                } else {
                    Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                }
            
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
            $q_trigger_multiple_answ = [];

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
                    if(is_numeric($q_trigger)) {
                        $questionTrigger = VoxQuestion::with('translations')->find($q_trigger);
                    } else {
                        $questionTrigger = $q_trigger;
                    }

                    $q_trigger_obj[] = $questionTrigger;
                    if(is_numeric($q_trigger) && !empty($questionTrigger) && $questionTrigger->type == 'multiple_choice') {
                        $q_trigger_multiple_answ[$questionTrigger->id] = '';
                    }
                }
            }

            if(!empty($q_trigger_multiple_answ)) {
                foreach ($q_trigger_multiple_answ as $key => $value) {
                    $answe = [];
                    foreach (json_decode(VoxQuestion::find($key)->answers, true) as $k => $ans) {
                        if(mb_strpos($ans, '!')===false) {
                            $answe[] = $k + 1;
                        }
                    }
                    $q_trigger_multiple_answ[$key] = implode(',', $answe);
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
                    $error_arr[] = 'Missing stats description';
                    $error = true;
                }

                if($item->stats_questions->isEmpty()) {
                    $error_arr[] = 'Missing stats questions';
                    $error = true;
                } else {
                    foreach ($item->stats_questions as $stat) {
                        if(empty($stat->stats_title_question) && empty($stat->stats_title) && empty($stat->stats_title_question)) {
                            $error_arr[] = 'Missing stats <a href="https://dentavox.dentacoin.com/cms/vox/edit/'.$item->id.'/question/'.$stat->id.'/">question</a> title';
                            $error = true;
                        }
                        if(empty($stat->stats_fields) && $stat->used_for_stats != 'dependency') {
                            $error_arr[] = 'Missing stats <a href="https://dentavox.dentacoin.com/cms/vox/edit/'.$item->id.'/question/'.$stat->id.'/">question</a> demographics';
                            $error = true;
                        }
                    }
                }
            }

            $questions_order_bug = false;
            $questions_order_bug_message = '';

            //there are duplicated questions order

            if($item->questions->isNotEmpty()) {

                $count_qs = $item->questions->count();

                for ($i=1; $i <= $count_qs ; $i++) { 
                    $duplicatedQuestion = VoxQuestion::with('translations')->where('vox_id', $item->id)->where('order', $i)->count();
                    if(!empty($duplicatedQuestion)) {
                        if($duplicatedQuestion > 1) {
                            $questions_order_bug = true;
                            $questions_order_bug_message .= 'Duplicated order number - '.$i.'<br/>';  //diplicated order
                        }
                    } else {
                        $questions_order_bug = true;
                        $questions_order_bug_message .= 'Missing order number - '.$i.'<br/>';  //missing order
                    }
                }
            }

            $countries = Country::with('translations')->get();

            return $this->showView('voxes-form', array(
                'types' => $this->types,
                'scales' => $this->scales_arr,
                'question_types' => $this->question_types,
                'stat_types' => $this->stat_types,
                'stat_top_answers' => $this->stat_top_answers,
                'scales_arr' => $scales,
                'item' => $item,
                'category_list' => VoxCategory::with('translations')->get(),
                'triggers' => $triggers,
                'linked_triggers' => $linked_triggers,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers,
                'all_voxes' => Vox::with('translations')->orderBy('launched_at', 'desc')->get(),
                'countries' => $countries,
                'countriesArray' => $countries->pluck('name', 'id')->toArray(),
                'q_trigger_obj' => $q_trigger_obj,
                'q_trigger_multiple_answ' => $q_trigger_multiple_answ,
                'error_arr' => $error_arr,
                'error' => $error,
                'questions_order_bug' => $questions_order_bug,
                'questions_order_bug_message' => $questions_order_bug_message,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function export( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Vox::find($id);

        if(!empty($item)) {

            ini_set('max_execution_time', 0);
            set_time_limit(0);
            ini_set('memory_limit','1024M');

            $flist = [];

            foreach(config('langs')['admin'] as $code => $lang_info) {
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        return 'This doesn\'t work. Tell the developer about it';

        $item = Vox::find($id);

        if(!empty($item)) {

            $that = $this;

            $newName = '/tmp/'.str_replace(' ', '-', Input::file('table')->getClientOriginalName());
            copy( Input::file('table')->path(), $newName );

            $results = Excel::toArray(new Import, $newName );

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Vox::find($id);

        if(!empty($item) && Input::file('table')) {

            session()->pull('brackets');

            global $i;
            $i = $item->questions->last() ? intval($item->questions->last()->order)+1 : 1;

            $that = $this;

            $newName = '/tmp/'.str_replace([' ', ','], ['-', ''], Input::file('table')->getClientOriginalName());
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

                            $prev_q_answ = null;
                            if (mb_strpos($q, 'prev_q') !== false) {
                                $prev_q_answ = intval(explode(':', explode('|', $q)[0])[1]);
                                $q = explode('|', $q)[1];
                            }

                            $qdata = [
                                'order' => $i,
                                'type' => 'single_choice',
                                'is_control' => null,
                                'question_scale' => null,
                                'question_trigger' => null,
                                'question-en' => $q,
                                'answers-en' => $a,
                            ];

                            if($prev_q_answ) {
                                $qdata['prev_q_order'] = $prev_q_answ; 
                            }

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
                    Request::session()->flash('error-message', 'Missing or more than necessary answer/s tooltip brackets: '.implode(' ;     ', session('brackets')['a_br'] ));
                }
            }
            
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function add_question( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Vox::find($id);

        if(!empty($item)) {

            $question = new VoxQuestion;
            $question->vox_id = $item->id;
            $this->saveOrUpdateQuestion($question);
            $item->checkComplex();

            if($item->type == 'normal') {
                foreach (config('langs-to-translate') as $lang_code => $value) {
                    if($lang_code != 'en') {
                        VoxHelper::translateQuestionWithAnswers($lang_code, $question);
                    }
                }
            }

            if(request('used_for_stats')=='standard' && !request('stats_fields')) {
                $question->delete();
                return Response::json([
                    'success' => false,
                    'message' => 'Please, select the demographic details which should be used for the statistics.',
                ]);
            }
            
            if ($question->type == 'scale' && request('used_for_stats')=='standard' && !request('stats_fields') && !request('stats_scale_answers')) {
                $question->delete();
                return Response::json([
                    'success' => false,
                    'message' => 'Please, select the demographic details and scale answers which should be used for the statistics.',
                ]);
            }
            
            if ($question->type == 'scale' && !request('question_scale')) {
                $question->delete();
                return Response::json([
                    'success' => false,
                    'message' => 'Please, pick a scale.',
                ]);
            }
            
            if(!empty(request('used_for_stats')) && empty(request('stats_title_question')) && empty(request('stats_title-en'))) {
                $question->delete();
                return Response::json([
                    'success' => false,
                    'message' => 'Stats title required.',
                ]);
            }
            
            if($question->type == 'number' && empty($question->number_limit)) {
                $question->delete();
                return Response::json([
                    'success' => false,
                    'message' => 'Number limit requited.',
                ]);
            }

            if(!empty($question->prev_q_id_answers) && (VoxQuestion::find($question->prev_q_id_answers)->type != 'multiple_choice' || ($question->type!='single_choice' || $question->type!='multiple_choice'))) {
                $question->delete();
                
                return Response::json([
                    'success' => false,
                    'message' => 'The current question must be single choice or multiple choice and the previous question must be multiple choice type.',
                ]);
            }

            $trigger = '';
            $trigger_same_as_prev = false;

            if($question->question_trigger) {

                foreach (explode(';', $question->question_trigger) as $v) {
                    $question_id = explode(':',$v)[0];

                    if($question_id==-1) {
                        $trigger .= 'Same as previous<br/>';
                        $trigger_same_as_prev = true;
                    } else if(!is_numeric($question_id)) {
                        $trigger .= ($question_id == 'age_groups' ? 'Age groups' : ($question_id == 'gender' ? 'Gender' : config('vox.details_fields.'.$question_id)['label'])).' : '.explode(':',$v)[1];
                    } else {
                        $q = VoxQuestion::find($question_id);

                        if(!empty($q)) {
                            if (!empty(explode(':',$v)[1])) {
                                $answ = explode(':',$v)[1];
                                $trigger .= $q->order.'. '.$q->question.': '.$answ.'<br/>';
                            } else {
                                $trigger .= $q->order.'. '.$q->question.'<br/>';
                            }                            
                        }
                    }
                }
            }
            
            return Response::json([
                'success' => true,
                'question' => $question,
                'realted_question' => $question->related ? $question->related->question : '',
                'trigger' => $trigger,
                'trigger_same_as_prev' => $trigger_same_as_prev,
                'question_type' => $this->question_types[$question->type],
            ]);
        }
    }

    public function edit_question( $id, $question_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox_id==$id) {

            $trigger_question_id = null;
            $trigger_valid_answers = null;

            $triggers_ids = [];
            $trigger_type = null;

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
                    }
                }
            }

            if(empty( $trigger_question_id )) {
                $prev_question = VoxQuestion::where('vox_id', $id)
                ->where('order', '<', intVal($question->order) )
                ->orderBy('order', 'DESC')
                ->first();

                $trigger_question_id = $prev_question ? $prev_question->id : '';
                $trigger_valid_answers = null;
            }

            if(Request::isMethod('post')) {

                $this->saveOrUpdateQuestion($question);
                $question->vox->checkComplex();

                if(request('used_for_stats')=='standard' && !request('stats_fields')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Please, select the demographic details which should be used for the statistics.',
                    ]);
                } else if ($question->type == 'scale' && request('used_for_stats')=='standard' && !request('stats_fields') && !request('stats_scale_answers')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Please, select the demographic details and scale answers which should be used for the statistics.',
                    ]);
                } else if ($question->type == 'scale' && !request('question_scale')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Please, pick a scale.',
                    ]);
                } else if(!empty(request('used_for_stats')) && empty(request('stats_title_question')) && empty(request('stats_title-en'))) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Stats title required.',
                    ]);
                } else if($question->type == 'number' && empty($question->number_limit)) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Number limit requited.',
                    ]);
                } else {

                    $trigger = '';
                    $trigger_same_as_prev = false;

                    if($question->question_trigger) {

                        foreach (explode(';', $question->question_trigger) as $v) {
                            $question_id = explode(':',$v)[0];

                            if($question_id==-1) {
                                $trigger .= 'Same as previous<br/>';
                                $trigger_same_as_prev = true;
                            } else if(!is_numeric($question_id)) {
                                $trigger .= ($question_id == 'age_groups' ? 'Age groups' : ($question_id == 'gender' ? 'Gender' : config('vox.details_fields.'.$question_id)['label'])).' : '.explode(':',$v)[1];
                            } else {
                                $q = VoxQuestion::find($question_id);

                                if(!empty($q)) {
                                    if (!empty(explode(':',$v)[1])) {
                                        $answ = explode(':',$v)[1];
                                        $trigger .= $q->order.'. '.$q->question.': '.$answ.'<br/>';
                                    } else {
                                        $trigger .= $q->order.'. '.$q->question.'<br/>';
                                    }                            
                                }
                            }
                        }
                    }
                    
                    return Response::json([
                        'success' => true,
                        'question' => $question,
                        'realted_question' => $question->related ? $question->related->question : '',
                        'trigger' => $trigger,
                        'trigger_same_as_prev' => $trigger_same_as_prev,
                        'question_type' => $this->question_types[$question->type],
                    ]);
                }
            }

            $question_answers_count = DB::table('vox_answers')
            ->join('users', 'users.id', '=', 'vox_answers.user_id')
            ->whereNull('users.deleted_at')
            ->whereNull('vox_answers.is_admin')
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
                    $error_arr[] = 'Missing stats question title';
                    $error = true;
                }
                if(empty($question->stats_fields) && $question->used_for_stats != 'dependency') {
                    $error_arr[] = 'Missing stats question demographics';
                    $error = true;
                }
            }

            $excluded_answers = [];
            if(!empty($question->excluded_answers)) {
                foreach($question->excluded_answers as $excluded_answers_array) {
                    foreach($excluded_answers_array as $excluded_answ) {
                        $excluded_answers[] = $excluded_answ;
                    }
                }
            }

            return $this->showView('voxes-form-question', array(
                'error' => $error,
                'error_arr' => $error_arr,
                'question' => $question,
                'question_answers_count' => $question_answers_count,
                'scales' => $this->scales_arr,
                'item' => $question->vox,
                'question_types' => $this->question_types,
                'stat_top_answers' => $this->stat_top_answers,
                'stat_types' => $this->stat_types,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers,
                'triggers_ids' => $triggers_ids,
                'trigger_type' => $trigger_type,
                'excluded_answers' => $excluded_answers,
            ));

        } else {
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }
    }

    public function delete_question( $question_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($question_id);

        if($question->vox->type == 'normal' && Auth::guard('admin')->user()->role=='voxer') {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }

        if(!empty($question)) {
            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $question->vox_id;
            $vox_history->question_id = $question_id;
            $vox_history->info = 'Question Deleted with order '.$question->order;
            $vox_history->save();

            $question->delete();
            $question->vox->checkComplex();

            return Response::json([
                'success' => true
            ]);

        } else {
            return Response::json([
                'success' => false
            ]);
        }
    }

    public function order_question( $id, $question_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox_id==$id) {
            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $question->vox_id;
            $vox_history->question_id = $question_id;
            $vox_history->info = 'Old Question Order: '.$question->order.'<br/> New Question Order: '.Request::input('val');
            $vox_history->save();

            $question->order = Request::input('val');
            $question->save();

            return Response::json([
                'success' => true
            ]);
        } else {
            return Response::json([
                'success' => false
            ]);
        }
    }

    public function reorder($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $list = Request::input('list');
        $i=1;
        foreach ($list as $qid) {
            $question = VoxQuestion::find($qid);
            if($question->vox_id==$id) {

                $vox_history = new VoxHistory;
                $vox_history->admin_id = $this->user->id;
                $vox_history->vox_id = $question->vox_id;
                $vox_history->question_id = $question->id;
                $vox_history->info = 'Old Question Order: '.$question->order.'<br/> New Question Order: '.$i;
                $vox_history->save();

                $question->order = $i;
                $question->save();
                $i++;
            }
        }

        return Response::json([
            'success' => true
        ]);
    }

    public function change_question_text( $id, $question_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($question_id);
        $lang = request('code');

        if(!empty($question) && $question->vox_id==$id) {
            $translation = $question->translateOrNew($lang);

            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $question->vox_id;
            $vox_history->question_id = $question->id;
            $vox_history->info = 'Old Question Title '.$lang.': '.$translation->question.'<br/> New Question Title: '.Request::input('val');
            $vox_history->save();

            $translation->question = Request::input('val');
            $translation->save();

            if($question->vox->type == 'normal') {
                foreach (config('langs-to-translate') as $lang_code => $value) {
                    if($lang_code != 'en') {
                        VoxHelper::translateQuestionWithAnswers($lang_code, $question);
                    }
                }
            }
            return Response::json([
                'success' => true
            ]);
        } else {
            return Response::json([
                'success' => false
            ]);
        }
    }

    private function saveOrUpdate($item) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit', '4095M');

        if ($this->request->input('type') == 'normal' && $item->type == 'hidden' && Request::getHost() != 'urgent.dentavox.dentacoin.com' && Request::getHost() != 'urgent.reviews.dentacoin.com') {
            $item->activeVox();
        }

        if ($this->request->input('featured') == 1 && $item->featured != 1 && $item->type == 'normal' && Request::getHost() != 'urgent.dentavox.dentacoin.com' && Request::getHost() != 'urgent.reviews.dentacoin.com') {
            UserDevice::sendPush('Now:', 'Double rewards for "'.$item->title.'" survey!', [
                'page' => '/paid-dental-surveys/'.$item->slug,
            ]);
        }

        if(!empty($item) && !$item->has_stats && !empty($this->request->input('has_stats'))) {
            $dependency_questions = VoxQuestion::where('vox_id', $item->id)
            ->where('used_for_stats', 'dependency')
            ->whereNotNull('stats_relation_id')
            ->whereNull('dependency_caching')
            ->get();

            if($dependency_questions->isNotEmpty()) {
                foreach ($dependency_questions as $dq) {
                    $dq->generateDependencyCaching();
                }
            }
        }

        $history_info = '';

        if($item->type == 'normal' && $this->request->input('type') == 'hidden' && !$this->request->input('hide-survey')) {
        } else {
            if($this->request->input('type') != $item->type) {
                $history_info.= 'OLD Type: '.$item->type.'<br/>';
                $history_info.= 'NEW Type: '.$this->request->input('type').'<br/>';
            }
            $item->type = $this->request->input('type');
        }

        if($this->request->input('scheduled_at') != $item->scheduled_at) {
            $history_info.= 'OLD Scheduled at: '.$item->scheduled_at.'<br/>';
            $history_info.= 'NEW Scheduled at: '.$this->request->input('scheduled_at').'<br/>';
        }
        if($this->request->input('featured') != $item->featured) {
            $history_info.= 'OLD Featured: '.$item->featured.'<br/>';
            $history_info.= 'NEW Featured: '.$this->request->input('featured').'<br/>';
        }
        if($this->request->input('stats_featured') != $item->stats_featured) {
            $history_info.= 'OLD Stats featured: '.$item->stats_featured.'<br/>';
            $history_info.= 'NEW Stats featured: '.$this->request->input('stats_featured').'<br/>';
        }
        if($this->request->input('has_stats') != $item->has_stats) {
            $history_info.= 'OLD Has stats: '.$item->has_stats.'<br/>';
            $history_info.= 'NEW Has stats: '.$this->request->input('has_stats').'<br/>';
        }
        if($this->request->input('gender') != $item->gender) {
            $history_info.= 'OLD Gender: '.implode(',',$item->gender).'<br/>';
            $history_info.= 'NEW Gender: '.implode(',',$this->request->input('gender')).'<br/>';
        }
        if($this->request->input('marital_status') != $item->marital_status) {
            $history_info.= 'OLD Marital status: '.implode(',',$item->marital_status).'<br/>';
            $history_info.= 'NEW Marital status: '.implode(',',$this->request->input('marital_status')).'<br/>';
        }
        if($this->request->input('children') != $item->children) {
            $history_info.= 'OLD Children: '.implode(',',$item->children).'<br/>';
            $history_info.= 'NEW Children: '.implode(',',$this->request->input('children')).'<br/>';
        }
        if($this->request->input('household_children') != $item->household_children) {
            $history_info.= 'OLD Household children: '.implode(',',$item->household_children).'<br/>';
            $history_info.= 'NEW Household children: '.implode(',',$this->request->input('household_children')).'<br/>';
        }
        if($this->request->input('education') != $item->education) {
            $history_info.= 'OLD Education: '.implode(',',$item->education).'<br/>';
            $history_info.= 'NEW Education: '.implode(',',$this->request->input('education')).'<br/>';
        }
        if($this->request->input('employment') != $item->employment) {
            $history_info.= 'OLD Employment: '.implode(',',$item->employment).'<br/>';
            $history_info.= 'NEW Employment: '.implode(',',$this->request->input('employment')).'<br/>';
        }
        if($this->request->input('job') != $item->job) {
            $history_info.= 'OLD Job: '.implode(',',$item->job).'<br/>';
            $history_info.= 'NEW Job: '.implode(',',$this->request->input('job')).'<br/>';
        }
        if($this->request->input('job_title') != $item->job_title) {
            $history_info.= 'OLD Job title: '.implode(',',$item->job_title).'<br/>';
            $history_info.= 'NEW Job title: '.implode(',',$this->request->input('job_title')).'<br/>';
        }
        if($this->request->input('income') != $item->income) {
            $history_info.= 'OLD Income: '.implode(',',$item->income).'<br/>';
            $history_info.= 'NEW Income: '.implode(',',$this->request->input('income')).'<br/>';
        }
        if($this->request->input('age') != $item->age) {
            $history_info.= 'OLD Age: '.implode(',',$item->age).'<br/>';
            $history_info.= 'NEW Age: '.implode(',',$this->request->input('age')).'<br/>';
        }
        if($this->request->input('countries_ids') != $item->countries_ids) {
            $history_info.= 'OLD Countries IDs: '.implode(',',$item->countries_ids).'<br/>';
            $history_info.= 'NEW Countries IDs: '.implode(',',$this->request->input('countries_ids')).'<br/>';
        }
        if($this->request->input('exclude_countries_ids') != $item->exclude_countries_ids) {
            $history_info.= 'OLD Exclude Countries IDs: '.implode(',',$item->exclude_countries_ids).'<br/>';
            $history_info.= 'NEW Exclude Countries IDs: '.implode(',',$this->request->input('exclude_countries_ids')).'<br/>';
        }
        if($this->request->input('country_percentage') != $item->country_percentage) {
            $history_info.= 'OLD Country percentage: '.$item->country_percentage.'<br/>';
            $history_info.= 'NEW Country percentage: '.$this->request->input('country_percentage').'<br/>';
        }
        if($this->request->input('dentists_patients') != $item->dentists_patients) {
            $history_info.= 'OLD Dentists patients: '.implode(',',$item->dentists_patients).'<br/>';
            $history_info.= 'NEW Dentists patients: '.implode(',',$this->request->input('dentists_patients')).'<br/>';
        }
        if($this->request->input('manually_calc_reward') != $item->manually_calc_reward) {
            $history_info.= 'OLD Manually calc reward: '.$item->manually_calc_reward.'<br/>';
            $history_info.= 'NEW Manually calc reward: '.$this->request->input('manually_calc_reward').'<br/>';
        }

        $item->scheduled_at = $this->request->input('scheduled_at');
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
        $item->exclude_countries_ids = $this->request->input('exclude_countries_ids');
        $item->country_percentage = $this->request->input('country_percentage');
        $item->dentists_patients = $this->request->input('dentists_patients');
        $item->manually_calc_reward = !empty($this->request->input('manually_calc_reward')) ? 1 : null;
        $item->last_count_at = null;

        if (!empty($this->request->input('count_dcn_questions'))) {
            $trigger_qs = [];
            foreach ($this->request->input('count_dcn_questions') as $k => $v) {
                $trigger_qs[$this->request->input('count_dcn_questions')[$k]] = $this->request->input('count_dcn_answers')[$k];
            }

            if($trigger_qs != $item->dcn_questions_triggers) {
                $history_info.= 'OLD Count dcn questions: '.(is_array($item->dcn_questions_triggers) ? implode(',', $item->dcn_questions_triggers) : $item->dcn_questions_triggers ).'<br/>';
                $history_info.= 'NEW Count dcn questions: '.implode(',', $trigger_qs).'<br/>';
            }

            $item->dcn_questions_triggers = $trigger_qs;
            $item->getLongestPath();
        }

        $item->save();
        
        if( !empty( Request::input('categories') )) {
            $catsDiff = array_merge(array_diff(request('categories'), $item->categories->pluck('vox_category_id')->toArray()), array_diff($item->categories->pluck('vox_category_id')->toArray(), request('categories')));

            if(!empty($catsDiff)) {
                $history_info.= 'OLD Categories: '.implode(',',$item->categories->pluck('vox_category_id')->toArray()).'<br/>';
                $history_info.= 'NEW Categories: '.implode(',',request('categories')).'<br/>';
            }
        } else {
            if($item->categories->isNotEmpty()) {
                $history_info.= 'Categories: null<br/>';
            }
        }

        VoxToCategory::where('vox_id', $item->id)->delete();
        if( !empty( Request::input('categories') )) {
            foreach(Request::input('categories') as $cat_id) {
                $vc = new VoxToCategory();
                $vc->vox_id = $item->id;
                $vc->vox_category_id = $cat_id;
                $vc->save();
            }   
        }
        
        if( !empty( Request::input('related_vox_id') )) {
            $relatedDiff = array_merge(array_diff(request('related_vox_id'), $item->related->pluck('related_vox_id')->toArray()), array_diff($item->related->pluck('related_vox_id')->toArray(), request('related_vox_id')));

            $has_diff = false;
            foreach($relatedDiff as $rd) {
                if($rd !== null) {
                    $has_diff = true;
                }
            }

            if(!empty($relatedDiff) && $has_diff) {
                $history_info.= 'OLD Relateds: '.implode(',',$item->related->pluck('related_vox_id')->toArray()).'<br/>';
                $history_info.= 'NEW Relateds: '.implode(',',request('related_vox_id')).'<br/>';
            }
        } else {
            if($item->related->isNotEmpty()) {
                $history_info.= 'Relateds: null<br/>';
            }
        }

        VoxRelated::where('vox_id', $item->id)->delete();
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

                if($this->request->input('title-'.$key) != $item->title) {
                    $history_info.= 'OLD Title '.$key.': '.$item->title.'<br/>';
                    $history_info.= 'NEW Title '.$key.': '.$this->request->input('title-'.$key).'<br/>';
                }
                if($this->request->input('slug-'.$key) != $item->slug) {
                    $history_info.= 'OLD Slug '.$key.': '.$item->slug.'<br/>';
                    $history_info.= 'NEW Slug '.$key.': '.$this->request->input('slug-'.$key).'<br/>';
                }
                if($this->request->input('description-'.$key) != $item->description) {
                    $history_info.= 'OLD Description '.$key.': '.$item->description.'<br/>';
                    $history_info.= 'NEW Description '.$key.': '.$this->request->input('description-'.$key).'<br/>';
                }
                if($this->request->input('stats_description-'.$key) != $item->stats_description) {
                    $history_info.= 'OLD Stats description '.$key.': '.$item->stats_description.'<br/>';
                    $history_info.= 'NEW Stats description '.$key.': '.$this->request->input('stats_description-'.$key).'<br/>';
                }

                $translation->vox_id = $item->id;
                $translation->slug = $this->request->input('slug-'.$key);
                $translation->title = $this->request->input('title-'.$key);
                $translation->description = $this->request->input('description-'.$key);
                $translation->stats_description = $this->request->input('stats_description-'.$key);
                $translation->save();
            }
        }
        $item->save();

        $extensions = ['png', 'jpg', 'jpeg'];
            
        if( Input::file('photo') ) {

            $extensions = ['image/jpeg', 'image/png'];

            if (!in_array(Input::file('photo')->getMimeType(), $extensions)) {

                if(!empty($history_info)) {
                    $vox_history = new VoxHistory;
                    $vox_history->admin_id = $this->user->id;
                    $vox_history->vox_id = $item->id;
                    $vox_history->info = $history_info;
                    $vox_history->save();
                }
                
                $this->request->session()->flash('error-message', 'File extension not supported' );
                return redirect('cms/vox/edit/'.$item->id);
            }
            $img = Image::make( Input::file('photo') )->orientate();
            $filename = explode('.', $_FILES['photo']['name'])[0];
            $item->addImage($img ,$filename);

            $history_info.= 'New photo<br/>';
        }
        if( Input::file('photo-social') ) {

            $extensions = ['image/jpeg', 'image/png'];

            if (!in_array(Input::file('photo-social')->getMimeType(), $extensions)) {

                if(!empty($history_info)) {
                    $vox_history = new VoxHistory;
                    $vox_history->admin_id = $this->user->id;
                    $vox_history->vox_id = $item->id;
                    $vox_history->info = $history_info;
                    $vox_history->save();
                }
                $this->request->session()->flash('error-message', 'File extension not supported' );
                return redirect('cms/vox/edit/'.$item->id);
            }
            $img = Image::make( Input::file('photo-social')->getRealPath() )->orientate();
            $filename = explode('.', $_FILES['photo-social']['name'])[0];
            $item->addSocialImage($img, $filename);

            $history_info.= 'New photo social<br/>';
        }
        if( Input::file('photo-stats') ) {

            $extensions = ['image/jpeg', 'image/png'];

            if (!in_array(Input::file('photo-stats')->getMimeType(), $extensions)) {

                if(!empty($history_info)) {
                    $vox_history = new VoxHistory;
                    $vox_history->admin_id = $this->user->id;
                    $vox_history->vox_id = $item->id;
                    $vox_history->info = $history_info;
                    $vox_history->save();
                }
                $this->request->session()->flash('error-message', 'File extension not supported' );
                return redirect('cms/vox/edit/'.$item->id);
            }
            $img = Image::make( Input::file('photo-stats') )->orientate();
            $filename = explode('.', $_FILES['photo-stats']['name'])[0];
            $item->addSocialImage($img, $filename, 'for-stats');

            $history_info.= 'New photo stats<br/>';
        }

        if(!empty($history_info)) {
            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $item->id;
            $vox_history->info = $history_info;
            $vox_history->save();
        }
    }

    private function saveOrUpdateQuestion($question, $data = null, $justCopy = false ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(empty($data)) {
            $data = $this->request->input();
        }

        $history_info = '';

        if($this->request->input('is_control_prev')) {
            if($this->request->input('is_control_prev') != $question->is_control) {
                $history_info.= 'OLD Control question: '.$question->is_control.'<br/>';
                $history_info.= 'NEW Control question: '.$this->request->input('is_control_prev').'<br/>';
            }
        } else {
            if($this->request->input('is_control') != $question->is_control) {
                $history_info.= 'OLD Control question: '.$question->is_control.'<br/>';
                $history_info.= 'NEW Control question: '.$this->request->input('is_control').'<br/>';
            }
        }

        if(!empty($data['is_control_prev'])) {
            $question->is_control = $data['is_control_prev'];
        } else {
            $question->is_control = $data['is_control'];
        }

        if($this->request->input('cross_check') != $question->cross_check) {
            $history_info.= 'OLD Cross check: '.$question->cross_check.'<br/>';
            $history_info.= 'NEW Cross check: '.$this->request->input('cross_check').'<br/>';
        }
        if($this->request->input('type') != $question->type) {
            $history_info.= 'OLD Type: '.$question->type.'<br/>';
            $history_info.= 'NEW Type: '.$this->request->input('type').'<br/>';
        }
        if($this->request->input('order') != $question->order) {
            $history_info.= 'OLD Order: '.$question->order.'<br/>';
            $history_info.= 'NEW Order: '.$this->request->input('order').'<br/>';
        }
        if($this->request->input('stats_featured') != $question->stats_featured) {
            $history_info.= 'OLD Stats featured: '.$question->stats_featured.'<br/>';
            $history_info.= 'OLD Stats featured: '.$this->request->input('stats_featured').'<br/>';
        }
        if($this->request->input('stats_top_answers') != $question->stats_top_answers) {
            $history_info.= 'OLD Stats top answers: '.$question->stats_top_answers.'<br/>';
            $history_info.= 'NEW Stats top answers: '.$this->request->input('stats_top_answers').'<br/>';
        }
        if($this->request->input('stats_fields') != $question->stats_fields) {
            $history_info.= 'OLD Stats fields answers: '.implode(',',$question->stats_fields).'<br/>';
            $history_info.= 'NEW Stats fields answers: '.($this->request->input('stats_fields') ? implode(',',$this->request->input('stats_fields')) : '').'<br/>';
        }
        if($this->request->input('stats_scale_answers') != json_decode($question->stats_scale_answers, true)) {
            $history_info.= 'OLD Stats scale answers: '.$question->stats_scale_answers.'<br/>';
            $history_info.= 'NEW Stats scale answers: '.($this->request->input('stats_scale_answers') ? implode(',',$this->request->input('stats_scale_answers')) : '').'<br/>';
        }
        if($this->request->input('question_scale') != $question->vox_scale_id) {
            $history_info.= 'OLD Vox scale id: '.$question->vox_scale_id.'<br/>';
            $history_info.= 'NEW Vox scale id: '.$this->request->input('question_scale').'<br/>';
        }
        if($this->request->input('dont_randomize_answers') != $question->dont_randomize_answers) {
            $history_info.= 'OLD Dont randomize answers: '.$question->dont_randomize_answers.'<br/>';
            $history_info.= 'NEW Dont randomize answers: '.$this->request->input('dont_randomize_answers').'<br/>';
        }
        if($this->request->input('image_in_tooltip') != $question->image_in_tooltip) {
            $history_info.= 'OLD Image in tooltip: '.$question->image_in_tooltip.'<br/>';
            $history_info.= 'NEW Image in tooltip: '.$this->request->input('image_in_tooltip').'<br/>';
        }
        if($this->request->input('image_in_question') != $question->image_in_question) {
            $history_info.= 'OLD Image in question: '.$question->image_in_question.'<br/>';
            $history_info.= 'NEW Image in question: '.$this->request->input('image_in_question').'<br/>';
        }
        if($this->request->input('prev_q_id_answers') != $question->prev_q_id_answers) {
            $history_info.= 'OLD Prev q id answers: '.$question->prev_q_id_answers.'<br/>';
            $history_info.= 'NEW Prev q id answers: '.$this->request->input('prev_q_id_answers').'<br/>';
        }
        if($this->request->input('remove_answers_with_diez') != $question->remove_answers_with_diez) {
            $history_info.= 'OLD Remove answers with diez: '.$question->remove_answers_with_diez.'<br/>';
            $history_info.= 'NEW Remove answers with diez: '.$this->request->input('remove_answers_with_diez').'<br/>';
        }
        if($this->request->input('show_answers_with_еxclamation_mark') != $question->show_answers_with_еxclamation_mark) {
            $history_info.= 'OLD Show answers with exclamation mark: '.$question->show_answers_with_еxclamation_mark.'<br/>';
            $history_info.= 'NEW Show answers with exclamation mark: '.$this->request->input('show_answers_with_еxclamation_mark').'<br/>';
        }
        if($this->request->input('trigger_type') != $question->trigger_type) {
            $history_info.= 'OLD Trigger type: '.$question->trigger_type.'<br/>';
            $history_info.= 'NEW Trigger type: '.$this->request->input('trigger_type').'<br/>';
        }
        if($this->request->input('used_for_stats') != $question->used_for_stats) {
            $history_info.= 'OLD Used for stats: '.$question->used_for_stats.'<br/>';
            $history_info.= 'NEW Used for stats: '.$this->request->input('used_for_stats').'<br/>';
        }
        if($question->used_for_stats=='dependency' && $this->request->input('stats_relation_id') != $question->stats_relation_id) {
            $history_info.= 'OLD Stats relation id: '.$question->stats_relation_id.'<br/>';
            $history_info.= 'NEW Stats relation id: '.$this->request->input('stats_relation_id').'<br/>';
        }
        if($question->used_for_stats=='dependency' && $this->request->input('stats_answer_id') != $question->stats_answer_id) {
            $history_info.= 'OLD Stats answer id: '.$question->stats_answer_id.'<br/>';
            $history_info.= 'NEW Stats answer id: '.$this->request->input('stats_answer_id').'<br/>';
        }
        if($this->request->input('stats_title_question') != $question->stats_title_question) {
            $history_info.= 'OLD Stats title question: '.$question->stats_title_question.'<br/>';
            $history_info.= 'NEW Stats title question: '.$this->request->input('stats_title_question').'<br/>';
        }
        if($this->request->input('order_stats_answers_with_diez_as_they_are') != $question->order_stats_answers_with_diez_as_they_are) {
            $history_info.= 'OLD Order stats answers with diez as they are: '.$question->order_stats_answers_with_diez_as_they_are.'<br/>';
            $history_info.= 'NEW Order stats answers with diez as they are: '.$this->request->input('order_stats_answers_with_diez_as_they_are').'<br/>';
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
        $question->image_in_tooltip = !empty($data['image_in_tooltip']) ? $data['image_in_tooltip'] : null;
        $question->image_in_question = !empty($data['image_in_question']) ? $data['image_in_question'] : null;
        $question->prev_q_id_answers = !empty($data['prev_q_id_answers']) ? $data['prev_q_id_answers'] : null;
        $question->remove_answers_with_diez = !empty($data['remove_answers_with_diez']) ? $data['remove_answers_with_diez'] : null;
        $question->show_answers_with_еxclamation_mark = !empty($data['show_answers_with_еxclamation_mark']) ? $data['show_answers_with_еxclamation_mark'] : null;
              
        if( !empty($data['trigger_type']) ) {
            $question->trigger_type = $data['trigger_type'];
        }

        $question->used_for_stats = !empty($data['used_for_stats']) ? $data['used_for_stats'] : null;
        $question->stats_relation_id = $question->used_for_stats=='dependency' ? $data['stats_relation_id'] : null;
        $question->stats_answer_id = $question->used_for_stats=='dependency' ? $data['stats_answer_id'] : null;
        $question->stats_title_question = !empty($data['stats_title_question']) ? $data['stats_title_question'] : null;
        $question->order_stats_answers_with_diez_as_they_are = !empty($data['order_stats_answers_with_diez_as_they_are']) ? $data['order_stats_answers_with_diez_as_they_are'] : null;

        if( $justCopy ) {

            if($this->request->input('question_trigger') != $question->question_trigger) {
                $history_info.= 'OLD Question trigger: '.$question->question_trigger.'<br/>';
                $history_info.= 'NEW Question trigger: '.$this->request->input('question_trigger').'<br/>';
            }

            $question->question_trigger = $data['question_trigger'];
        } else {
            if(!empty( $data['triggers'] )) {
                $help_array = [];
                foreach($data['triggers'] as $i => $trg) {
                    if(!empty($trg)) {
                        $q_trg = VoxQuestion::find($trg);
                        $help_array[] = $trg.( !empty( $data['answers-number'][$i] ) || (!empty( $data['answers-number'][$i] ) && $data['answers-number'][$i] == '0' && $q_trg->type == 'number') ? ':'.$data['answers-number'][$i] : '' );
                    }
                }

                if($question->question_trigger != implode(';', $help_array)) {
                    $q_vox = Vox::find($question->vox_id);
                    $q_vox->manually_calc_reward = null;
                    $q_vox->save();
                }
                
                if(implode(';', $help_array) != $question->question_trigger) {
                    $history_info.= 'OLD Question trigger: '.$question->question_trigger.'<br/>';
                    $history_info.= 'NEW Question trigger: '.implode(';', $help_array).'<br/>';
                }

                $question->question_trigger = implode(';', $help_array);
            } else {
                if($this->request->input('question_trigger') != $question->question_trigger) {
                    $history_info.= 'OLD Question trigger: '.$question->question_trigger.'<br/>';
                    $history_info.= 'NEW Question trigger: null<br/>';
                }
                $question->question_trigger = '';
            }
        }

        if(isset($data['number-min']) && $data['number-max'] && (!empty($data['number-min']) || $data['number-min'] === '0' ) && !empty($data['number-max'])) {
            $array = [
                $data['number-min'],
                $data['number-max']
            ];
            
            if(implode(':', $array) != $question->number_limit) {
                $history_info.= 'OLD Number limit: '.$question->number_limit.'<br/>';
                $history_info.= 'NEW Number limit: '.implode(':', $array).'<br/>';
            }

            $question->number_limit = implode(':', $array);
        } else {
            if($this->request->input('number_limit') != $question->number_limit) {
                $history_info.= 'OLD Number limit: '.$question->number_limit.'<br/>';
                $history_info.= 'NEW Number limit: null<br/>';
            }
            $question->number_limit = '';
        }

        if($justCopy) {
            if(isset($data['excluded_answers']) && !empty($data['excluded_answers'])) {
                if($this->request->input('excluded_answers') != $question->excluded_answers) {
                    $history_info.= 'OLD Excluded answers: '.json_encode($question->excluded_answers).'<br/>';
                    $history_info.= 'NEW Excluded answers: '.$this->request->input('excluded_answers').'<br/>';
                }
                $question->excluded_answers = $data['excluded_answers'];
            } else {
                if($this->request->input('excluded_answers') != $question->excluded_answers) {
                    $history_info.= 'OLD Excluded answers: '.json_encode($question->excluded_answers).'<br/>';
                    $history_info.= 'NEW Excluded answers: null<br/>';
                }
                $question->excluded_answers = null;
            }
        } else {
            if(isset($data['exclude_answers_checked']) && isset($data['excluded_answers']) && !empty(json_decode($data['excluded_answers'], true))) {
                if(json_decode($this->request->input('excluded_answers'), true) != $question->excluded_answers) {
                    $history_info.= 'OLD Exclude answers checked: '.json_encode($question->excluded_answers).'<br/>';
                    $history_info.= 'NEW Exclude answers checked: '.$data['excluded_answers'].'<br/>';
                }
                $question->excluded_answers = json_decode($data['excluded_answers'], true);
            } else {
                $question->excluded_answers = null;
            }
        }

        $question->save();

        if(isset($data['prev_q_order'])) {
            $prev_q = VoxQuestion::where('vox_id', $question->vox_id)->where('order', $data['prev_q_order'])->first()->id;

            if($prev_q != $question->number_limit) {
                $history_info.= 'OLD Prev q id answers: '.$question->number_limit.'<br/>';
                $history_info.= 'NEW Prev q id answers: '.$prev_q.'<br/>';
            }

            $question->prev_q_id_answers = $prev_q;
            $question->save();
        }        

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
                $translation->vox_id = $question->vox_id;

                if($this->request->input('question-'.$key) != $translation->question) {
                    $history_info.= 'OLD Question '.$key.': '.$translation->question.'<br/>';
                    $history_info.= 'NEW Question '.$key.': '.$this->request->input('question-'.$key).'<br/>';
                }
                if($this->request->input('stats_title-'.$key) != $translation->stats_title) {
                    $history_info.= 'OLD Stats title '.$key.': '.$translation->stats_title.'<br/>';
                    $history_info.= 'NEW Stats title '.$key.': '.$this->request->input('stats_title-'.$key).'<br/>';
                }

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
                if(!empty( $data['rank_explanation-'.$key])) {
                    $translation->rank_explanation = $data['rank_explanation-'.$key];
                } else {
                    $translation->rank_explanation = '';
                }
                if(!empty( $data['stats_subtitle-'.$key] )) {
                    $translation->stats_subtitle = $data['stats_subtitle-'.$key];
                } else {
                    $translation->stats_subtitle = '';
                }
                //dd($data['answers-'.$key]);
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

                    $answrs = json_encode( $data['answers-'.$key], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );

                    if($answrs != $translation->answers) {
                        $history_info.= 'OLD Answers '.$key.': '.$translation->answers.'<br/>';
                        $history_info.= 'NEW Answers '.$key.': '.$answrs.'<br/>';
                    }

                    $translation->answers = $answrs;
                } else {
                    if($this->request->input('answers-'.$key) != $translation->answers) {
                        $history_info.= 'OLD Answers '.$key.': '.$translation->answers.'<br/>';
                        $history_info.= 'Answers: null<br/>';
                    }
                    $translation->answers = '';                            
                }

                $translation->save();
            }
        }
        $question->save();

        if($question->vox->type == 'normal') {
            foreach (config('langs-to-translate') as $lang_code => $value) {
                if($lang_code != 'en') {
                    VoxHelper::translateQuestionWithAnswers($lang_code, $question);
                }
            }
        }

        if( Input::file('answer-photos') ) {
            $image_filename = [];

            foreach (json_decode($question->answers, true) as $k => $v) {

                if(!empty(Input::file('answer-photos')[$k])) {

                    $extensions = ['image/jpeg', 'image/png'];
    
                    if (!in_array(Input::file('answer-photos')[$k]->getMimeType(), $extensions)) {
                        $this->request->session()->flash('error-message', 'File extension not supported' );
                        return redirect('cms/vox/edit/'.$question->vox_id.'/question/'.$question->id);
                    }

                    $unique = 'ans-'.mb_substr(microtime(true), 0, 10).$k;

                    $image_filename[] = $unique;
                    $img = Image::make( Input::file('answer-photos')[$k] )->orientate();
                    $question->addAnswerImage($img, $unique);
                } else {
                    if(!empty($data['filename'][$k])) {
                        $image_filename[] = $data['filename'][$k];
                    } else {
                        $image_filename[] = '';
                    }
                }
            }

            $question->answers_images_filename = json_encode($image_filename);
            $question->save();

            $history_info.= 'NEW Answer photos<br/>';

        } else if(!empty($data['filename']) && !in_array(null, $data['filename'])) {
            $imgs_arr = [];
            foreach (json_decode($question->answers, true) as $k => $v) {
                $imgs_arr[] = !empty($data['filename'][$k]) ? $data['filename'][$k] : '';
            }

            if(json_encode($imgs_arr) != $question->answers_images_filename) {
                $history_info.= 'NEW Answer filenames: '.json_encode($imgs_arr).'<br/>';
            }

            $question->answers_images_filename = json_encode($imgs_arr);
            $question->save();
        }

        if( Input::file('question-photo') ) {
            
            $extensions = ['image/jpeg', 'image/png'];

            if (!in_array(Input::file('question-photo')->getMimeType(), $extensions)) {
                $this->request->session()->flash('error-message', 'File extension not supported' );
                return redirect('cms/vox/edit/'.$question->vox_id.'/question/'.$question->id);
            }

            $img = Image::make( Input::file('question-photo') )->orientate();
            $question->addImage($img);

            $history_info.= 'NEW Question image<br/>';

            if(empty($question->image_in_question) && empty($question->image_in_tooltip)) {
                $question->image_in_question = true;
                $question->save();
            }
        }

        if(!empty($history_info)) {
            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $question->vox_id;
            $vox_history->question_id = $question->id;
            $vox_history->info = $history_info;
            $vox_history->save();
        }

        session([
            'brackets' => $sess
        ]);
    }

    public function categories( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        return $this->showView('vox-categories', array(
            'categories' => VoxCategory::with(['voxes', 'voxes.vox.translations', 'translations'])->orderBy('id', 'ASC')->get()
        ));
    }

    public function add_category( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');
        }

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

                $extensions = ['image/jpeg', 'image/png'];

                if (!in_array(Input::file('icon')->getMimeType(), $extensions)) {
                    $this->request->session()->flash('error-message', 'File extension not supported' );
                    return redirect('cms/vox/categories');
                }
                
                $img = Image::make( Input::file('icon') )->orientate();
                $item->addImage($img);
            }
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.category.added'));
            return redirect('cms/vox/categories');
        }

        return $this->showView('vox-categories-form');
    }

    public function delete_category( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        VoxCategory::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.category.deleted') );
        return redirect('cms/vox/categories');
    }

    public function delete_cat_image( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = VoxCategory::find($id);

        if(!empty($item)) {
            $item->hasimage = false;
            $item->save();
        }

        $this->request->session()->flash('success-message', trans('Image Deleted') );
        return redirect('cms/vox/categories/edit/'.$id);
    }

    public function edit_category( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

                    $extensions = ['image/jpeg', 'image/png'];
    
                    if (!in_array(Input::file('icon')->getMimeType(), $extensions)) {
                        $this->request->session()->flash('error-message', 'File extension not supported' );
                        return redirect('cms/vox/categories');
                    }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        return $this->showView('vox-scales', array(
            'scales' => VoxScale::with('translations')->orderBy('id', 'DESC')->get()
        ));
    }

    public function add_scale( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $item = new VoxScale;
            $this->saveOrUpdateScale($item);

            foreach (config('langs-to-translate') as $lang_code => $value) {
                if($lang_code != 'en') {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$item->translateOrNew('en')->answers."&target_lang=".strtoupper($lang_code));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $answers = curl_exec ($ch);
                    curl_close ($ch);

                    $translation = $item->translateOrNew($lang_code);
                    $translation->vox_scale_id = $item->id;
                    $translation->answers = isset(json_decode($answers, true)['translations']) ? json_decode($answers, true)['translations'][0]['text'] : '';
                    $translation->save();
                }
            }

            $item->save();

            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.'.$this->current_subpage.'.added'));
            return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/edit/'.$item->id);
        }

        return $this->showView('voxes-scale-form', array(
            'scales' => $this->scales_arr,
        ));
    }

    private function saveOrUpdateScale($item) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        User::whereNull('deleted_at')->update(['update_vox_scales' => 1]);
    }

    public function edit_scale( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = VoxScale::find($id);

        if( $item ) {

            if(Request::isMethod('post')) {
                $this->saveOrUpdateScale($item);

                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.'.$this->current_subpage.'.updated'));
                return redirect('cms/'.$this->current_page.'/'.$this->current_subpage.'/edit/'.$item->id);
            }

            return $this->showView('voxes-scale-form', array(
                'item' => $item,
                'scales' => $this->scales_arr,
            ));
        }
    }

    public function delete_scale( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = VoxScale::find($id);

        if( $item ) {
            $item->delete();
            Request::session()->flash('success-message', 'Scale deleted');
        }

        return redirect('cms/'.$this->current_page.'/'.$this->current_subpage);
    }

    public function faq() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $pathToFile = base_path().'/resources/lang/en/faq.php';
        $content = json_decode( file_get_contents($pathToFile), true );

        if(Request::isMethod('post') && request('faq')) {
            file_put_contents($pathToFile, json_encode(request('faq')));
            $this->request->session()->flash('success-message', 'FAQs are saved!');

            return Response::json([
                'success' => true
            ]);
        }

        return $this->showView('voxes-faq', array(
            'content' => $content
        ));
    }

    public function faqiOS() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $pathToFile = base_path().'/resources/lang/en/faq-ios.php';
        $content = json_decode( file_get_contents($pathToFile), true );

        if(Request::isMethod('post') && request('faq')) {
            file_put_contents($pathToFile, json_encode(request('faq')));
            $this->request->session()->flash('success-message', 'FAQs are saved!');

            return Response::json([
                'success' => true
            ]);
        }

        return $this->showView('voxes-faq', array(
            'content' => $content
        ));
    }

    public function explorer($vox_id=null,$question_id=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
                    $items_count = VoxAnswer::with(['user.country', 'user.country.translations'])
                    ->whereNull('is_admin')
                    ->where('question_id',$question_id )
                    ->select('vox_answers.*')
                    ->where('is_completed', 1)
                    ->where('is_skipped', 0)
                    ->where('answer', '!=', 0)
                    ->has('user')
                    ->join('users', 'vox_answers.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->count();

                    $items_count_old = VoxAnswerOld::with(['user.country', 'user.country.translations'])
                    ->whereNull('is_admin')
                    ->where('question_id',$question_id )
                    ->select('vox_answer_olds.*')
                    ->where('is_completed', 1)
                    ->where('is_skipped', 0)
                    ->where('answer', '!=', 0)
                    ->has('user')
                    ->join('users', 'vox_answer_olds.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->count();

                    $items_count = $items_count + $items_count_old;
                } else {
                    $items_count = VoxAnswer::with(['user.country', 'user.country.translations'])
                    ->whereNull('is_admin')
                    ->where('question_id',$question_id )
                    ->where('is_completed', 1)
                    ->where('is_skipped', 0)
                    ->where('answer', '!=', 0)
                    ->has('user')
                    ->count();

                    $items_count_old = VoxAnswerOld::with(['user.country', 'user.country.translations'])
                    ->whereNull('is_admin')
                    ->where('question_id',$question_id )
                    ->where('is_completed', 1)
                    ->where('is_skipped', 0)
                    ->where('answer', '!=', 0)
                    ->has('user')
                    ->count();

                    $items_count = $items_count + $items_count_old;
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
                $question_respondents = VoxAnswer::with(['user.country', 'user.country.translations'])
                ->whereNull('is_admin')
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

                $question_respondents = $question_respondents->get();

                $question_respondents_old = VoxAnswerOld::with(['user.country', 'user.country.translations'])
                ->whereNull('is_admin')
                ->where('question_id',$question_id )
                ->where('is_completed', 1)
                ->where('is_skipped', 0)
                ->where('answer', '!=', 0)
                ->has('user')
                ->select('vox_answer_olds.*');

                if (request()->input( 'country' )) {

                    $order = request()->input( 'country' );
                    $question_respondents_old = $question_respondents_old
                    ->join('users', 'vox_answer_olds.user_id', '=', 'users.id')
                    ->join('countries', 'users.country_id', '=', 'countries.id')
                    ->orderBy('countries.name', $order);

                } else if (request()->input( 'name' )) {

                    $order = request()->input( 'name' );
                    $question_respondents_old = $question_respondents_old
                    ->join('users', 'vox_answer_olds.user_id', '=', 'users.id')
                    ->orderBy('users.name', $order);

                } else if (request()->input( 'taken' )) {

                    $order = request()->input( 'taken' );
                    $question_respondents_old = $question_respondents_old
                    ->orderBy('created_at', $order);

                } else if (request()->input( 'type' )) {

                    $order = request()->input( 'type' );
                    $question_respondents_old = $question_respondents_old
                    ->join('users', 'vox_answer_olds.user_id', '=', 'users.id')
                    ->orderBy('users.is_dentist', $order)
                    ->orderBy('users.is_clinic', $order);

                } else {
                    $question_respondents_old = $question_respondents_old
                    ->orderBy('created_at', 'desc');
                }

                $question_respondents_old = $question_respondents_old->get();

                $question_respondents = $question_respondents->concat($question_respondents_old);

                if (request()->input( 'show-more' )) {
                    // $question_respondents = $question_respondents->get();
                    $show_button = false;
                    $show_all_button = false;
                    $show_pagination = false;
                    $respondents_shown = $items_count;
                } else {
                    // $question_respondents = $question_respondents->skip( ($page-1)*$ppp )->take($ppp)->get();
                    $question_respondents = $this->paginate($question_respondents, $ppp)
                    ->withPath('cms/vox/explorer/'.($vox_id ? $vox_id.($question_id ? '/'.$question_id : '') : ''));
                }

                $respondents = '';

            } else {
                $respondents = DcnReward::with(['user', 'user.country'])
                ->where('reference_id',$vox_id )
                ->where('platform', 'vox')
                ->where('type', 'survey')
                ->has('user')
                ->select('dcn_rewards.*');

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

                $respondents = $respondents->get();

                if (request()->input( 'show-more' )) {
                    $show_button = false;
                    $show_all_button = false;
                    $show_pagination = false;
                    $respondents_shown = $items_count;
                } else {
                    $respondents = $this->paginate($respondents, $ppp)->withPath('cms/vox/explorer/'.($vox_id ? $vox_id.($question_id ? '/'.$question_id : '') : ''));
                }

                $question_respondents = '';
            }

            $total_count = $items_count;
            $total_pages = ceil($total_count/$ppp);

            $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
            $start = $paginations['start'];
            $end = $paginations['end'];

            $current_url = url('cms/vox/explorer/'.$vox_id.($question_id ? '/'.$question_id : '') );

            $pagination_link = "";
            foreach (Request::all() as $key => $value) {
                if($key != 'page') {
                    $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
                }
            }

            $viewParams = [
                'show_pagination' => $show_pagination,
                'question_respondents' => $question_respondents,
                'question' => $question,
                'vox_id' => $vox_id,
                'respondents' => $respondents,
                'vox' => $vox,
                'voxes' => Vox::with(['translations', 'questions.translations'])->orderBy('launched_at', 'desc')->get(),
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
                'voxes' => Vox::with(['translations', 'questions.translations'])->orderBy('launched_at', 'desc')->get(),
            ];
        }

        return $this->showView('voxes-explorer', $viewParams);
    }

    private function paginate($items, $perPage = 50, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function export_survey_data() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
                if( $question->type == 'single_choice' || $question->type == 'number' ) {
                    $cols[] = $question->question;
                    $cols2[] = '';
                    $cols3[] = $this->exportQuestionTriggers($question);
                } else if( $question->type == 'scale' || $question->type == 'rank' ) {
                    $list = json_decode($question->answers, true);
                    foreach ($list as $l) {
                        $cols[] = $question->question;
                        $cols2[] = $l;
                        $cols3[] = $this->exportQuestionTriggers($question);
                    }
                } else if( $question->type == 'multiple_choice' ) {
                    $list = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) :  json_decode($question->answers, true);
                    foreach ($list as $l) {
                        $cols[] = $question->question;
                        $cols2[] = mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l;
                        $cols3[] = $this->exportQuestionTriggers($question);
                    }
                }                
            }

            $rows = [
                $cols,
                $cols2,
                $cols3
            ];

            $users = DcnReward::where('reference_id', $vox->id)
            ->where('platform', 'vox')
            ->where('type', 'survey')
            ->with('user');

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

                $answers_old = VoxAnswerOld::whereNull('is_admin')
                ->where('user_id', $user->user->id)
                ->where('vox_id', $vox->id)
                ->get();

                $answers = $answers->concat($answers_old);

                foreach ($vox->questions as $question) {
                    $qid = $question->id;
                    $qanswers = $answers->filter( function($item) use ($qid) {
                        return $qid == $item->question_id;
                    } );

                    if( $question->type == 'single_choice' ) {
                        $answerwords = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true);
                        $row[] = $qanswers->last() && $qanswers->last()->answer && isset( $answerwords[ ($qanswers->last()->answer)-1 ] ) ? $answerwords[ ($qanswers->last()->answer)-1 ] : '';
                    } else if( $question->type == 'number' ) {
                        $row[] = $qanswers->last() ? $qanswers->last()->answer : '';
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

                    } else if( $question->type == 'rank' ) {
                        $list = json_decode($question->answers, true);
                        $answerwords = $question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true);

                        foreach ($list as $k => $l) {
                            foreach ($qanswers as $qa) {
                                if($qa->scale == $k + 1) {
                                    $row[] = $qa->answer;
                                }
                            }
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
            $file_to_export = Excel::download($export, $fname.'.xlsx');
            ob_end_clean();
            return $file_to_export;
        }

        return $this->showView('voxes-export-survey-data', array(
            'voxes' => Vox::with('translations')->orderBy('launched_at', 'desc')->get()
        ));
    }

    public function duplicate_question() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $qObj = VoxQuestion::find($this->request->input('d-question'));
        $q = $qObj->toArray();

        foreach ($this->langs as $key => $value) {
            $translation = $qObj->translateOrNew($key);
            $q['question-'.$key] = $translation->question;
            $q['answers-'.$key] = json_decode($translation->answers);
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

            foreach (config('langs-to-translate') as $lang_code => $value) {
                if($lang_code != 'en') {
                    VoxHelper::translateQuestionWithAnswers($lang_code, $question);
                }
            }
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function getTitle() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $title = trim(Request::input('title'));

        $test_surveys_ids = [48,80];

        $voxes = Vox::with('translations')
        ->whereNotIn('id', $test_surveys_ids)
        ->whereHas('translations', function ($query) use ($title) {
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

        $questions = VoxQuestion::has('vox')
        ->whereNotIn('vox_id', $test_surveys_ids)
        ->whereHas('translations', function ($query) use ($title) {
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {

            $delqs = VoxQuestion::whereIn('id', Request::input('ids'))->get();
            foreach ($delqs as $dq) {
                $dq->delete();
            }
        }

        $this->request->session()->flash('success-message', 'All selected questions are deleted' );
        return redirect(url()->previous());
    }

    public function deleteAnswerImage( $vox_id, $q_id, $answer ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($q_id);

        if(!empty($question)) {
            $images_files = json_decode($question->answers_images_filename, true);
            
            $k = array_search($answer, $images_files ) ;
            if($k) {
                unset($images_files[$k]);
            }

            $question->answers_images_filename = json_encode($images_files);
            $question->save();
        }

        return Response::json([
            'success' => true,
        ]);
    }

    public function deleteQuestionImage( $vox_id, $q_id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = VoxQuestion::find($q_id);

        if(!empty($item)) {
            $vox_history = new VoxHistory;
            $vox_history->admin_id = $this->user->id;
            $vox_history->vox_id = $vox_id;
            $vox_history->question_id = $q_id;
            $vox_history->info = 'Question Image Deleted';
            $vox_history->save();

            $item->has_image = false;
            $item->save();
        }

        $this->request->session()->flash('success-message', 'Photo deleted!' );
        return redirect('cms/'.$this->current_page.'/edit/'.$vox_id.'/question/'.$q_id);
    }

    private function exportQuestionTriggers($question) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        if($question->question_trigger) {

            if($question->question_trigger == '-1') {
                return 'Trigger: SAME AS BEFORE';
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
                                $answer_names[] = isset(json_decode(VoxQuestion::find($triggerId)->answers, true)[intval($value)-1]) ? json_decode(VoxQuestion::find($triggerId)->answers, true)[intval($value)-1] : $value;
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
                                $triggers[] = VoxQuestion::find($tq)->question.' - '.implode(',', $trigger_ans[$tq]);
                                
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

                    return 'Trigger: (trigger logic '.$trg_logic.') '.$trg;
                } else {
                    return '';
                }
            }
        } else {
            return '';
        }
    }

    public function exportStats() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');
        }

        if( empty(request('chosen-qs'))) {
            $this->request->session()->flash('error-message', 'Please, choose questions!' );
            return redirect('cms/vox/edit/'.request('vox-id'));
        }

        if( empty(request('demographics'))) {
            $this->request->session()->flash('error-message', 'Please, choose demographics!' );
            return redirect('cms/vox/edit/'.request('vox-id'));
        }

        // SELECT * FROM `vox_answers_dependencies` WHERE `question_dependency_id` = 2951 AND `question_id` = 15910
        // 823 total

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        $vox = Vox::find(request('vox-id'));
        $all_period = $vox->launched_at ? date('d/m/Y',strtotime($vox->launched_at)).'-'.date('d/m/Y') : date('d/m/Y',strtotime($vox->created_at)).'-'.date('d/m/Y');
        $demographics = request('demographics');

        //ako e scale ??
        $export_array = [];
        foreach(request('chosen-qs') as $q_id) {
            $q = VoxQuestion::find($q_id);

            $results_old = VoxAnswerOld::whereNull('is_admin')
            ->where('question_id', $q_id)
            ->where('is_completed', 1)
            ->where('is_skipped', 0)
            ->has('user');

            $results = VoxAnswer::whereNull('is_admin')
            ->where('question_id', $q_id)
            ->where('is_completed', 1)
            ->where('is_skipped', 0)
            ->has('user');

            if($q->type == 'scale') {
                foreach (json_decode($q->answers, true) as $key => $scale) {
                    if(empty($q->stats_scale_answers) || (!empty($q->stats_scale_answers) && in_array(($key + 1), json_decode($q->stats_scale_answers, true)))) {

                        $results_old = VoxAnswerOld::whereNull('is_admin')
                        ->where('question_id', $q_id)
                        ->where('is_completed', 1)
                        ->where('is_skipped', 0)
                        ->has('user');

                        $results = VoxAnswer::whereNull('is_admin')
                        ->where('question_id', $q_id)
                        ->where('is_completed', 1)
                        ->where('is_skipped', 0)
                        ->has('user');

                        $export_array[] = VoxHelper::exportStatsXlsx($vox, $q, $demographics, $results, $results_old, $key+1, $all_period, true);
                    }
                }
            } else {
                $export_array[] = VoxHelper::exportStatsXlsx($vox, $q, $demographics, $results, $results_old, null, $all_period, true);
            }
        }

        $document = [
            'flist' => [
                "Raw Data" => [],
                "Breakdown" => [],
            ],
            "breakdown_rows_count" => 0
        ];

        foreach($export_array as $key => $exportArr) {
            foreach($exportArr['flist']["Raw Data"] as $raw_data) {
                $document['flist']["Raw Data"][] = $raw_data;
            }
            foreach($exportArr['flist']["Breakdown"] as $breakdown_data) {
                $document['flist']["Breakdown"][] = $breakdown_data;
            }
            $document['breakdown_rows_count'] = $exportArr["breakdown_rows_count"]++;
        }

        $fname = $vox->title;
        $pdf_title = strtolower(str_replace(['?', ' ', ':', "'"], ['', '-', '', ''] ,$fname)).'-dentavox'.mb_substr(microtime(true), 0, 10);
        $downloaded_content = (new MultipleStatSheetExport($document['flist'], $document['breakdown_rows_count']))->download($pdf_title.'.xlsx');
        ob_end_clean();

        return $downloaded_content;
    }

    public function getQuestionsCount($vox_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $vox = Vox::find($vox_id);

        if( !empty($vox)) {
            return Response::json([
                'q_count' => $vox->questions->count(),
            ]);
        }
    }

    public function getRespondentsCount($vox_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $vox = Vox::find($vox_id);

        if( !empty($vox)) {
            return Response::json( [
                'resp_count' => $vox->realRespondentsCountForAdminPurposes(),
            ]);
        }
    }

    public function getRespondentsQuestionCount($question_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($question_id);

        if( !empty($question)) {
            return Response::json( [
                'resp_count' => $question->respondent_count(),
            ]);
        }
    }

    public function getReward($vox_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $vox = Vox::find($vox_id);

        if( !empty($vox)) {
            return Response::json( [
                'reward' => $vox->getRewardTotal(),
            ]);
        }
    }

    public function getDuration($vox_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $vox = Vox::find($vox_id);

        if( !empty($vox)) {
            return Response::json( [
                'duration' => '~'.ceil($vox->questions()->count()/6).'min',
            ]);
        }
    }

    public function showAllResults() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        session([
            'vox-show-all-results' => true
        ]);

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/vox/list');
    }

    public function showIndividualResults() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        session([
            'vox-show-all-results' => false
        ]);

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/ban_appeals');
    }

    public function test() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $questions_single = VoxQuestion::where('type', 'single_choice')->whereNull('vox_scale_id')->orderBy('id', 'DESC')->take(3)->get();
        $questions_multiple = VoxQuestion::where('type', 'multiple_choice')->orderBy('id', 'DESC')->take(3)->get();
        $questions_scale = VoxQuestion::where('type', 'scale')->orderBy('id', 'DESC')->take(3)->get();
        $questions_number = VoxQuestion::where('type', 'number')->orderBy('id', 'DESC')->take(3)->get();
        $questions_rank = VoxQuestion::where('type', 'rank')->orderBy('id', 'DESC')->take(3)->get();
        $questions_cross_check = VoxQuestion::whereNotNull('cross_check')->orderBy('id', 'DESC')->take(15)->get();
        $questions_prev_q_answer = VoxQuestion::whereNotNull('prev_q_id_answers')->orderBy('id', 'DESC')->take(3)->get();
        $questions_with_scale_answers = VoxQuestion::where('type', 'single_choice')->whereNotNull('vox_scale_id')->orderBy('id', 'DESC')->take(3)->get();
        $questions_with_image_tooltip = VoxQuestion::whereNotNull('has_image')->whereNotNull('image_in_tooltip')->orderBy('id', 'DESC')->take(3)->get();
        $questions_with_image_question = VoxQuestion::whereNotNull('has_image')->whereNotNull('image_in_question')->orderBy('id', 'DESC')->take(3)->get();
        $questions_with_image_answers = VoxQuestion::whereNotNull('answers_images_filename')->orderBy('id', 'DESC')->take(3)->get();
        $questions_control = VoxQuestion::whereNotNull('is_control')->orderBy('id', 'DESC')->take(3)->get();
        $questions_control_trigger_and = VoxQuestion::where('trigger_type', 'and')->orderBy('id', 'DESC')->take(3)->get();
        $questions_control_trigger_or = VoxQuestion::where('trigger_type', 'or')->orderBy('id', 'DESC')->take(3)->get();

        $questions_stats_multiple = VoxQuestion::where('used_for_stats', 'standard')
        ->whereHas('vox', function($query) {
            $query->where('has_stats', 1);
        })->where('type', 'multiple_choice')
        ->orderBy('id', 'DESC')
        ->take(3)
        ->get();

        $questions_stats_scale = VoxQuestion::where('used_for_stats', 'standard')
        ->whereHas('vox', function($query) {
            $query->where('has_stats', 1);
        })->where('type', 'scale')
        ->orderBy('id', 'DESC')
        ->take(3)
        ->get();

        $questions_stats_number = VoxQuestion::where('used_for_stats', 'standard')
        ->whereHas('vox', function($query) {
            $query->where('has_stats', 1);
        })->where('type', 'number')
        ->orderBy('id', 'DESC')
        ->take(3)
        ->get();

        $questions_stats_rank = VoxQuestion::where('used_for_stats', 'standard')
        ->whereHas('vox', function($query) {
            $query->where('has_stats', 1);
        })->where('type', 'rank')
        ->orderBy('id', 'DESC')
        ->take(3)
        ->get();

        $questions_stats_dependency_single = VoxQuestion::where('used_for_stats', 'dependency')
        ->whereHas('vox', function($query) {
            $query->where('has_stats', 1);
        })->whereHas('related', function($query) {
            $query->where('type', 'single_choice');
        })->orderBy('id', 'DESC')
        ->take(3)
        ->get();

        $questions_stats_dependency_multiple = VoxQuestion::where('used_for_stats', 'dependency')
        ->whereHas('vox', function($query) {
            $query->where('has_stats', 1);
        })->whereHas('related', function($query) {
            $query->where('type', 'multiple_choice');
        })->orderBy('id', 'DESC')
        ->take(3)
        ->get();

        $arr = [
            'single' => [
                'name' => 'Single questions',
                'value' => $questions_single,
            ],
            'multiple' => [
                'name' => 'Multiple questions',
                'value' => $questions_multiple,
            ],
            'scale' => [
                'name' => 'Scale questions',
                'value' => $questions_scale,
            ],
            'number' => [
                'name' => 'Number questions',
                'value' => $questions_number,
            ],
            'rank' => [
                'name' => 'Rank questions',
                'value' => $questions_rank,
            ],
            'questions_cross_check' => [
                'name' => 'Questions with cross checks', //to add crosscheck
                'value' => $questions_cross_check,
            ],
            'questions_prev_q_answer' => [
                'name' => 'Questions with previous question answers',
                'value' => $questions_prev_q_answer,
            ],
            'questions_with_scale_answers' => [
                'name' => 'Questions with scale answers',
                'value' => $questions_with_scale_answers,
            ],
            'questions_with_image_tooltip' => [
                'name' => 'Questions with image in tooltip',
                'value' => $questions_with_image_tooltip,
            ],
            'questions_with_image_question' => [
                'name' => 'Questions with image in question',
                'value' => $questions_with_image_question,
            ],
            'questions_with_image_answers' => [
                'name' => 'Questions with image answers',
                'value' => $questions_with_image_answers,
            ],
            'questions_control' => [
                'name' => 'Control questions',
                'value' => $questions_control,
            ],
            'questions_control_trigger_and' => [
                'name' => 'Questions with trigger type "and"',
                'value' => $questions_control_trigger_and,
            ],
            'questions_control_trigger_or' => [
                'name' => 'Questions with trigger type "or"',
                'value' => $questions_control_trigger_or,
            ],
            'questions_stats_multiple' => [
                'name' => 'Stats for multiple choice question',
                'value' => $questions_stats_multiple,
            ],
            'questions_stats_scale' => [
                'name' => 'Stats for scale question',
                'value' => $questions_stats_scale,
            ],
            'questions_stats_number' => [
                'name' => 'Stats for number question',
                'value' => $questions_stats_number,
            ],
            'questions_stats_rank' => [
                'name' => 'Stats for rank question',
                'value' => $questions_stats_rank,
            ],
            'questions_stats_dependency_single' => [
                'name' => 'Dependency stats for single choice question',
                'value' => $questions_stats_dependency_single,
            ],
            'questions_stats_dependency_multiple' => [
                'name' => 'Dependency stats for multiple choice question',
                'value' => $questions_stats_dependency_multiple,
            ],
        ];

        return $this->showView('vox-test', [
            'arr' => $arr
        ]);
    }

    public function hideSurvey($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');
        }

        if(!$this->request->input('hide-survey-confirm') || strtolower($this->request->input('hide-survey-confirm')) != 'hide') {
            $this->request->session()->flash('error-message', 'The survey is not hidden' );
            return redirect('cms/vox/list');
        }

        $item = Vox::find($id);

        if(!empty($item)) {

            if($this->request->input('type') != $item->type) {
                $history_info = 'OLD Type: '.$item->type.'<br/>';
                $history_info.= 'NEW Type: '.$this->request->input('type').'<br/>';

                $vox_history = new VoxHistory;
                $vox_history->admin_id = $this->user->id;
                $vox_history->vox_id = $id;
                $vox_history->info = $history_info;
                $vox_history->save();
            }
            
            $item->type = 'hidden';
            $item->save();

            $this->request->session()->flash('success-message', 'The survey is hidden' );
            return redirect('cms/vox/list');
        }

        $this->request->session()->flash('error-message', 'Survey not found' );
        return redirect('cms/vox/list');
    }

    public function getQuestionContent($q_id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $question = VoxQuestion::find($q_id);
        $id = $question->vox_id;

        if(!empty($question)) {
            $trigger_question_id = null;
            $trigger_valid_answers = null;

            $triggers_ids = [];
            $trigger_type = null;

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
                    }
                }
            }

            if(empty( $trigger_question_id )) {
                $prev_question = VoxQuestion::where('vox_id', $id)->where('order', '<', intVal($question->order) )->orderBy('order', 'DESC')->first();
                $trigger_question_id = $prev_question ? $prev_question->id : '';
                $trigger_valid_answers = null;
            }

            $question_answers_count = DB::table('vox_answers')
            ->join('users', 'users.id', '=', 'vox_answers.user_id')
            ->whereNull('users.deleted_at')
            ->whereNull('vox_answers.is_admin')
            ->where('vox_id', $id )
            ->where('question_id', $q_id)
            ->where('is_completed', 1)
            ->where('is_skipped', 0)
            ->where('answer', '!=', 0)
            ->select('answer', DB::raw('count(*) as total'))
            ->groupBy('answer')
            ->get()
            ->pluck('total', 'answer')
            ->toArray();

            $excluded_answers = [];
            if(!empty($question->excluded_answers)) {
                foreach($question->excluded_answers as $excluded_answers_array) {
                    foreach($excluded_answers_array as $excluded_answ) {
                        $excluded_answers[] = $excluded_answ;
                    }
                }
            }

            return response()->view('admin.parts.vox-question', array(
                'langs' => config('langs')['admin'],
                'current_page' => $this->current_page,
                'question' => $question,
                'question_answers_count' => $question_answers_count,
                'scales' => $this->scales_arr,
                'item' => $question->vox,
                'question_types' => $this->question_types,
                'stat_top_answers' => $this->stat_top_answers,
                'stat_types' => $this->stat_types,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers,
                'triggers_ids' => $triggers_ids,
                'trigger_type' => $trigger_type,
                'excluded_answers' => $excluded_answers,
            ), 200)->header('X-Frame-Options', 'DENY');
        }
    }

    public function addQuestionContent($vox_id) {

        if(!in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $vox = Vox::find($vox_id);

        if( !empty($vox)) {

            $trigger_question_id = null;
            $trigger_valid_answers = null;

            $triggers_ids = [];
            $trigger_type = null;

            foreach ($vox->questions as $q) {
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
                    }
                }
            }

            if(empty( $trigger_question_id )) {
                $prev_question = VoxQuestion::where('vox_id', $vox_id)->orderBy('order', 'DESC')->first();
                $trigger_question_id = $prev_question ? $prev_question->id : '';
                $trigger_valid_answers = null;
            }

            return response()->view('admin.parts.vox-question', array(
                'langs' => config('langs')['admin'],
                'current_page' => $this->current_page,
                'question' => null,
                'scales' => $this->scales_arr,
                'item' => $vox,
                'question_types' => $this->question_types,
                'stat_top_answers' => $this->stat_top_answers,
                'stat_types' => $this->stat_types,
                'trigger_question_id' => $trigger_question_id,
                'trigger_valid_answers' => $trigger_valid_answers,
                'triggers_ids' => $triggers_ids,
                'trigger_type' => $trigger_type,
                'next' => $vox->questions->count()+1,
            ), 200)->header('X-Frame-Options', 'DENY');
        }
    }

    public function voxesHistory() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $history = VoxHistory::whereNotNull('admin_id');
        if(!empty(request('search-admin-id'))) {
            $history = $history->where('admin_id', request('search-admin-id'));
        }
        if(!empty(request('search-vox-id'))) {
            $history = $history->where('vox_id', request('search-vox-id'));
        }
        if(!empty(request('search-question-id'))) {
            $history = $history->where('question_id', request('search-question-id'));
        }
        $history = $history->orderBy('id', 'desc')->get();
        $history = $this->paginate($history)->withPath('cms/vox/history/');

        return $this->showView('voxes-history', array(
            'admins' => Admin::get(),
            'history' => $history,
            'search_admin_id' => request('search-admin-id'),
            'search_vox_id' => request('search-vox-id'),
            'search_question_id' => request('search-question-id'),
        ));
    }

    public function errorsResolved() {

        if(!in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $vox_error = VoxError::first();
        $vox_error->is_read = true;
        $vox_error->save();

        return Response::json( [
            'success' => true,
        ]);
    }

    public function checkVoxForChanges($vox_id) {

        $vox = Vox::find($vox_id);

        if(!empty($vox)) {

            $has_changes = false;

            if($vox->historyOnlyVox->isNotEmpty()) {
                if($vox->historyOnlyVox->first()->created_at > Carbon::parse(request()->input('date'))) {
                    $has_changes = true;
                }
            }
    
            return Response::json( [
                'success' => true,
                'has_changes' => $has_changes
            ]);
        }

        return Response::json( [
            'success' => false,
        ]);

    }

    // public function duplicateSurvey($id) {
        
    //     if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'voxer', 'support'])) {
    //         $this->request->session()->flash('error-message', 'You don\'t have permissions' );
    //         return redirect('cms/home');            
    //     }

    //     $vox = Vox::with(['translations', 'categories', 'related'])->find($id);
    //     // $vox = Vox::with(['translations', 'questions', 'categories', 'related'])->find($id);

    //     // dd($vox->getRelations());

    //     // $vox->load('invoices');

    //     $newVox = $vox->replicate();
    //     $newVox->push();
    //     $newVox->type = 'hidden';
    //     $newVox->translation_langs = null;
    //     $newVox->save();

    //     // dd($vox->getRelations());
    //     // dd($newVox);


    // // ne zapazwa localeto

    //     $relation = 'translations';
    //     $newVox->{$relation}()->create([
    //         "title" => "20 Questions: Dental Visits",
    //         "locale" => "en",
    //         "description" => "Are dental visits always painful? Should you visit the dentist only when a tooth starts hurting? Are dental X-rays really harmful? In what are orthodontic denti",
    //         "stats_description" => "Are dental visits always painful? Should you visit the dentist only when a tooth starts hurting? Are dental X-rays really harmful? In what are orthodontic denti",
    //         "slug" => "20-questions-myths-facts-dental-visits",
    //         "options" => null,
    //         "vox_id" => $newVox->id,
    //     ]);

    //     foreach($vox->getRelations() as $relation => $items){
    //         $newRelationn = [];
    //         foreach($items as $item){
                
    //             $newRelation = [];
    //             if(!empty($item)) {
    //                 // dd($items);
    //                 unset($item->id);
    //                 unset($item->vox_id);
    //                 $newRelation = $item->toArray();
    //                 $newRelation['vox_id'] = $newVox->id;

    //                 $newRelationn[] = $newRelation;
    //                 dd($newRelation);
    //                 $newVox->{$relation}()->create($newRelation);
    //             }
    //         }

    //         dd($newRelationn);
    //     }

    //     $this->request->session()->flash('error-message', 'Survey duplicated' );
    //     return redirect('cms/vox/list/');
    // }
}