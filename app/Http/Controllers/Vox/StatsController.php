<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\VoxAnswersDependency;
use App\Models\UserGuidedTour;
use App\Models\VoxQuestion;
use App\Models\VoxCategory;
use App\Models\VoxAnswer;
use App\Models\VoxScale;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\User;
use App\Models\Dcn;
use App\Models\Vox;

use App\Exports\MultipleStatSheetExport;
use App\Exports\Export;
use App\Imports\Import;
use Carbon\Carbon;
use Dompdf\Dompdf;

use Validator;
use Response;
use Storage;
use Request;
use Route;
use Hash;
use Mail;
use Auth;
use App;
use PDF;
use DB;

class StatsController extends FrontController {

    public function home($locale=null) {

        $this->current_page = 'stats';

        $sorts = [
            // 'featured' => trans('vox.page.stats.sort-featured'),
            'all' => trans('vox.page.stats.sort-all'),
            'newest' => trans('vox.page.stats.sort-newest'),
            // 'popular' => trans('vox.page.stats.sort-popular'),
        ];

        $name = !empty(Request::input('survey-search')) ? Request::input('survey-search') : null;

        if(Request::isMethod('post') && !empty($name)) {

            $searchValues = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY); 

            $voxes = Vox::where('type', '!=', 'hidden')
            ->with('stats_main_question')
            ->where('has_stats', 1)
            ->with('translations')
            ->whereHas('translations', function ($query) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $query->where(function($q) use ($value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    });
                }
            })->orderBy('stats_featured', 'DESC')
            ->get();

        } else {

            $voxes = Vox::with('stats_main_question')->where('has_stats', 1)->with('translations');

            if (!Auth::guard('admin')->user()) {
                $voxes = $voxes->where('type', '!=', 'hidden');
            }

            $voxes = $voxes->get();

            $voxes = $voxes->sortByDesc(function ($vox, $key) {
                if($vox->stats_featured) {
                    return 10000000000 + ($vox->launched_at ? $vox->launched_at->timestamp : 0);
                } else {

                    return 10000 + ($vox->launched_at ? $vox->launched_at->timestamp : 0);
                }
            });

            $voxes = $this->paginate($voxes)->withPath(App::getLocale().'/dental-survey-stats/');
        }

        $seos = PageSeo::find(11);

        return $this->ShowVoxView('stats', array(
            'name' => $name,
            'taken' => $this->user ? $this->user->filledVoxes() : [],
            'canonical' => getLangUrl('dental-survey-stats'),
            'countries' => Country::with('translations')->get(),
            'voxes' => $voxes,
            'cats' => VoxCategory::with('voxes.vox')->with('translations')->get(),
            'sorts' => $sorts,
            'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
            'js' => [
                'stats.js',
            ],
            'css' => [
                'vox-stats.css',
            ],
        ));
    }

    public function stats($locale=null, $slug=null, $question_id=null) {
        
        $this->current_page = 'stats';

        $vox = Vox::whereTranslationLike('slug', $slug)->first();

        if(empty($vox)) {
            return redirect( getLangUrl('page-not-found') );
        }

        if(!$vox->has_stats && empty($this->admin)) {
            return redirect( getLangUrl('dental-survey-stats') );
        }

        if(request('app')) {

            if(request('app-user-id')) {

                $user_id = User::decrypt(request('app-user-id'));

                if($user_id) {

                    $user = User::find($user_id);

                    if(!empty($user)) {

                        Auth::login($user);

                        return redirect(getLangUrl('dental-survey-stats/'.$slug).'?'. http_build_query(['app'=>1]));
                    }
                }
            } else {
                Auth::guard('web')->logout();
            }
        }

        if(!empty($this->user) && $this->user->isBanned('vox')) {
            return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
        }

        if(!empty($this->user) && $this->user->is_dentist) {
            $gt = UserGuidedTour::where('user_id', $this->user->id)->first();

            if(!empty($gt) && (empty($gt->check_stats_on) || (!empty($gt->check_stats_on) && $gt->check_stats_on < Carbon::now()->subDays(1)))) {
                $gt->check_stats_on = Carbon::now();
                $gt->save();
            }
        }

        if(Request::isMethod('post')) {
            $dates = !empty(Request::input('download-date')) && Request::input('download-date') != 'all' ? explode('-', Request::input('download-date')) : Request::input('timeframe');
            $answer_id = Request::input('answer_id');
            $question_id = Request::input('question_id');
            $scale_answer_id = !empty(Request::input('scale_answer_id')) ? Request::input('scale_answer_id') : null;
            $question = VoxQuestion::find($question_id);
            $scale = Request::input('scale');
            $scale_name = Request::input('scale_name');
            $type = $question->used_for_stats;
            $scale_options  = Request::input('scale_options');

            if(!empty($scale_options) && in_array('all', $scale_options)) {
                $removed_key = array_search('all', $scale_options);
                unset($scale_options[$removed_key]);
            }

            $answerField = $scale_answer_id ? 'scale' : 'answer';

            if($scale=='dependency') {
                if(!empty($question->stats_answer_id)) {
                    $answer_id = $question->stats_answer_id;
                }

                if (empty($answer_id)) {
                    $answer_id = 1;
                }
                if($dates || $question->respondent_count() < 50) {

                    $results = VoxAnswer::prepareQuery($question_id, $dates,[
                        'dependency_answer' => $answer_id,
                        'dependency_question' => $question->stats_relation_id,
                    ]);
                }
            } else {
                $results = VoxAnswer::prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id
                ]);
            }

            $main_chart = [];
            $second_chart = [];
            $third_chart = [];
            $relation_info = [];
            $totalf = 0;
            $totalm = 0;
            if( $question->vox_scale_id ) {
                $answers = explode(',', VoxScale::find($question->vox_scale_id)->answers );
                foreach ($answers as $key => $value) {
                    $answers[$key] = trim($question->removeAnswerTooltip($value));
                }
            } else {

                if($question->type == 'number') {
                    $answers_min = intval(explode(':',$question->number_limit)[0]);
                    $answers_max = intval(explode(':',$question->number_limit)[1]);

                    if($answers_max <= 10) {
                        for ($i=$answers_min; $i <= $answers_max; $i++) { 
                            $answers[] = $i;
                        }
                    } else {
                        if($answers_max <= 100) {
                            $number_answer = 10;
                        } else if($answers_max <= 1000) {
                            $number_answer = 100;
                        } else if($answers_max <= 10000) {
                            $number_answer = 1000;
                        }

                        $max_count = ceil($answers_max / 10) * 10;
                        $min_count = floor($answers_min / 10) * 10;

                        for ($i=$min_count; $i <= $max_count; $i+=$number_answer) {
                            if($i + $number_answer <= $max_count){

                                $answers[] = ($i == $min_count ? $i : $i + 1).'-'.($i + $number_answer);
                            }
                        }
                    }
                } else {
                    $ans_array = json_decode($question->answers);
                    foreach ($ans_array as $ans) {
                        $answers[] = strip_tags($question->removeAnswerTooltip($ans), ['a']);
                    }
                }
            }


            foreach ($answers as $key => $value) {
                // if(mb_strpos($value, '!')===0 || ($question->type != 'single_choice' && mb_strpos($value, '#')===0)) {
                //     $answers[$key] = mb_substr($value, 1);
                // }
                $main_chart[$answers[$key]] = 0;
            }

            $related_question_type = false;
            $converted_rows = [];

            if($scale=='dependency') {

                $total = VoxAnswer::prepareQuery($question_id, $dates);
                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

                if($dates || $question->respondent_count() < 50) {

                    $results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
                    $results = $results->get();
                } else {
                    $results = VoxAnswersDependency::where('question_id', $question_id)->where('question_dependency_id', $question->stats_relation_id)->where('answer_id', $answer_id)->get();

                    if($results->isEmpty()) {
                        $results = VoxAnswer::prepareQuery($question_id, null,[
                            'dependency_answer' => $answer_id,
                            'dependency_question' => $question->stats_relation_id,
                        ]);

                        $results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
                        $results = $results->get();
                    }
                }

                foreach ($answers as $key => $value) {
                    $second_chart_array[$value] = 0;
                }

                foreach ($results as $res) {

                    $answer_number = $this->getAnswerNumber($question->type, $answers, $res->$answerField);

                    if(!isset( $answer_number )) {
                        continue;
                    }
                    $second_chart_array[ $answer_number ] = $res->cnt;
                }

                $reorder = $this->reorderStats($second_chart_array, $question);
                foreach ($second_chart_array as $key => $value) {
                    $second_chart[ $question->removeAnswerTooltip($key) ] = $value;
                }

                $relation_info['answer'] = !empty($answer_id) ? $answer_id-1 : -1;
                $relation_info['question'] = $question->related->questionWithTooltips();
                $relation_info['current_question'] = $question->questionWithTooltips();

                if ($question->related->type == 'multiple_choice') {
                    $related_question_type = 'multiple';
                } else {
                    $related_question_type = 'single';
                }

                $answers_related = json_decode($question->related->answers);
                foreach ($answers_related as $key => $value) {
                    if(mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0) {
                        $answers_related[$key] = $question->removeAnswerTooltip(mb_substr($value, 1));
                    } else {
                        $answers_related[$key] = $question->removeAnswerTooltip($value);
                    }                    
                }

                $main_chart = [];
                foreach ($answers_related as $key => $value) {
                    if(mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0) {
                        $answers_related[$key] = mb_substr($value, 1);
                    }
                    $main_chart[$answers_related[$key]] = 0;
                }
                $results = VoxAnswer::prepareQuery($question->stats_relation_id, $dates);
                $results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
                $results = $results->get();
                foreach ($results as $res) {
                    $main_chart[ $answers_related[ $res->$answerField-1 ] ] = $res->cnt;
                }


                $styles = new \stdClass();
                $styles->role = 'style';
                if($question->type == 'multiple_choice') {
                    $sum = $total;
                } else {
                    $sum = 0;
                    foreach ($second_chart as $key => $value) {
                        $sum+=$value;
                    }
                }
                
                //reorder answers by respondents desc if they're not from scale!!
                if($reorder) {

                    foreach ($second_chart as $key => $value) {
                        if(mb_strpos($key, '#')!==0) {
                            $resp = $value ? $value/$sum : 0;

                            $converted_rows[] = [
                                $key,
                                $resp,
                            ];
                        }
                    }

                    $keys = array_map(function($val) { return $val[1]; }, $converted_rows);
                    array_multisort($keys, SORT_DESC, $converted_rows);

                    $rows_diez = [];
                    foreach ($second_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            $resp = $value ? $value/$sum : 0;

                            $rows_diez[] = [
                                $key,
                                $resp,
                            ];
                        }
                    }

                    if(!empty($rows_diez)) {

                        $keys = array_map(function($val) { return $val[1]; }, $rows_diez);
                        array_multisort($keys, SORT_DESC, $rows_diez);

                        $rows_without_diez = [];
                        foreach ($rows_diez as $key => $value) {
                            $new_key = mb_substr($value[0], 1);
                            $new_value = $value[1];
                            $rows_without_diez[] = [
                                $new_key,
                                $new_value
                            ];
                        }
                    }
                } else {
                    foreach ($second_chart as $key => $value) {
                        $resp = $value ? $value/$sum : 0;

                        $converted_rows[] = [
                            mb_strpos($key, '#')===0 ? mb_substr($key, 1) : $key,
                            $resp,
                        ];
                    }
                }


                //answers with ! at the bottom of array
                $has_disabler = false;
                foreach ($converted_rows as $key => $value) {

                    if(mb_strpos($value[0], '!')===0) {
                        $has_disabler = true;
                        unset($converted_rows[$key]);
                        $converted_rows[] = $value;
                    }
                }

                if($has_disabler) {

                    $converted_rows = array_values($converted_rows);

                    foreach ($converted_rows as $key => $value) {
                        if(mb_strpos($value[0], '!')===0) {
                            $converted_rows[$key] = [
                                mb_substr($value[0], 1),
                                $value[1]
                            ];
                        }
                    }
                }

                array_unshift($converted_rows , [
                    'Answer',
                    'Respondents',
                    $styles,
                ]);

            } else if($scale=='gender') {

                $total = VoxAnswer::prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id
                ])->whereNotNull('gender')->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

                $results_main_chart = $results->whereNotNull('gender');
                if($question->type == 'rank') {
                    $results_main_chart = $results_main_chart->groupBy($answerField, 'scale', 'gender')->selectRaw($answerField.', 
                        scale, 
                        gender, 
                        SUM(`scale`) AS `sbor`, 
                        COUNT(*) as cnt, 
                        ( '.(count($answers)+1).' * COUNT(*) - SUM(`scale`) ) / COUNT(*) AS `weight`
                    ')->get();

                    //SUM( '.(count($answers)+1).' - `scale`) / COUNT(*) AS `weight`
                } else {
                    $results_main_chart = $results_main_chart->groupBy($answerField, 'gender')->selectRaw($answerField.', gender, COUNT(*) as cnt')->get();
                }

                if( $scale_options ) {
                    $results = $results->whereIn($scale, array_values($scale_options));
                }
                $results = $results->whereNotNull('gender');

                if($question->type == 'rank') {
                    $results = $results->groupBy($answerField, 'scale', 'gender')->selectRaw($answerField.', 
                        scale, 
                        gender, 
                        SUM(`scale`) AS `sbor`, 
                        COUNT(*) as cnt, 
                        ( '.(count($answers)+1).' * COUNT(*) - SUM(`scale`) ) / COUNT(*) AS `weight`
                    ')->get();
                } else {
                    $results = $results->groupBy($answerField, 'gender')->selectRaw($answerField.', gender, COUNT(*) as cnt')->get();
                }

                foreach ($answers as $key => $value) {
                    $second_chart[$value] = 0;
                    $third_chart[$value] = 0;
                }

                foreach ($results_main_chart as $res_main) {
                    $answer_number = $this->getAnswerNumber($question->type, $answers, $res_main->$answerField);
                    if(empty( $answer_number )) {
                        continue;
                    }

                    if(!isset($main_chart[ $answer_number ])) {
                        $main_chart[ $answer_number ] = 0;
                    }

                    if($question->type == 'rank') {
                        $main_chart[ $answer_number ] += $res_main->weight;
                    } else {
                        $main_chart[ $answer_number ] += $res_main->cnt;
                    }
                }

                foreach ($results as $res) {

                    if($question->type == 'number') {
                        foreach ($answers as $key => $value) {
                            $value_string = $value;
                            if (strpos( $value, '-') !== FALSE) {
                                //from 0-100,0-1000
                                $last_num = intval(explode('-', $value)[1]);

                                if($res->$answerField <= $last_num) {
                                    $answer_number = $value_string;
                                    break;
                                }
                            } else {
                                //from 0-10
                                $answer_number = $res->$answerField;
                                break;
                            }
                        }
                    } else {
                        if(isset($answers[ $res->$answerField-1])) {
                            $answer_number = $answers[ $res->$answerField-1];
                        }
                    }

                    if(!isset( $answer_number )) {
                        continue;
                    }

                    if(!isset($second_chart[ $answer_number ])) {
                        //$main_chart[ $answers[ $res->$answerField-1 ] ] = 0;
                        $second_chart[ $answer_number ] = 0; //m
                        $third_chart[ $answer_number ] = 0; //f
                    }
                    //$main_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt;
                    if($res->gender=='f') {
                        if($question->type == 'rank') {
                            $second_chart[ $answer_number ] += $res->weight; //m
                        } else {
                            $second_chart[ $answer_number ] += $res->cnt; //m
                        }                       
                    }
                    if($res->gender=='m') {
                        if($question->type == 'rank') {
                            $third_chart[ $answer_number ] += $res->weight; //f
                        } else {
                            $third_chart[ $answer_number ] += $res->cnt; //f
                        }
                    }

                    $totalm = $totalf = 0;
                    $totalQuery = VoxAnswer::prepareQuery($question_id, $dates, [
                        'scale_answer_id' => $scale_answer_id, 
                        'scale' => $scale, 
                        'scale_options' => $scale_options
                    ])->whereNotNull('gender')->groupBy('gender')->select(DB::raw('gender, count(distinct `user_id`) as num'))->get();
                    foreach ($totalQuery->toArray() as $garr) {
                        if($garr['gender']=='m') {
                            $totalm = $garr['num'];
                        } else if($garr['gender']=='f') {
                            $totalf = $garr['num'];
                        }
                    }
                }

                $reorder = $this->reorderStats($main_chart, $question);
                //reorder answers by respondents desc if they're not from scale!!
                if($reorder) {
                    $new_main_chart = [];
                    foreach ($main_chart as $k => $v) {
                        if(mb_strpos($k, '#')!==0) {
                            $new_main_chart[$k] = $v;
                        }
                    }
                    arsort($new_main_chart);

                    $rows_diez = [];
                    foreach ($main_chart as $k => $v) {
                        if(mb_strpos($k, '#')===0) {
                            $rows_diez[$k] = $v;
                        }
                    }

                    if(!empty($rows_diez)) {
                        arsort($rows_diez);

                        $rows_without_diez = [];
                        foreach ($rows_diez as $k => $v) {
                            $new_key = mb_substr($k, 1);
                            $new_value_second = $v;
                            $rows_without_diez[$new_key] = $new_value_second;
                        }

                        $new_main_chart = array_merge($new_main_chart, $rows_without_diez);
                    }

                    $main_chart = $new_main_chart;

                    foreach ($second_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            unset($second_chart[$key]);
                            $second_chart[mb_substr($key, 1)] = $value;
                        }
                    }

                    foreach ($third_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            unset($third_chart[$key]);
                            $third_chart[mb_substr($key, 1)] = $value;
                        }
                    }

                    $new_second_chart=[];
                    foreach ($main_chart as $key => $value) {
                        $new_second_chart[$key] = $second_chart[$key];
                    }

                    $new_third_chart=[];
                    foreach ($main_chart as $key => $value) {
                        $new_third_chart[$key] = $third_chart[$key];
                    }

                    $second_chart = $new_second_chart;
                    $third_chart = $new_third_chart;
                } else {

                    foreach ($main_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            unset($main_chart[$key]);
                            $main_chart[mb_substr($key, 1)] = $value;
                        }
                    }

                    foreach ($second_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            unset($second_chart[$key]);
                            $second_chart[mb_substr($key, 1)] = $value;
                        }
                    }

                    foreach ($third_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            unset($third_chart[$key]);
                            $third_chart[mb_substr($key, 1)] = $value;
                        }
                    }
                }

                if(!empty($question->stats_top_answers) && $question->type == 'multiple_choice') {
                    $multiple_top_ans = intval(explode('_', $question->stats_top_answers)[1]);

                    $i=0;
                    foreach ($main_chart as $key => $value) {
                        $i++;
                        if($i > $multiple_top_ans) {
                            unset($main_chart[$key]);
                        }
                    }
                    $i=0;
                    foreach ($second_chart as $key => $value) {
                        $i++;
                        if($i > $multiple_top_ans) {
                            unset($second_chart[$key]);
                        }
                    }
                    $i=0;
                    foreach ($third_chart as $key => $value) {
                        $i++;
                        if($i > $multiple_top_ans) {
                            unset($third_chart[$key]);
                        }
                    }
                }

                //answers with ! at the bottom of array
                foreach ($main_chart as $key => $value) {

                    if(mb_strpos($key, '!')===0) {
                        unset($main_chart[$key]);
                        $main_chart[mb_substr($key, 1)] = $value;
                    }
                }

                foreach ($second_chart as $key => $value) {

                    if(mb_strpos($key, '!')===0) {
                        unset($second_chart[$key]);
                        $second_chart[mb_substr($key, 1)] = $value;
                    }
                }

                foreach ($third_chart as $key => $value) {

                    if(mb_strpos($key, '!')===0) {
                        unset($third_chart[$key]);
                        $third_chart[mb_substr($key, 1)] = $value;
                    }
                }                

            } else if($scale=='country_id') {
                $countries = Country::with('translations')->get()->keyBy('id');
                $total = VoxAnswer::prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id
                ]);
                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

                if($question->type == 'rank') {
                    $results = $results->groupBy($answerField, 'scale', 'country_id')->selectRaw($answerField.', 
                        scale, 
                        country_id, 
                        SUM(`scale`) AS `sbor`, 
                        COUNT(*) as cnt, 
                        ( '.(count($answers)+1).' * COUNT(*) - SUM(`scale`) ) / COUNT(*) AS `weight`
                    ')->get();
                } else {
                    $results = $results->groupBy($answerField, 'country_id')->selectRaw($answerField.', country_id, COUNT(*) as cnt')->get();
                }

                $country_resp_count = [];
                $second_chart_before = [];
                foreach ($results as $res) {

                    $answer_number = $this->getAnswerNumber($question->type, $answers, $res->$answerField);

                    if(!isset( $answer_number )) {
                        continue;
                    }

                    if($answer_number !== '') {

                        if(!isset($main_chart[ $answer_number ])) {
                            $main_chart[ $answer_number ] = 0;
                        }
                        if($question->type == 'rank') {
                            $main_chart[ $answer_number ] += $res->weight;
                        } else {
                            $main_chart[ $answer_number ] += $res->cnt;
                        }

                        if( $res->country_id ) {
                            $country = $countries->get($res->country_id);
                            $country->code = mb_strtoupper($country->code);
                            if(!isset($second_chart_before[ $country->code ] )) {
                                $second_chart_before[ $country->code ] = [
                                    'name' => $country->name
                                ];
                                foreach ($answers as $a) {
                                    $second_chart_before[ $country->code ][$a] = 0;
                                }
                            }
                            if(empty($answer_id) || $res->$answerField==$answer_id) {
                                if($question->type == 'rank') {
                                    $second_chart_before[ $country->code ][ $answer_number ] = intval($res->weight);
                                    $country_resp_count[ $country->code ][ $answer_number ] = $res->cnt;
                                } else {
                                    $second_chart_before[ $country->code ][ $answer_number ] = $res->cnt;
                                }
                            }
                        }
                    }
                }

                $reorder = $this->reorderStats($main_chart, $question);

                //reorder answers by respondents desc if they're not from scale!!
                if($reorder) {

                    arsort($main_chart);
                    $sum = 0;
                    foreach ($main_chart as $key => $value) {
                        $sum+=$value;
                    }

                    foreach ($main_chart as $key => $value) {
                        if(mb_strpos($key, '#')!==0) {
                            $resp = $value ? $value/$sum : 0;

                            $converted_rows[] = [
                                $key,
                                $resp,
                            ];
                        }
                    }

                    $keys = array_map(function($val) { return $val[1]; }, $converted_rows);
                    array_multisort($keys, SORT_DESC, $converted_rows);

                    $rows_diez = [];
                    foreach ($main_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            $resp = $value ? $value/$sum : 0;

                            $rows_diez[] = [
                                $key,
                                $resp,
                            ];
                        }
                    }

                    if(!empty($rows_diez)) {

                        $keys = array_map(function($val) { return $val[1]; }, $rows_diez);
                        array_multisort($keys, SORT_DESC, $rows_diez);

                        $rows_without_diez = [];
                        foreach ($rows_diez as $key => $value) {
                            $new_key = mb_substr($value[0], 1);
                            $new_value = $value[1];
                            $rows_without_diez[] = [
                                $new_key,
                                $new_value
                            ];
                        }

                        $converted_rows = array_merge($converted_rows, $rows_without_diez);
                    }

                    //reorder and configure second chart & main chart
                    foreach ($second_chart_before as $key => $value) {
                        $old_value = $value;
                        $new_value = array_slice($value, 1);

                        $new_ordered_array = [];
                        foreach ($new_value as $k => $v) {
                            if(mb_strpos($k, '#')!==0) {
                                $new_ordered_array[$k] = $v;
                            }
                        }
                        arsort($new_ordered_array);

                        $rows_diez = [];
                        foreach ($new_value as $k => $v) {
                            if(mb_strpos($k, '#')===0) {
                                $rows_diez[$k] = $v;
                            }
                        }

                        if(!empty($rows_diez)) {
                            arsort($rows_diez);

                            $rows_without_diez = [];
                            foreach ($rows_diez as $k => $v) {
                                $new_key = mb_substr($k, 1);
                                $new_value_second = $v;
                                $rows_without_diez[$new_key] = $new_value_second;
                            }

                            $new_ordered_array = array_merge($new_ordered_array, $rows_without_diez);
                        }

                        $last_order_arr = [];
                        foreach ($converted_rows as $k => $value) {
                            $last_order_arr[$value[0]] = $new_ordered_array[$value[0]];
                        }

                        $last_order_arr=array("name"=>$old_value['name']) + $last_order_arr;
                        $second_chart[$key] = $last_order_arr;

                        if($question->type == 'rank') {
                            
                        }
                    }

                } else {
                    $sum = 0;
                    foreach ($main_chart as $key => $value) {
                        $sum+=$value;
                    }

                    foreach ($main_chart as $key => $value) {
                        $resp = $value ? $value/$sum : 0;

                        $converted_rows[] = [
                            $key,
                            $resp,
                        ];
                    }

                    foreach ($second_chart_before as $key => $value) {
                        $old_value = $value;
                        $new_value = array_slice($value, 1);

                        $new_ordered_array = [];
                        foreach ($new_value as $k => $v) {
                            $new_ordered_array[$k] = $v;
                        }

                        $last_order_arr = [];
                        foreach ($converted_rows as $k => $value) {
                            $last_order_arr[$value[0]] = $new_ordered_array[$value[0]];
                        }

                        $last_order_arr=array("name"=>$old_value['name']) + $last_order_arr;
                        $second_chart[$key] = $last_order_arr;
                    }

                    foreach ($converted_rows as $key => $value) {
                        if(mb_strpos($value[0], '#')===0) {
                            $converted_rows[$key] = [
                                mb_substr($value[0], 1),
                                $value[1]
                            ];
                        }
                    }

                    foreach ($second_chart as $key => $value) {
                        if(is_array($value)) {

                            foreach($value as $k => $v) {

                                if(mb_strpos($k, '#')===0) {
                                    unset($value[$k]);
                                    $value[mb_substr($k, 1)] = $v;

                                }
                            }
                            $second_chart[$key] = $value;
                        }
                    }

                }

                if($question->type == 'rank') {
                    $array_foreach = $country_resp_count;
                } else {
                    $array_foreach = $second_chart;
                }

                $max_resp_from_country = 0;
                foreach ($array_foreach as $key => $value) {
                    $max_resp = 0;
                    $total_answr_count = 0;

                    foreach ($value as $k => $v) {
                        if(is_numeric($v)) {
                            $total_answr_count+= $v;
                            if($max_resp <= $v) {
                                $max_resp = $v;
                            }
                        }

                        if(mb_strpos($k, '!')===0) {
                            unset($value[$k]);
                            $value[mb_substr($k, 1)] = $v;

                            $second_chart[$key] = $value;

                        }
                    }

                    if($max_resp_from_country < $max_resp) {
                        $max_resp_from_country = $max_resp;
                    }

                    $second_chart[$key]['all_count'] = $question->type != 'rank' ? $total_answr_count : 0;
                    $second_chart[$key]['count'] = $max_resp;
                }
                $second_chart['max_resp_from_country'] = $max_resp_from_country;

                if($question->type == 'rank') {
                    foreach ($second_chart as $key => $value) {
                        $total_answr_count = 0;

                        if(is_array($value)) {

                            foreach ($value as $k => $v) {
                                if(is_numeric($v) && $k != 'count' ) {
                                    $total_answr_count+= $v;
                                }
                            }

                            $second_chart[$key]['all_count'] = $total_answr_count;
                        }
                    }
                }

                if($question->type == 'rank') {
                    foreach ($converted_rows as $key => $value) {
                        $converted_rows[$key] = [
                            $value[0],
                            $main_chart[$value[0]],
                        ];
                    }
                }

                //dd($second_chart, $converted_rows);
                if(!empty($question->stats_top_answers) && $question->type == 'multiple_choice') {
                    $multiple_top_ans = intval(explode('_', $question->stats_top_answers)[1]);

                    foreach ($converted_rows as $key => $value) {
                        if($key+1 > $multiple_top_ans) {
                            unset($converted_rows[$key]);
                        }
                    }
                    foreach ($second_chart as $key => $value) {
                        $i=0;

                        if(is_array($value)) {

                            foreach ($value as $k => $v) {
                                $i++;
                                if($i > $multiple_top_ans + 1) {
                                    if($k != 'all_count' && $k != 'count') {

                                        unset($value[$k]);
                                    }
                                }
                            }
                            $second_chart[$key] = $value;
                        }
                    }
                }


                //answers with ! at the bottom of array
                $has_disabler = false;
                foreach ($converted_rows as $key => $value) {

                    if(mb_strpos($value[0], '!')===0) {
                        $has_disabler = true;
                        unset($converted_rows[$key]);
                        $converted_rows[] = $value;
                    }
                }

                if($has_disabler) {

                    $converted_rows = array_values($converted_rows);

                    foreach ($converted_rows as $key => $value) {
                        if(mb_strpos($value[0], '!')===0) {
                            $converted_rows[$key] = [
                                mb_substr($value[0], 1),
                                $value[1]
                            ];
                        }
                    }
                }

                array_unshift($converted_rows , [
                    '',
                    '',
                ]);

            } else if($scale=='age') {

                $total = VoxAnswer::prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id, 
                    'scale' => $scale, 
                    'scale_options' => $scale_options
                ]);

                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->whereNotNull('age')->first()->num;
                if( $scale_options ) {
                    $results = $results->whereIn($scale, array_values($scale_options));
                }
                if($question->type == 'rank') {
                    $results = $results->groupBy($answerField, 'scale', 'age')->selectRaw($answerField.', 
                        scale, 
                        age, 
                        SUM(`scale`) AS `sbor`, 
                        COUNT(*) as cnt, 
                        ( '.(count($answers)+1).' * COUNT(*) - SUM(`scale`) ) / COUNT(*) AS `weight`
                    ')->get();
                } else {
                    $results = $results->groupBy($answerField, 'age')->selectRaw($answerField.', age, COUNT(*) as cnt')->get();
                }

                $age_to_group = config('vox.age_groups');

                if (!empty($scale_options)) {
                    foreach ($scale_options as $sv) {
                        $second_chart_before[ config('vox.age_groups.'.$sv) ] = [];
                        foreach ($answers as $a) {
                            $second_chart_before[ config('vox.age_groups.'.$sv) ][$a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
                    }
                } else {
                    foreach ($age_to_group as $k => $v) {
                        $second_chart_before[ $v ] = [];
                        foreach ($answers as $a) {
                            $second_chart_before[ $v ][$a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
                    }
                }

                foreach ($results as $res) {

                    $answer_number = $this->getAnswerNumber($question->type, $answers, $res->$answerField);

                    if(!isset( $answer_number )) {
                        continue;
                    }
                    if($answer_number !== '') {
                        if(!isset($main_chart[ $answer_number ])) {
                            $main_chart[ $answer_number ] = 0;
                        }
                        if($question->type == 'rank') {
                            $main_chart[ $answer_number ] += $res->weight;
                        } else {
                            $main_chart[ $answer_number ] += $res->cnt;
                        }

                        if( $res->age ) {
                            if($question->type == 'rank') {
                                $second_chart_before[ $age_to_group[$res->age] ][ $answer_number ] = $res->weight; //m
                            } else {
                                $second_chart_before[ $age_to_group[$res->age] ][ $answer_number ] = $res->cnt; //m
                            }
                        }
                    }
                }
                
            } else {

                $total = VoxAnswer::prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id, 
                    'scale' => $scale, 
                    'scale_options' => $scale_options
                ]);
                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->whereNotNull($scale)->first()->num;
                if( $scale_options ) {
                    $results = $results->whereIn($scale, array_values($scale_options));
                }
                
                if($question->type == 'rank') {
                    $results = $results->groupBy($answerField, 'scale', $scale)->selectRaw($answerField.', 
                        scale, 
                        '.$scale.', 
                        SUM(`scale`) AS `sbor`, 
                        COUNT(*) as cnt, 
                        ( '.(count($answers)+1).' * COUNT(*) - SUM(`scale`) ) / COUNT(*) AS `weight`
                    ')->get();
                } else {
                    $results = $results->groupBy($answerField, $scale)->selectRaw($answerField.', '.$scale.', COUNT(*) as cnt')->get();
                }

                $age_to_group = config('vox.details_fields.'.$scale.'.values');
                if (!empty($scale_options)) {
                    foreach ($scale_options as $sv) {
                        $second_chart_before[ config('vox.details_fields.'.$scale.'.values.'.$sv) ] = [];
                        foreach ($answers as $a) {
                            $second_chart_before[ config('vox.details_fields.'.$scale.'.values.'.$sv) ][ $a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
                    }
                } else {
                    
                    foreach ($age_to_group as $k => $v) {
                        $second_chart_before[ $v ] = [];
                        foreach ($answers as $a) {
                            $second_chart_before[ $v ][$a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
                    }
                }

                foreach ($results as $res) {

                    $answer_number = $this->getAnswerNumber($question->type, $answers, $res->$answerField);
                    if($res->$scale===null || !isset( $answer_number )) {
                        continue;
                    }

                    if($answer_number !== '') {

                        if(!isset($main_chart[ $answer_number ])) {
                            $main_chart[ $answer_number ] = 0;
                        }
                        if($question->type == 'rank') {
                            $main_chart[ $answer_number ] += $res->weight;
                        } else {
                            $main_chart[ $answer_number ] += $res->cnt;
                        }

                        if( $res->$scale ) {
                            if($question->type == 'rank') {
                                $second_chart_before[ $age_to_group[$res->$scale] ][ $answer_number ] = $res->weight; //m
                            } else {
                                $second_chart_before[ $age_to_group[$res->$scale] ][ $answer_number ] = $res->cnt; //m
                            }
                        }
                    }
                }
            }

            if($scale != 'dependency' && $scale != 'country_id' && $scale != 'gender') {
                
                $reorder = $this->reorderStats($main_chart, $question);
                //reorder answers by respondents desc if they're not from scale!!
                if($reorder) {
                    $new_main_chart = [];
                    foreach ($main_chart as $k => $v) {
                        if(mb_strpos($k, '#')!==0) {
                            $new_main_chart[$k] = $v;
                        }
                    }
                    arsort($new_main_chart);

                    $rows_diez = [];
                    foreach ($main_chart as $k => $v) {
                        if(mb_strpos($k, '#')===0) {
                            $rows_diez[$k] = $v;
                        }
                    }

                    if(!empty($rows_diez)) {
                        arsort($rows_diez);

                        $rows_without_diez = [];
                        foreach ($rows_diez as $k => $v) {
                            $new_key = mb_substr($k, 1);
                            $new_value_second = $v;
                            $rows_without_diez[$new_key] = $new_value_second;
                        }

                        $new_main_chart = array_merge($new_main_chart, $rows_without_diez);
                    }

                    $main_chart = $new_main_chart;
                } else {

                    foreach ($main_chart as $key => $value) {
                        if(mb_strpos($key, '#')===0) {
                            unset($main_chart[$key]);
                            $main_chart[mb_substr($key, 1)] = $value;
                        }
                    }
                }

                //remove diezes
                foreach ($second_chart_before as $key => $value) {
                    foreach ($value as $k => $v) {
                        if(mb_strpos($k, '#')===0) {
                            unset($value[$k]);
                            $value[mb_substr($k, 1)] = $v;
                            $second_chart_before[$key] = $value;
                        }
                    }
                }

                $second_chart_after = [];
                foreach ($second_chart_before as $key => $value) {

                    $last_order_arr = [];
                    foreach ($main_chart as $k => $v) {
                        $last_order_arr[$k] = $value[$k];
                    }

                    $second_chart_after[$key] = $last_order_arr;
                }

                //in percentage
                $second_chart[] = [$scale_name];
                foreach ($main_chart as $key => $value) {
                    $second_chart[0][] = $key;
                }
                
                foreach ($second_chart_after as $key => $value) {

                    $sum = 0;
                    foreach ($value as $k => $v) {
                        $sum+=$v;
                    }

                    $array = [];
                    $array[] = $key;

                    foreach ($value as $k => $v) {
                        $array[] = $v ? $v/$sum : 0;
                    }

                    $second_chart[] = $array;
                }

                if(!empty($question->stats_top_answers) && $question->type == 'multiple_choice') {
                    $multiple_top_ans = intval(explode('_', $question->stats_top_answers)[1]);

                    $i=0;
                    foreach ($main_chart as $key => $value) {
                        $i++;
                        if($i > $multiple_top_ans) {
                            unset($main_chart[$key]);
                        }
                    }
                    foreach ($second_chart as $key => $value) {
                        foreach ($value as $k => $v) {
                            if($k > $multiple_top_ans) {
                                unset($value[$k]);
                            }
                        }
                        $value = array_values($value);
                        $second_chart[$key] = $value;
                    }
                }

                if(!empty($answer_id)) {
                    foreach ($second_chart as $key => $value) {
                        foreach ($value as $k => $v) {
                            if($k!=$answer_id & $k!=0) {
                                unset($value[$k]);
                            }
                        }
                        $value = array_values($value);
                        $second_chart[$key] = $value;
                    }
                }


                //answers with ! at the bottom of array
                $disabler_position = -1;
                foreach ($main_chart as $key => $value) {

                    if(mb_strpos($key, '!')===0) {
                        $disabler_position = array_search($key, array_keys($main_chart));
                        unset($main_chart[$key]);
                        $main_chart[mb_substr($key, 1)] = $value;
                    }
                }

                // dd($second_chart);
                if($disabler_position > -1) {

                    foreach ($second_chart as $key => $value) {
                        $value[] = is_string($value[$disabler_position + 1]) ? mb_substr($value[$disabler_position + 1], 1) : $value[$disabler_position + 1];
                        unset($value[$disabler_position + 1]);
                        $value = array_values($value);
                        $second_chart[$key] = $value;
                    }
                }

            } else {
                $second_chart = $this->processArray($second_chart);
            }

            $main_chart = $this->processArray($main_chart);
            $third_chart = $this->processArray($third_chart);


            if(!empty($this->admin) && $total == 0 ) {
                $c = 0;

                foreach ($main_chart as $key => $value) {
                    $c++;

                    $main_chart[$key] = [
                        $value[0],
                        1,
                    ];
                }

                if($scale=='dependency' || $scale=='gender' || $scale=='country_id') {
                    //dd($second_chart);
                    foreach ($second_chart as $key => $value) {
                        $second_chart[$key] = [
                            $value[0],
                            1,
                        ];
                    }
                }

                if(!empty($third_chart)) {

                    foreach ($third_chart as $key => $value) {
                        $third_chart[$key] = [
                            $value[0],
                            1,
                        ];
                    }
                }

                $total = $c;
            }

            
            return Response::json( [
                'related_question_type' => $related_question_type,
                'converted_rows' => $converted_rows,
                'main_chart' => $main_chart,
                'second_chart' => $second_chart,
                'third_chart' => $third_chart,
                'total' => $total,
                'totalm' => $totalm,
                'totalf' => $totalf,
                'relation_info' => $relation_info,
                'answer_id' => $answer_id,
                'vox_scale_id' => !empty($question->vox_scale_id) || !empty($question->dont_randomize_answers) ? true : false,
                'question_type' => $question->type,
                'multiple_top_answers' => !empty($question->stats_top_answers) && $question->type == 'multiple_choice' ? intval(explode('_', $question->stats_top_answers)[1]) : null,
                'q_id' => $question->id,
                'available_dep_answer' => $question->stats_answer_id ? $question->stats_answer_id : null,
            ] );
        }

        $vox->respondentsCountryCount();

        $respondents_count = $vox->respondentsCount();
        $respondents_country_count = $vox->respondentsCountryCount();

        $launched = strtotime($vox->launched_at);
        $launched_date = date('Y-m-d',$launched);

        $seos = PageSeo::find(12);
        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
        $seo_description = str_replace(':respondents_country', $respondents_country_count, $seo_description);
        $seo_description = str_replace(':respondents', $respondents_count, $seo_description);
        $social_title = str_replace(':title', $vox->title, $seos->social_title);
        $social_description = str_replace(':description', $vox->description, $seos->social_description);

        if(!empty(Request::input('create-stat-pdf'))) {
            if(!empty($this->user) && !empty(Request::input('format')) && !empty(Request::input('stats-for'))) {
                $q = VoxQuestion::find(Request::input('stats-for'));

                if(!empty($q->used_for_stats)) {

                    $format = Request::input('format');
                    $demographics = explode(',', Request::input('demographics'));
                    $dem_options = [];

                    $dem_not_null = [];

                    foreach ($demographics as $demographic) {
                        if($demographic != 'gender' && $demographic != 'country_id' && (!empty(Request::input('dem-'.$demographic)) || Request::input('dem-'.$demographic) == 0)) {
                            $dem_options[$demographic] = explode(',', Request::input('dem-'.$demographic));
                        }

                        if($demographic == 'gender' || $demographic == 'country_id') {
                            $dem_not_null[] = $demographic;
                        }
                    }

                    if (Request::input('download-date') == 'all') {
                        $results =  VoxAnswer::whereNull('is_admin')
                        ->where('question_id', Request::input('stats-for'))
                        ->where('is_completed', 1)
                        ->where('is_skipped', 0)
                        ->has('user');

                        $all_period = date('d/m/Y',strtotime($vox->launched_at)).'-'.date('d/m/Y');
                    } else {
                        $from = Carbon::parse(explode('-', Request::input('download-date'))[0]);
                        $to = Carbon::parse(explode('-', Request::input('download-date'))[1]);

                        $results = VoxAnswer::whereNull('is_admin')
                        ->where('question_id', Request::input('stats-for'))
                        ->where('is_completed', 1)
                        ->where('is_skipped', 0)
                        ->has('user')
                        ->where('created_at', '>=', $from)
                        ->where('created_at', '<=', $to);

                        $all_period = date('d/m/Y', $from->timestamp).'-'.date('d/m/Y', $to->timestamp);
                    }

                    if(!empty($dem_not_null) && $format != 'xlsx') {
                        foreach ($dem_not_null as $dnn) {
                            //echo implode(',',$value).'<br/>';
                            $results = $results->whereNotNull($dnn);
                        }
                    } else {
                        if(!empty($dem_options)) {

                            $do = $dem_options;
                            $demographic_options = [];

                            foreach ($do as $k => $dm) {
                                if( in_array('all', $dm)) {
                                    $removed_key = array_search('all', $dm);
                                    unset($dm[$removed_key]);
                                }

                                $demographic_options[$k] = $dm;
                            }

                            foreach ($demographic_options as $key => $value) {
                                //echo implode(',',$value).'<br/>';
                                if($key != 'relation') {

                                    $results = $results->whereIn($key, array_values($value));
                                }
                            }
                        }
                    }

                    $resp = $results->count();

                    if($format == 'xlsx') {

                        ini_set('max_execution_time', 0);
                        set_time_limit(0);
                        ini_set('memory_limit','1024M');

                        $cols = ['Survey Date'];
                        $cols2 = [''];

                        foreach ($demographics as $dem) {
                            if($dem != 'relation') {
                                $cols[] = config('vox.stats_scales')[$dem];
                                $cols2[] = '';
                            }
                        }

                        if(in_array('relation', $demographics)) {
                            if(!empty($q->stats_answer_id)) {

                                $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                                $cols[] = $q->related->question.' ['.$q->removeAnswerTooltip($list[$q->stats_answer_id - 1]).']';
                                $cols2[] = '';
                            } else {
                                if($q->related->type == 'multiple_choice') {
                                    $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);
                                    foreach ($list as $l) {
                                        $cols[] = $q->related->question;
                                        $cols2[] = mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l;
                                    }
                                } else {
                                    $cols[] = $q->related->question;
                                    $cols2[] = '';
                                }
                            }
                        }

                        $slist = VoxScale::get();
                        $scales = [];
                        foreach ($slist as $sitem) {
                            $scales[$sitem->id] = $sitem;
                        }

                        if( $q->type == 'single_choice' || $q->type == 'number') {
                            $cols[] = in_array('relation', $demographics) ? $q->questionWithTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                            $cols2[] = '';

                        } else if( $q->type == 'scale' ) {
                            $list = json_decode($q->answers, true);
                            $cols[] = $q->stats_title.' ['.$list[(Request::input('scale-for') - 1)].']';
                            $cols2[] = '';

                        } else if( $q->type == 'rank' ) {
                            $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);
                            foreach ($list as $l) {
                                $cols[] = in_array('relation', $demographics) ? $q->questionWithTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                                $cols2[] = $q->removeAnswerTooltip(mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l);
                            }

                        } else if( $q->type == 'multiple_choice' ) {
                            $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

                            $list_done = [];
                            foreach ($list as $k => $elm) {
                                if(mb_strpos($elm, '!')===0 || mb_strpos($elm, '#')===0) {
                                    $list_done[$k] = mb_substr($elm, 1);
                                } else {
                                    $list_done[$k] = $elm;
                                }
                            }

                            foreach ($list_done as $l) {
                                $cols[] = in_array('relation', $demographics) ? $q->questionWithTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                                $cols2[] = $q->removeAnswerTooltip(mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l);
                            }
                        }

                        if($q->type == 'scale') {
                            $results_resp = $results->where('answer', Request::input('scale-for'))->count();
                        } else {
                            $results_resp = $results->count();
                        }

                        $cols_title = [
                            strtoupper($vox->title).', Base: '.$results_resp.' respondents, '.$all_period
                        ];

                        $rows = [
                            $cols_title,
                            $cols,
                            $cols2
                        ];
                        // dd($dem_options);
                        if($q->type == 'scale') {
                            $all_results = $results->where('answer', Request::input('scale-for'))->get();
                        } else if ($q->type == 'rank') {
                            $all_results = $results->groupBy('user_id')->get();
                        } else {
                            $all_results = $results->get();
                        }

                        foreach ($all_results as $answ) {
                            $row = [];

                            $row[] = $answ->created_at->format('d.m.Y');

                            foreach ($demographics as $dem) {
                                if($dem != 'relation') {

                                    if($dem == 'gender') {

                                        if(!empty($answ->gender)) {
                                            $row[] = $answ->gender=='m' ? 'Male' : 'Female';
                                        } else {
                                            $row[] = '0';
                                        }

                                    } else if($dem == 'age') {
                                        if(!empty($answ->age)) {

                                            $row[] = config('vox.age_groups.'.$answ->age);
                                        } else {
                                            $row[] = '0';
                                        }

                                    } else if($dem == 'country_id') {

                                        if(!empty($answ->country_id)) {
                                            $row[] = $answ->country->name;
                                        } else {
                                            $row[] = '0';
                                        }

                                    } else {
                                        $row[] = !empty($answ->$dem) ? config('vox.details_fields.'.$dem.'.values')[$answ->$dem] : '';
                                    }
                                }
                            }

                            if(in_array('relation', $demographics)) {
                                if(!empty($q->stats_answer_id)) {

                                    $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                                    $row[] = $q->removeAnswerTooltip($list[$q->stats_answer_id - 1]);
                                } else {
                                    if($q->related->type == 'multiple_choice') {
                                        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) : json_decode($q->related->answers, true);
                                        $i=1;
                                        foreach ($list as $l) {
                                            $thisanswer = $i == $answ->answer;
                                            $row[] = $thisanswer ? '1' : '0';
                                            $i++;
                                        }
                                    } else {

                                        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                                        $given_related_answer = VoxAnswer::whereNull('is_admin')->where('user_id', $answ->user_id)->where('question_id', $q->related->id)->first();
                                        $row[] = $given_related_answer ? $q->removeAnswerTooltip($list[$given_related_answer->answer - 1]) : '0';
                                    }
                                }
                            }

                            if( $q->type == 'single_choice' ) {
                                $answerwords = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);

                                if(isset( $answerwords[ ($answ->answer)-1 ] )) {
                                    if(mb_strpos($answerwords[ ($answ->answer)-1 ], '!')===0 || mb_strpos($answerwords[ ($answ->answer)-1 ], '#')===0) {
                                        $row[] = strip_tags($q->removeAnswerTooltip(mb_substr($answerwords[ ($answ->answer)-1 ], 1)));
                                    } else {
                                        $row[] = strip_tags($q->removeAnswerTooltip($answerwords[ ($answ->answer)-1 ]));
                                    }
                                } else {
                                    $row[] = '0';
                                }
                                
                            } else if( $q->type == 'scale' ) {

                                $list = json_decode($q->answers, true);
                                $answerwords = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);
                                $row[] = isset( $answerwords[ ($answ->scale)-1 ] ) ? $answerwords[ ($answ->scale)-1 ] : '0';

                            } else if( $q->type == 'rank' ) {
                                $vox_answers = VoxAnswer::where('user_id', $answ->user_id)->where('question_id', $q->id)->get();
                                foreach ($vox_answers as $va) {
                                    $row[] = $va->scale;
                                }
                                
                            } else if( $q->type == 'multiple_choice' ) {
                                $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);

                                $i=1;
                                foreach ($list as $l) {
                                    $thisanswer = $i == $answ->answer;
                                    $row[] = $thisanswer ? '1' : '0';
                                    $i++;
                                }
                            } else if($q->type == 'number') {
                                $row[] = $answ->answer;
                            }

                            $rows[] = $row;
                        }

                        $flist['Raw Data'] = $rows;



                        ///Breakdown Sheet

                        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

                        $breakdown_rows_count = 0;

                        if($q->type == 'scale') {
                            $results_total = $results->where('answer', Request::input('scale-for'))->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
                        } else {
                            $results_total = $results->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
                        }

                        $total = $results_total; 

                        $cols_title_second = [
                            strtoupper($vox->title).', Base: '.$results_resp.' respondents, '.$all_period
                        ];

                        $rows_breakdown = [
                            $cols_title_second,
                        ];


                        foreach($demographics as $chosen_dem) {

                            if($chosen_dem == 'relation') {

                                $second_chart = [];

                                $answers_related_array = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                                foreach ($answers_related_array as $key => $value) {
                                    $second_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                                }

                                if(!empty($q->stats_answer_id)) {

                                    $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                                    $rows_breakdown[] = [$q->related->question.' ['.$q->removeAnswerTooltip($list[$q->stats_answer_id - 1]).']'];                                

                                    $rows_breakdown[] = ['in relation to:'];

                                    $cols_q_title_second = [
                                        ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).$q->questionWithTooltips()
                                    ];

                                    $rows_breakdown[] = $cols_q_title_second;

                                    $cur_chart = [];

                                    // $all_results = VoxAnswer::whereNull('is_admin')
                                    // ->where('question_id', $q->id)
                                    // ->where('is_completed', 1)
                                    // ->where('is_skipped', 0)
                                    // ->has('user');

                                    // if (!empty(Request::input('download-date')) && Request::input('download-date') != 'all') {
                                    //     $from = Carbon::parse(explode('-', Request::input('download-date'))[0]);
                                    //     $to = Carbon::parse(explode('-', Request::input('download-date'))[1]);

                                    //     $all_results = $all_results->where('created_at', '>=', $from)
                                    //     ->where('created_at', '<=', $to);
                                    // }

                                    $a = $q->stats_answer_id;

                                    // $all_results = $all_results->whereIn('user_id', function($query) use ($q, $a) {
                                    //     $query->select('user_id')
                                    //     ->from('vox_answers')
                                    //     ->where('question_id', $q->related->id)
                                    //     ->where('answer', $a);
                                    // } );

                                    $all_results = VoxAnswersDependency::where('question_dependency_id', $q->related->id)->where('answer', $a)->first();

                                    $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

                                    $breakdown_rows_count = count($answers_array);

                                    foreach ($answers_array as $key => $value) {
                                        $cur_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                                        // $count_people = 0;
                                        // foreach ($all_results->get() as $k => $v) {

                                        //     if($v->answer == ($key + 1)) {
                                        //         $count_people++;
                                        //     }
                                        // }

                                        $cur_chart[$key][] = $count_people;
                                    }
                                    
                                    // $results_total = $all_results->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
                                    $results_total = $all_results->cnt;


                                    $total = $results_total; 

                                    foreach ($cur_chart as $key => $value) {
                                        foreach ($value as $k => $v) {
                                            if($k == 1 && $v == 0) {
                                                $value[$k] = '0';
                                            } else {
                                                $value[$k] =  $v;
                                            }
                                        }
                                        $cur_chart[$key] = $value;

                                        $cur_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total);

                                    }

                                    usort($cur_chart, function($a, $b) {
                                        return $a[2] <= $b[2];
                                    });


                                    $ordered_diez = [];

                                    foreach ($cur_chart as $key => $value) {

                                        if(mb_strpos($value[0], '#')===0) {
                                            $ordered_diez[] = $value;
                                            unset( $cur_chart[$key] );
                                        }
                                    }

                                    if(count($ordered_diez)) {

                                        if( count($ordered_diez) > 1) {
                                            usort($ordered_diez, function($a, $b) {
                                                return $a[2] <= $b[2];
                                            });

                                            foreach ($ordered_diez as $key => $value) {

                                                $value[0] = mb_substr($value[0], 1);

                                                $cur_chart[] = $value;
                                            }
                                        } else {
                                            foreach ($ordered_diez as $key => $value) {

                                                $ordered_diez[$key][0] = mb_substr($value[0], 1);
                                            }
                                            $cur_chart[] = $ordered_diez[0];
                                        }

                                        $cur_chart = array_values($cur_chart);
                                    }

                                    $rows_breakdown[] = $cur_chart;
                                } else {

                                    for($i = 1; $i < count($second_chart); $i++) {

                                        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                                        $rows_breakdown[] = [$q->related->question.' ['.$q->removeAnswerTooltip($list[$i - 1]).']'];

                                        $rows_breakdown[] = ['in relation to:'];

                                        $cols_q_title_second = [
                                            ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).$q->questionWithTooltips()
                                        ];

                                        $rows_breakdown[] = $cols_q_title_second;

                                        $m_original_chart = [];
                                        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

                                        $breakdown_rows_count = count($answers_array);

                                        $all_related_original_results = VoxAnswersDependency::where('question_dependency_id', $q->related->id)->get();
                                        // $all_related_original_results = VoxAnswer::whereNull('is_admin')
                                        // ->where('question_id', $q->id)
                                        // ->where('is_skipped', 0)
                                        // ->has('user')
                                        // ->whereIn('user_id', function($query) use ($q, $i) {
                                        //     $query->select('user_id')
                                        //     ->from('vox_answers')
                                        //     ->where('question_id', $q->related->id)
                                        //     ->where('answer', $i);
                                        // });

                                        // if (!empty(Request::input('download-date')) && Request::input('download-date') != 'all') {
                                        //     $from = Carbon::parse(explode('-', Request::input('download-date'))[0]);
                                        //     $to = Carbon::parse(explode('-', Request::input('download-date'))[1]);

                                        //     $all_related_original_results = $all_related_original_results->where('created_at', '>=', $from)
                                        //     ->where('created_at', '<=', $to);
                                        // }
                                        
                                        foreach ($answers_array as $key => $value) {
                                            $m_original_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                                            // $count_people = 0;
                                            // foreach ($all_related_original_results->get() as $k => $v) {
                                            //     if($v->answer == ($key + 1)) {
                                            //         $count_people++;
                                            //     }
                                            // }

                                            $m_original_chart[$key][] = VoxAnswersDependency::where('question_dependency_id', $q->related->id)->where('answer', $key+1)->first()->cnt; 
                                            // $m_original_chart[$key][] = $count_people;                        
                                        }

                                        $tr = 0;
                                        foreach ($all_related_original_results as $alor) {
                                            $tr+= $alor->cnt;
                                        }
                                        $total_count = $tr; 

                                        // if($q->type == 'multiple_choice') {
                                        //     $results_total = $all_related_original_results->count();
                                        // } else {
                                        //     $results_total = $all_related_original_results->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
                                        // } 

                                        foreach ($m_original_chart as $key => $value) {
                                            foreach ($value as $k => $v) {
                                                if($k == 1 && $v == 0) {
                                                    $value[$k] = '0';
                                                } else {
                                                    $value[$k] =  $v;
                                                }
                                            }
                                            $m_original_chart[$key] = $value;

                                            $m_original_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total_count);

                                        }                                    

                                        usort($m_original_chart, function($a, $b) {
                                            return $a[2] <= $b[2];
                                        });

                                        $rows_breakdown[] = $m_original_chart;
                                        $rows_breakdown[] = [''];
                                    }
                                }

                            } else if($chosen_dem == 'gender') {

                                $main_breakdown_chart = [];
                                $male_breakdown_chart = [];
                                $female_breakdown_chart = [];

                                $main_total_count = 0;
                                $male_total_count = 0;
                                $female_total_count = 0;

                                foreach ($answers_array as $key => $value) {
                                    $main_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                                    $male_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                                    $female_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                                    $count_people = 0;
                                    $count_people_male = 0;
                                    $count_people_female = 0;


                                    foreach ($all_results as $k => $v) {
                                        if(!empty($v->gender)) {
                                            if($q->type == 'scale' ) {
                                                if($v->scale == ($key + 1)) {
                                                    $count_people++;

                                                    if($v->gender == 'm') {
                                                        $count_people_male++;
                                                    }

                                                    if($v->gender == 'f') {
                                                        $count_people_female++;
                                                    }
                                                }
                                            } else {

                                                if($v->answer == ($key + 1)) {
                                                    $count_people++;

                                                    if($v->gender == 'm') {
                                                        $count_people_male++;
                                                    }

                                                    if($v->gender == 'f') {
                                                        $count_people_female++;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $main_total_count = $main_total_count + $count_people;
                                    $male_total_count = $male_total_count + $count_people_male;
                                    $female_total_count = $female_total_count + $count_people_female;

                                    $main_breakdown_chart[$key][] = $count_people;
                                    $male_breakdown_chart[$key][] = $count_people_male;
                                    $female_breakdown_chart[$key][] = $count_people_female;
                                }


                                if(!empty(Request::input('scale-for'))) {
                                    $list = json_decode($q->answers, true);
                                    $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title).' ['.$list[(Request::input('scale-for') - 1)].']';
                                } else {
                                    $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                                }

                                $cols_q_title_second = [
                                    $title_stats,
                                ];

                                $rows_breakdown[] = $cols_q_title_second;

                                $chart_titles = [
                                    '',
                                    'Total',
                                    'Total',
                                    'Men',
                                    'Men',
                                    'Women',
                                    'Women',
                                ];

                                $rows_breakdown[] = $chart_titles;

                                foreach ($main_breakdown_chart as $key => $value) {
                                    foreach ($value as $k => $v) {
                                        if($k == 1 && $v == 0) {
                                            $value[$k] = '0';
                                        } else {
                                            $value[$k] =  $v;
                                        }
                                    }
                                    $main_breakdown_chart[$key] = $value;
                                    $main_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total);
                                }

                                foreach ($female_breakdown_chart as $key => $value) {
                                    foreach ($value as $k => $v) {
                                        if($k == 1 && $v == 0) {
                                            $value[$k] = '0';
                                        } else {
                                            $value[$k] =  $v;
                                        }
                                    }
                                    $female_breakdown_chart[$key] = $value;
                                    $female_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total);
                                }

                                foreach ($male_breakdown_chart as $key => $value) {
                                    foreach ($value as $k => $v) {
                                        if($k == 1 && $v == 0) {
                                            $value[$k] = '0';
                                        } else {
                                            $value[$k] =  $v;
                                        }
                                    }
                                    $male_breakdown_chart[$key] = $value;
                                    $male_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total);
                                }

                                usort($main_breakdown_chart, function($a, $b) {
                                    return $a[2] <= $b[2];
                                });
                                
                                $male_breakdown_final = [];
                                $female_breakdown_final = [];
                                foreach($main_breakdown_chart as $key => $value) {
                                    foreach ($male_breakdown_chart as $k => $v) {
                                        if($v[0] == $value[0]) {
                                            $male_breakdown_final[$key] = [
                                                $v[1],
                                                $v[2],
                                            ];
                                        }
                                    }

                                    foreach ($female_breakdown_chart as $k => $v) {
                                        if($v[0] == $value[0]) {
                                            $female_breakdown_final[$key] = [
                                                $v[1],
                                                $v[2],
                                            ];
                                        }
                                    }
                                }

                                foreach($main_breakdown_chart as $key => $value) {
                                    $main_breakdown_chart[$key][] = $male_breakdown_final[$key][0];
                                    $main_breakdown_chart[$key][] = $male_breakdown_final[$key][1];
                                    $main_breakdown_chart[$key][] = $female_breakdown_final[$key][0];
                                    $main_breakdown_chart[$key][] = $female_breakdown_final[$key][1];
                                }

                                $ordered_diez = [];

                                foreach ($main_breakdown_chart as $key => $value) {

                                    if(mb_strpos($value[0], '#')===0) {
                                        $ordered_diez[] = $value;
                                        unset( $main_breakdown_chart[$key] );
                                    }
                                }

                                if(count($ordered_diez)) {

                                    if( count($ordered_diez) > 1) {
                                        usort($ordered_diez, function($a, $b) {
                                            return $a[2] <= $b[2];
                                        });

                                        foreach ($ordered_diez as $key => $value) {

                                            $value[0] = mb_substr($value[0], 1);

                                            $main_breakdown_chart[] = $value;
                                        }
                                    } else {
                                        foreach ($ordered_diez as $key => $value) {

                                            $ordered_diez[$key][0] = mb_substr($value[0], 1);
                                        }
                                        $main_breakdown_chart[] = $ordered_diez[0];
                                    }

                                    $main_breakdown_chart = array_values($main_breakdown_chart);
                                }

                                $rows_breakdown[] = $main_breakdown_chart;
                                $rows_breakdown[] = [
                                    '',
                                    $main_total_count,
                                    '',
                                    $male_total_count,
                                    '',
                                    $female_total_count,
                                ];
                                $rows_breakdown[] = [''];

                            } else if($chosen_dem == 'country_id') {

                            } else {

                                $main_breakdown_chart = [];
                                $dem_breakdown_chart = [];
                                $main_total_count = 0;

                                if($chosen_dem == 'age' ) {
                                    $config_dem_groups = config('vox.age_groups');
                                } else {
                                    $config_dem_groups = config('vox.details_fields')[$chosen_dem]['values'];
                                }
                               
                                foreach ($answers_array as $key => $value) {
                                    $main_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                                    $dem_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                                    $count_people = 0;

                                    //da opravq, ako choveka e izbral opredeleni godini (dem_options)

                                    $dem_count = [];
                                    foreach($config_dem_groups as $k => $v) {
                                        $dem_count[$k] = [
                                            'count' => 0,
                                        ];
                                    }

                                    foreach ($all_results as $k => $v) {

                                        if(!empty($v->$chosen_dem)) {

                                            if($q->type == 'scale' ) {
                                                if($v->scale == ($key + 1)) {
                                                    $count_people++;
                                                    $dem_count[$v->$chosen_dem]['count']++;
                                                }

                                            } else {
                                                if($v->answer == ($key + 1)) {
                                                    $count_people++;                                                
                                                    $dem_count[$v->$chosen_dem]['count']++;
                                                }
                                            }
                                        }
                                    }

                                    $main_total_count = $main_total_count + $count_people;
                                    $main_breakdown_chart[$key][] = $count_people;
                                    $dem_breakdown_chart[$key][] = $dem_count;
                                }

                                // dd($main_breakdown_chart, $age_breakdown_chart);

                                if(!empty(Request::input('scale-for'))) {
                                    $list = json_decode($q->answers, true);
                                    $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title).' ['.$list[(Request::input('scale-for') - 1)].']';
                                } else {
                                    $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                                }

                                $cols_q_title_second = [
                                    $title_stats,
                                ];

                                $rows_breakdown[] = $cols_q_title_second;

                                $chart_titles = [
                                    '',
                                    'Total',
                                    'Total',
                                ];

                                foreach($config_dem_groups as $ak => $dem_name) {
                                    $chart_titles[] = $dem_name;
                                    $chart_titles[] = $dem_name;
                                }

                                $rows_breakdown[] = $chart_titles;

                                foreach ($main_breakdown_chart as $key => $value) {
                                    foreach ($value as $k => $v) {
                                        if($k == 1 && $v == 0) {
                                            $value[$k] = '0';
                                        } else {
                                            $value[$k] =  $v;
                                        }
                                    }
                                    $main_breakdown_chart[$key] = $value;
                                    $main_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total);
                                }

                                $total_count_by_group = [];

                                foreach($config_dem_groups as $k => $v) {
                                    $total_count_by_group[$k] = 0;
                                }

                                foreach ($dem_breakdown_chart as $key => $value) {
                                    foreach($value[1] as $k => $v) {
                                        $total_count_by_group[$k]+=$v['count'];
                                    }
                                }

                                foreach ($dem_breakdown_chart as $key => $value) {
                                    foreach($value[1] as $k => $v) {
                                        $dem_breakdown_chart[$key][1][$k] = [
                                            $v['count'],
                                            $v['count'] == 0 ? '0' : ($v['count'] / $total_count_by_group[$k])
                                        ];
                                    }
                                }

                                usort($main_breakdown_chart, function($a, $b) {
                                    return $a[2] <= $b[2];
                                });
                                
                                $dem_breakdown_final = [];
                                foreach($main_breakdown_chart as $key => $value) {
                                    foreach ($dem_breakdown_chart as $k => $v) {

                                        if($v[0] == $value[0]) {
                                            $dem_breakdown_final[$key] = $v;
                                        }
                                    }
                                }

                                foreach ($dem_breakdown_final as $key => $value) {
                                    foreach($value[1] as $k => $v) {
                                        // dd($k, $v);
                                        $main_breakdown_chart[$key][] = $v[0];
                                        $main_breakdown_chart[$key][] = $v[1];
                                    }
                                }

                                $ordered_diez = [];

                                foreach ($main_breakdown_chart as $key => $value) {

                                    if(mb_strpos($value[0], '#')===0) {
                                        $ordered_diez[] = $value;
                                        unset( $main_breakdown_chart[$key] );
                                    }
                                }

                                if(count($ordered_diez)) {

                                    if( count($ordered_diez) > 1) {
                                        usort($ordered_diez, function($a, $b) {
                                            return $a[2] <= $b[2];
                                        });

                                        foreach ($ordered_diez as $key => $value) {

                                            $value[0] = mb_substr($value[0], 1);

                                            $main_breakdown_chart[] = $value;
                                        }
                                    } else {
                                        foreach ($ordered_diez as $key => $value) {

                                            $ordered_diez[$key][0] = mb_substr($value[0], 1);
                                        }
                                        $main_breakdown_chart[] = $ordered_diez[0];
                                    }

                                    $main_breakdown_chart = array_values($main_breakdown_chart);
                                }

                                $rows_breakdown[] = $main_breakdown_chart;

                                $final_count_group = [
                                    '',
                                    $main_total_count,
                                ];

                                foreach($total_count_by_group as $k => $v) {
                                    $final_count_group[] = '';
                                    $final_count_group[] = $v;
                                } 

                                $rows_breakdown[] = $final_count_group;
                                $rows_breakdown[] = [''];
                            }                        

                        }

                        $flist['Breakdown'] = $rows_breakdown;

                        $fname = $vox->title;

                        $pdf_title = strtolower(str_replace(['?', ' ', ':'], [' ', '-', ' '] ,$fname)).'-dentavox'.mb_substr(microtime(true), 0, 10);

                        return (new MultipleStatSheetExport($flist, $breakdown_rows_count))->download($pdf_title.'.xlsx');

                    } else {
                        return $this->ShowVoxView('download-stats', array(
                            'vox' => $vox,
                            'question' => $q,
                            'launched_date' => $launched_date,
                            'scales' => config('vox.stats_scales'),
                            'canonical' => $vox->getStatsList(),
                            'social_image' => $vox->getSocialImageUrl('stats'),
                            'seo_title' => $seo_title,
                            'seo_description' => $seo_description,
                            'social_title' => $social_title,
                            'social_description' => $social_description,
                            'demographics' => $demographics,
                            'demographics_count' => count($demographics),
                            'dem_options' => $dem_options,
                            'respondents' => $resp,
                            'all_period' => $all_period,
                            'format' => $format,
                        ));
                    }
                }
            }

            return redirect( getVoxUrl('/'));

        } else {

            $filters = [
                'all' => 'All times ('.date('d/m/Y',strtotime($vox->launched_at)).'-'.date('d/m/Y').')',
            ];

            if($vox->launched_at < Carbon::now()->subDays(7)) {
                $filters['last7'] = 'Last 7 days';
            }

            if($vox->launched_at < Carbon::now()->subDays(30)) {
                $filters['last30'] = 'Last 30 days';
            }

            if($vox->launched_at < Carbon::now()->subYear()) {
                $filters['last365'] = 'Last year';
            }

            $active_filter = Request::input('filter');
            if(!isset($filters[$active_filter])) {
                $active_filter = key($filters);
            }

            $blurred_stats = false;
            if (empty($this->user) && $vox->stats_questions->count() > 3) {
                $blurred_stats = true;
            }
            $demogr = [];
            foreach ($vox->stats_questions as $st_key => $st_value) {
                if(!empty($st_value->stats_fields)) {
                    foreach ($st_value->stats_fields as $value) {
                        if(!in_array($value, $demogr)) {
                            $demogr[] = $value;
                        }
                    }
                }
            }

            $items_array = [
                'demogr' => $demogr,
                'taken' => $this->user ? $this->user->filledVoxes() : [],
                'respondents' => $respondents_count,
                'respondents_country' => $respondents_country_count,
                'filters' => $filters,
                'active_filter' => $active_filter,
                'vox' => $vox,
                'launched_date' => $launched_date,
                'scales' => config('vox.stats_scales'),
                'blurred_stats' => $blurred_stats,
                'js' => [
                    'stats.js',
                ],
                'css' => [
                    'vox-stats-single.css'
                ],
                'canonical' => $vox->getStatsList(),
                'social_image' => $vox->getSocialImageUrl('stats'),
                'seo_title' => $seo_title,
                'seo_description' => $seo_description,
                'social_title' => $social_title,
                'social_description' => $social_description,
            ];

            if(!empty($this->user)) {
                $items_array['js'][] = 'amcharts-core.js';
                $items_array['js'][] = 'amcharts-maps.js';
                $items_array['js'][] = 'amcharts-worldLow.js';
                $items_array['js'][] = 'gstatic-charts-loader.js';
            }

            return $this->ShowVoxView('stats-survey', $items_array);
        }
    }

    public function processArray($arr) {
        $newarr = [];
        foreach ($arr as $key => $value) {
            if(is_array($value)) {
                $a = $this->processArray($value);
                array_unshift($a, $key);
                $newarr[] = $a;
            } else {
                $newarr[] = [$key.'', $value];
            }
        }
        return $newarr;
    }

    public function download() {
        
        if(!empty($this->user) && Request::isMethod('post')) {
            //dd(Request::all());
            $validator = Validator::make(Request::all(), [
                'download-format' => array('required'),
                'download-date' => array('required'),
                'date-from-download' => array('required_if:download-date,custom'),
                'date-to-download' => array('required_if:download-date,custom'),
                'download-demographic' => array('required', 'array'),
                'stats-for' => array('required'),
            ]);

            if ($validator->fails()) {

                $msg = $validator->getMessageBag()->toArray();
                $ret = array(
                    'success' => false,
                    'messages' => array()
                );

                foreach ($msg as $field => $errors) {
                    if ($field == 'download-format') {
                        $ret['messages'][$field] = 'Please, choose a download format.';
                    } else if($field == 'download-date' || $field == 'date-from-download' || $field == 'date-to-download') {
                        $ret['messages'][$field] = 'Please, choose a date period.';
                    } else if($field == 'download-demographic') {
                        $ret['messages'][$field] = 'Please, choose demographics.';
                    } else {
                        $ret['messages'][$field] = implode(', ', $errors);
                    }
                }

            } else {
                if (Request::input('download-date') == 'all') {
                    $date = 'all';
                } else {
                    $date = Request::input('date-from-download').'-'.Request::input('date-to-download');
                }

                $for_scale='';

                if( !empty(Request::input('scale-for'))) {
                    $for_scale.='&scale-for='.Request::input('scale-for');
                }

                $tail='';
                foreach(Request::input('download-demographic') as $dem) {
                    if($dem != 'country_id' && $dem != 'gender' && (!empty(Request::input('download-'.$dem)) || Request::input('download-'.$dem) === 0 )) {
                        $tail.='&dem-'.$dem.'='.implode(',',Request::input('download-'.$dem));
                    }
                }

                $demographics_details = Request::input('download-demographic');

                
                if(Request::input('download-format') != 'xlsx') {
                    $removed_key = array_search('country_id', $demographics_details);

                    if(!empty($removed_key)) {
                        unset($demographics_details[$removed_key]);
                    }
                }

                $ret = array(
                    'success' => true,
                    'tail' => '?create-stat-pdf=true&stats-for='.Request::input('stats-for').$for_scale.'&format='.Request::input('download-format').'&download-date='.$date.'&demographics='.implode(',', $demographics_details).$tail
                );
            }
            return Response::json( $ret );
        }

        return redirect(getLangUrl('/'));
    }

    public function createPdf() {
        if(!empty($this->user) && !empty(Request::input("hidden_html"))) {

            set_time_limit(300);
            ini_set('memory_limit', '1024М');

            $html = Request::input("hidden_html");
            $title = Request::input("stats-title");
            $original_title = Request::input("stats-original-title");
            $respondents = Request::input("stats-respondents");
            $period = Request::input("period");

            //$height = ceil(Request::input("hidden_heigth") / 2 + 200) * 72 / 96;
            //setPaper(array(0,0,600,$height))->

            $pdf = PDF::loadView('vox.export-stats', [
                'data' => $html,
                'original_title' => $original_title,
                'respondents' => $respondents,
                'period' => $period,
                'title' => $title,
            ]);

            $dir = storage_path().'/app/public/pdf/';
            if(!is_dir($dir)) {
                mkdir($dir);
            }


            $pdf_title = strtolower(str_replace(['?', ' ', ':', '&'], ['', '-', '', 'and'] ,$original_title)).'-dentavox'.mb_substr(microtime(true), 0, 10);

            $pdf->save($dir.'/'.$pdf_title.'.pdf');

            $sess = [
                'download_stat' => $pdf_title
            ];
            session($sess);

            $ret = array(
                'success' => true,
                'url' => Request::input("stat_url").'?download=1',
            );

            return Response::json( $ret );

            // return $pdf->stream($pdf_title.'.pdf');

            // return $pdf->download('pdf.pdf');
        }

        return redirect( getVoxUrl('/'));
    }

    public function createPng() {

        $cur_time = mb_substr(microtime(true), 0, 10);

        $png_title = strtolower(str_replace(['?', ' ', ':', '&'], ['', '-', '', 'and'] ,Request::input("stat_title"))).'-dentavox'.$cur_time;

        $folder = storage_path().'/app/public/png/'.$png_title;
        if(!is_dir($folder)) {
            mkdir($folder);
        }

        $picture_title = strtolower(str_replace(['?', ' ', ':', '&'], ['', '-', '', 'and'] ,Request::input("stat_title"))).'-dentavox-';

        $count_img = 0;
        for ($i=1; $i < 7; $i++) { 

            if(!empty(Input::file('picture'.$i))) {
                $count_img++;

                $newName = $folder.'/'.$picture_title.$i.'.png';
                copy( Input::file('picture'.$i)->path(), $newName );
            }
        }
        if($count_img > 1) {
            exec('cd '.$folder.' && zip -r0 '.$folder.'.zip ./*');
        }        

        $sess = [
            'download_stat_png' => $png_title
        ];
        session($sess);

        $ret = array(
            'success' => true,
            'url' => Request::input("stat_url").'?download-png=1',
        );

        return Response::json( $ret );
    }


    public function download_file($locale=null,$name) {
        session()->pull('download_stat');

        $file = storage_path().'/app/public/pdf/'.$name.'.pdf';
        return response()->download($file);
    }


    public function download_file_png($locale=null,$name) {
        session()->pull('download_stat_png');

        $file = storage_path().'/app/public/png/'.$name.'.zip';

        if (file_exists($file)) {
            return response()->download($file);
        } else {
            $png_file = storage_path().'/app/public/png/'.$name.'/'.mb_substr($name, 0, -10).'-1.png';
            return response()->download($png_file);
        }
        
    }

    private function paginate($items, $perPage = 10, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function getAnswerNumber($q_type, $answers, $answerfield) {
        if($q_type == 'number') {
            foreach ($answers as $key => $value) {
                $value_string = $value;
                if (strpos( $value, '-') !== FALSE) {
                    //from 0-100,0-1000
                    $last_num = intval(explode('-', $value)[1]);

                    if($answerfield <= $last_num) {
                        $answer_number = $value_string;
                        break;
                    }
                } else {
                    //from 0-10
                    $answer_number = $answerfield;
                    break;
                }
            }
        } else {
            if(isset($answers[ $answerfield-1])) {
                $answer_number = $answers[ $answerfield-1];
            } else {
                $answer_number = '';
            }
        }

        return $answer_number;
    }

    private function reorderStats($main_chart, $question) {
        $reorder = true;

        $count_diez = 0;
        foreach ($main_chart as $key => $value) {
            if(mb_strpos($key, '#')===0) {
                $count_diez++;
            }
        }

        if(count($main_chart) == $count_diez) {
            $reorder = false;
        }

        //reorder answers by respondents desc if they're not from scale!!
        return empty($question->vox_scale_id) && empty($question->dont_randomize_answers) && $reorder && $question->type != 'number';
    }
                    
}