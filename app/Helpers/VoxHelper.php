<?php

namespace App\Helpers;

use App\Models\VoxAnswersDependency;
use App\Models\VoxQuestion;
use App\Models\VoxAnswer;
use App\Models\VoxScale;
use App\Models\Vox;

use Carbon\Carbon;
use DB;

class VoxHelper {

    public static function translateVoxInfo($lang_code, $vox) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$vox->slug."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $slug = curl_exec ($ch);
        curl_close ($ch);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$vox->title."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $title = curl_exec ($ch);
        curl_close ($ch);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$vox->description."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $description = curl_exec ($ch);
        curl_close ($ch);

        $translation = $vox->translateOrNew($lang_code);
        $translation->vox_id = $vox->id;
        $translation->slug = json_decode($slug, true)['translations'][0]['text'];
        $translation->title = json_decode($title, true)['translations'][0]['text'];
        $translation->description = json_decode($description, true)['translations'][0]['text'];
        $translation->save();
    }

    public static function translateQuestionWithAnswers($lang_code, $question) {

        $translation = $question->translateOrNew($lang_code);
        $translation->vox_question_id = $question->id;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$question->question."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);
        curl_close ($ch);

        $translation->question = isset(json_decode($server_output, true)['translations']) ? json_decode($server_output, true)['translations'][0]['text'] : '';

        if(!empty($question->answers) && !empty(json_decode($question->answers, true))) {

            $answers = json_decode($question->answers, true);
            if($answers) {
                $translated_answers = [];

                foreach($answers as $a) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                                "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$a."&target_lang=".strtoupper($lang_code));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $server_output = curl_exec ($ch);
                    curl_close ($ch);

                    $translated_answers[] = json_decode($server_output, true)['translations'][0]['text'];
                }

                $translation->answers = json_encode( $translated_answers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );
            } else {
                $translation->answers = '';                            
            }
        } else {
            $translation->answers = '';                            
        }

        $translation->save();
    }

    public static function translateSurvey($lang_code, $vox) {
        self::translateVoxInfo($lang_code, $vox);

        foreach($vox->questions as $question) {
            self::translateQuestionWithAnswers($lang_code, $question);
        }
        
        if(!in_array($lang_code, $vox->translation_langs)) {
            $available_translations = $vox->translation_langs;
            $available_translations[] = $lang_code;
            $vox->translation_langs = $available_translations;
            $vox->save();
        }
    }

    public static function exportStatsXlsx($vox, $q, $demographics, $results, $scale_for, $all_period, $is_admin) {

        $cols = ['Survey Date'];
        $cols2 = [''];

        foreach ($demographics as $dem) {
            if($dem != 'relation') {
                $cols[] = config('vox.stats_scales')[$dem];
                $cols2[] = '';
            }
        }

        $slist = VoxScale::get();
        $scales = [];
        foreach ($slist as $sitem) {
            $scales[$sitem->id] = $sitem;
        }

        if(in_array('relation', $demographics) && $q->used_for_stats == 'dependency') {
            if(!empty($q->stats_answer_id)) {

                $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                $cols[] = $q->related->questionWithoutTooltips().' ['.$q->removeAnswerTooltip($list[$q->stats_answer_id - 1]).']';
                $cols2[] = '';
            } else {
                if($q->related->type == 'multiple_choice') {
                    $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);
                    foreach ($list as $l) {
                        $cols[] = $q->related->questionWithoutTooltips();
                        $cols2[] = mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l;
                    }
                } else {
                    $cols[] = $q->related->questionWithoutTooltips();
                    $cols2[] = '';
                }
            }
        }

        if( $q->type == 'single_choice' || $q->type == 'number') {
            $cols[] = in_array('relation', $demographics) && $q->used_for_stats == 'dependency' ? $q->questionWithoutTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
            $cols2[] = '';

        } else if( $q->type == 'scale' ) {
            $list = json_decode($q->answers, true);
            $cols[] = $q->stats_title.' ['.$list[($scale_for - 1)].']';
            $cols2[] = '';

        } else if( $q->type == 'rank' ) {
            $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);
            foreach ($list as $l) {
                $cols[] = in_array('relation', $demographics) && $q->used_for_stats == 'dependency' ? $q->questionWithoutTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
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
                $cols[] = in_array('relation', $demographics) && $q->used_for_stats == 'dependency' ? $q->questionWithoutTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
                $cols2[] = $q->removeAnswerTooltip(mb_substr($l, 0, 1)=='!' ? mb_substr($l, 1) : $l);
            }
        }
        // echo $scale_for.'<br/>';

        if($q->type == 'scale') {
            $breakdown_results = clone $results;
            $breakdown_results = $breakdown_results->where('scale', $scale_for)->groupBy('user_id')->get();
            $all_results = $results->where('answer', $scale_for)->get();
        } else if ($q->type == 'rank' || $q->type == 'multiple_choice') {
            $breakdown_results = clone $results;
            $breakdown_results = $breakdown_results->groupBy('user_id')->get();
            $all_results = $results->get();
        } else {
            $all_results = $results->get();
            $breakdown_results = $all_results;
        }

        // if($q->type == 'scale') {
        //     $results_resp = clone $results;
        //     $results_resp = $results_resp->where('scale', $scale_for)->groupBy('user_id')->get()->count();
        // } else {
            $results_resp = clone $results;
            $results_resp = $results_resp->groupBy('user_id')->get()->count();
        // }
        // dd($results_resp, $results);

        $cols_title = [
            strtoupper($vox->title).', Base: '.$results_resp.' respondents, '.$all_period
        ];


        if(!empty($is_admin)) {
            if(!empty($scale_for)) {
                $list = json_decode($q->answers, true);
                $t_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() )).' ['.$list[($scale_for - 1)].']';
            } else {
                $t_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
            }

            $rows = [
                $cols_title,
                [$t_stats],
                $cols,
                $cols2
            ];
        } else {
            $rows = [
                $cols_title,
                $cols,
                $cols2
            ];
        }

        // dd($breakdown_results);

        foreach ($all_results as $answ) {
            $row = [];

            $row[] = $answ->created_at ? $answ->created_at->format('d.m.Y') : '';

            foreach ($demographics as $dem) {
                if($dem != 'relation') {

                    if($dem == 'gender') {

                        if(!empty($answ->gender)) {
                            $row[] = $answ->gender=='m' ? 'Male' : 'Female';
                            // $row[] = $answ->gender=='m' ? 'Male '.$answ->user_id : 'Female '.$answ->user_id;
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

            if(in_array('relation', $demographics) && $q->used_for_stats == 'dependency') {
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
                        $row[] = $given_related_answer ? $q->removeAnswerTooltip(mb_strpos($list[$given_related_answer->answer - 1], '!')===0 || mb_strpos($list[$given_related_answer->answer - 1], '#')===0 ?  mb_substr($list[$given_related_answer->answer - 1], 1) : $list[$given_related_answer->answer - 1]) : '0';
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

        $rows[] = [''];

        $flist['Raw Data'] = $rows;

        ///Breakdown Sheet

        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

        $breakdown_rows_count = 0;

        if($q->type == 'scale') {
            $results_total = $results->where('answer', $scale_for)->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;
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

            if($chosen_dem == 'relation' && $q->used_for_stats == 'dependency') {

                $second_chart = [];

                $answers_related_array = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

                foreach ($answers_related_array as $key => $value) {
                    $second_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                }

                if(!empty($q->stats_answer_id)) {
                    $convertedRelation = self::downloadRelationXlsx($q, $q->stats_answer_id, $scales, $rows_breakdown);
                    $rows_breakdown = $convertedRelation['rows_breakdown'];
                    $rows_breakdown[] = [''];

                    $breakdown_rows_count = $convertedRelation['breakdown_rows_count'];
                } else {
                    for($i = 1; $i <= count($second_chart); $i++) {
                        $convertedRelation = self::downloadRelationXlsx($q, $i, $scales, $rows_breakdown);
                        $rows_breakdown = $convertedRelation['rows_breakdown'];
                        $rows_breakdown[] = [''];

                        $breakdown_rows_count = $convertedRelation['breakdown_rows_count'];
                    }
                }

            } else if($chosen_dem == 'gender') {

                $main_breakdown_chart = [];
                $male_breakdown_chart = [];
                $female_breakdown_chart = [];

                $main_total_count = 0;
                $male_total_count = 0;
                $female_total_count = 0;

                $unique_total_count = 0;
                $unique_male_total_count = 0;
                $unique_female_total_count = 0;

                // dd($all_results, $breakdown_results);
                // dd($answers_array);
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
                    
                    $unique_count_people = 0;
                    $unique_count_people_male = 0;
                    $unique_count_people_female = 0;

                    foreach ($breakdown_results as $k => $v) {
                        if(!empty($v->gender)) {
                            if($q->type == 'scale' ) {
                                if($v->scale == ($key + 1)) {
                                    $unique_count_people++;

                                    if($v->gender == 'm') {
                                        $unique_count_people_male++;
                                    }

                                    if($v->gender == 'f') {
                                        $unique_count_people_female++;
                                    }
                                }
                            } else {
                                if($v->answer == ($key + 1)) {
                                    $unique_count_people++;

                                    if($v->gender == 'm') {
                                        $unique_count_people_male++;
                                    }

                                    if($v->gender == 'f') {
                                        $unique_count_people_female++;
                                    }
                                }
                            }
                        }
                    }

                    $unique_total_count = $unique_total_count + $unique_count_people;
                    $unique_male_total_count = $unique_male_total_count + $unique_count_people_male;
                    $unique_female_total_count = $unique_female_total_count + $unique_count_people_female;

                    $main_total_count = $main_total_count + $count_people;
                    $male_total_count = $male_total_count + $count_people_male;
                    $female_total_count = $female_total_count + $count_people_female;

                    $main_breakdown_chart[$key][] = $count_people;
                    $male_breakdown_chart[$key][] = $count_people_male;
                    $female_breakdown_chart[$key][] = $count_people_female;
                }

                // dd($main_breakdown_chart);

                if(!empty($scale_for)) {
                    $list = json_decode($q->answers, true);
                    $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() )).' ['.$list[($scale_for - 1)].']';
                } else {
                    $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
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
                    $main_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $main_total_count : $unique_total_count));
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
                    $female_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $female_total_count : $unique_female_total_count));
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
                    $male_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $male_total_count : $unique_male_total_count));
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

            } else if($chosen_dem != 'relation') {

                $main_breakdown_chart = [];
                $dem_breakdown_chart = [];
                $unique_dem_breakdown_chart = [];
                $main_total_count = 0;
                $unique_main_total_count = 0;

                if($chosen_dem == 'age' ) {
                    $config_dem_groups = config('vox.age_groups');
                } else if($chosen_dem == 'country_id') {
                    $config_dem_groups = Country::with('translations')->get()->pluck('name', 'id')->toArray();
                } else {
                    $config_dem_groups = config('vox.details_fields')[$chosen_dem]['values'];
                }
               
                foreach ($answers_array as $key => $value) {
                    $main_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                    $dem_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);
                    $unique_dem_breakdown_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                    $count_people = 0;
                    $unique_count_people = 0;

                    $dem_count = [];
                    foreach($config_dem_groups as $k => $v) {
                        $dem_count[$k] = [
                            'count' => 0,
                        ];
                    }

                    $unique_dem_count = [];
                    foreach($config_dem_groups as $k => $v) {
                        $unique_dem_count[$k] = [
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

                    foreach ($breakdown_results as $k => $v) {

                        if(!empty($v->$chosen_dem)) {

                            if($q->type == 'scale' ) {
                                if($v->scale == ($key + 1)) {
                                    $unique_count_people++;
                                    $unique_dem_count[$v->$chosen_dem]['count']++;
                                }
                            } else {
                                if($v->answer == ($key + 1)) {
                                    $unique_count_people++;                                                
                                    $unique_dem_count[$v->$chosen_dem]['count']++;
                                }
                            }
                        }
                    }

                    $unique_main_total_count = $unique_main_total_count + $unique_count_people;
                    $main_total_count = $main_total_count + $count_people;
                    $main_breakdown_chart[$key][] = $count_people;
                    $dem_breakdown_chart[$key][] = $dem_count;
                    $unique_dem_breakdown_chart[$key][] = $unique_dem_count;
                }

                // dd($main_breakdown_chart, $age_breakdown_chart);

                if(!empty($scale_for)) {
                    $list = json_decode($q->answers, true);
                    $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() )).' ['.$list[($scale_for - 1)].']';
                } else {
                    $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : (!empty($q->stats_title) ? $q->stats_title : $q->questionWithoutTooltips() ));
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
                    $main_breakdown_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / ($q->type == 'scale' ? $main_total_count : $unique_main_total_count));
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

                $unique_total_count_by_group = [];

                foreach($config_dem_groups as $k => $v) {
                    $unique_total_count_by_group[$k] = 0;
                }

                foreach ($unique_dem_breakdown_chart as $key => $value) {
                    foreach($value[1] as $k => $v) {
                        $unique_total_count_by_group[$k]+=$v['count'];
                    }
                }

                foreach ($dem_breakdown_chart as $key => $value) {
                    foreach($value[1] as $k => $v) {
                        $dem_breakdown_chart[$key][1][$k] = [
                            $v['count'],
                            $v['count'] == 0 ? '0' : ($v['count'] / $total_count_by_group[$k]) //tuk trqbwa da e unique ?
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

        return [
            'flist' => $flist,
            'breakdown_rows_count' => $breakdown_rows_count, 
        ];
    }

    public static function downloadRelationXlsx($q, $answer, $scales, $rows_breakdown) {

        $list = $q->related->vox_scale_id && !empty($scales[$q->related->vox_scale_id]) ? explode(',', $scales[$q->related->vox_scale_id]->answers) :  json_decode($q->related->answers, true);

        $rows_breakdown[] = [$q->related->questionWithoutTooltips().' ['.$q->removeAnswerTooltip($list[$answer - 1]).']'];
        $rows_breakdown[] = ['in relation to:'];

        $cols_q_title_second = [
            ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).$q->questionWithoutTooltips()
        ];

        $rows_breakdown[] = $cols_q_title_second;

        $m_original_chart = [];
        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

        $breakdown_rows_count = count($answers_array);
        $total_count = 0;

        foreach ($answers_array as $key => $value) {
            $m_original_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

            $answer_resp = VoxAnswersDependency::where('question_id', $q->id)->where('question_dependency_id', $q->related->id)->where('answer_id', $answer)->where('answer', $key+1)->first();

            if($answer_resp) {
                $m_original_chart[$key][] = $answer_resp->cnt; 
                $total_count+=$answer_resp->cnt;
            } else {

                $cur_answer = VoxAnswer::whereNull('is_admin')
                ->where('question_id', $q->id)
                ->where('is_completed', 1)
                ->where('is_skipped', 0)
                ->where('answer', $key+1)
                ->has('user');        

                $quest = $q->related->id;
                $aaa = $answer;
                $cur_answer = $cur_answer->whereIn('user_id', function($query) use ($quest, $aaa) {
                    $query->select('user_id')
                    ->from('vox_answers')
                    ->where('question_id', $quest)
                    ->where('answer', $aaa);
                } )->groupBy('answer')->selectRaw('answer, COUNT(*) as cnt')->first();

                // dd($cur_answers);
                $m_original_chart[$key][] = $cur_answer ? $cur_answer->cnt : 0; 
                $total_count+=$cur_answer ? $cur_answer->cnt : 0; 
            }
        }
        // dd($m_original_chart, $total_count);

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

        $ordered_diez = [];

        foreach ($m_original_chart as $key => $value) {
            if(mb_strpos($value[0], '#')===0) {
                $ordered_diez[] = $value;
                unset( $m_original_chart[$key] );
            }
        }

        if(count($ordered_diez)) {

            if( count($ordered_diez) > 1) {
                usort($ordered_diez, function($a, $b) {
                    return $a[2] <= $b[2];
                });

                foreach ($ordered_diez as $key => $value) {
                    $value[0] = mb_substr($value[0], 1);
                    $m_original_chart[] = $value;
                }
            } else {
                foreach ($ordered_diez as $key => $value) {
                    $ordered_diez[$key][0] = mb_substr($value[0], 1);
                }
                $m_original_chart[] = $ordered_diez[0];
            }

            $m_original_chart = array_values($m_original_chart);
        }

        $rows_breakdown[] = $m_original_chart;

        return [
            'rows_breakdown' => $rows_breakdown,
            'breakdown_rows_count' => $breakdown_rows_count,
        ];
    }

    public static function getBirthyearOptions() {
        $ret = '';        

        for($i=(date('Y')-18);$i>=(date('Y')-90);$i--) {
            $age = date('Y') - $i;

            if ($age <= 24) {
                $index = '1';
            } else if($age <= 34) {
                $index = '2';
            } else if($age <= 44) {
                $index = '3';
            } else if($age <= 54) {
                $index = '4';
            } else if($age <= 64) {
                $index = '5';
            } else if($age <= 74) {
                $index = '6';
            } else if($age > 74) {
                $index = '7';
            }

            $ret .= '<option value="'.$i.'" demogr-index="'.$index.'">'.$i.'</option>';
        }

        return $ret;
    }

    public static function getDemographicQuestions() {
        $demographic_questions = [];
        $welcome_survey = Vox::find(11);
        $welcome_questions = VoxQuestion::where('vox_id', $welcome_survey->id)->get();
        
        foreach ($welcome_questions as $welcome_question) {
            $demographic_questions[$welcome_question->id] = $welcome_question->question;
        }

        $demographic_questions['gender'] = 'What is your biological sex?';
        $demographic_questions['birthyear'] = "What's your year of birth?";
        foreach (config('vox.details_fields') as $k => $v) {
            $demographic_questions[$k] = $v['label'];
        }

        return $demographic_questions;
    }

    public static function getDemographicAnswers() {

        $welcome_answers = [];

        foreach (self::getDemographicQuestions() as $key => $value) {
            if (is_numeric($key)) {
                $welcome_question = VoxQuestion::where('id', $key)->first();
                $welcome_answers[$welcome_question->id] = json_decode($welcome_question->answers, true);
            } else {
                if ($key == 'gender') {
                    $welcome_answers['gender'] = [
                        'Male',
                        'Female'
                    ];
                } else if ($key == 'birthyear') {
                    $welcome_answers['birthyear'] = [
                        '',
                    ];
                } else {
                    $welcome_answers[$key] = config('vox.details_fields')[$key]['values'];
                }
            }
        }

        return $welcome_answers;
    }

    public static function prepareQuery($question_id, $dates, $options = []) {

        $results = VoxAnswer::whereNull('is_admin')
        ->where('question_id', $question_id)
        ->where('is_completed', 1)
        ->where('is_skipped', 0)
        ->has('user');        

        if( isset($options['dependency_question']) && isset($options['dependency_answer']) ) {

            $q = $options['dependency_question'];
            $a = $options['dependency_answer'];

            $results = $results->whereIn('user_id', function($query) use ($q, $a) {
                $query->select('user_id')
                ->from('vox_answers')
                ->where('question_id', $q)
                ->where('answer', $a);
            });
        }

        if( isset($options['scale_answer_id']) ) {
            $results = $results->where('answer', $options['scale_answer_id']);
        }

        if( isset($options['scale_options']) && isset( $options['scale'] ) ) {
            $results = $results->whereIn($options['scale'], array_values($options['scale_options']));
        }

        if(is_array($dates)) {
            $from = Carbon::parse($dates[0]);
            $to = Carbon::parse($dates[1]);
            $results = $results->where('created_at', '>=', $from)->where('created_at', '<=', $to);
        } else if($dates) {
            $from = Carbon::parse($dates);
            $results = $results->where('created_at', '>=', $from);
        }

        return $results;
    }    

}