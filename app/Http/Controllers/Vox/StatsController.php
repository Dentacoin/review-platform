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

class StatsController extends FrontController
{
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
                'stats.js'
            ],
            'css' => [
                'vox-stats.css'
            ],
            'jscdn' => [
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js',
            ],
            'csscdn' => [
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css',
            ],
        ));
    }

	public function stats($locale=null, $slug=null, $question_id=null) {
        
        $this->current_page = 'stats';

		$vox = Vox::whereTranslationLike('slug', $slug)->first();

		if(empty($vox)) {
			return redirect( getLangUrl('page-not-found') );
		}

        if(empty($this->user)) {
            session([
                'vox-redirect-workaround' => str_replace( getLangUrl('/').App::getLocale().'/', '', $vox->getLink())
            ]);
        }

        if(!$vox->has_stats && empty($this->admin)) {
            return redirect( getLangUrl('dental-survey-stats') );
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
                if($dates) {

                    $results = $this->prepareQuery($question_id, $dates,[
                        'dependency_answer' => $answer_id,
                        'dependency_question' => $question->stats_relation_id,
                    ]);
                }
            } else {
                $results = $this->prepareQuery($question_id, $dates, [
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
                    $ans_array = VoxAnswer::where('question_id', $question_id)->groupBy('answer')->select('answer')->get();
                    foreach ($ans_array as $ans) {
                        $answers[] = $ans->answer;
                    }
                } else {
                    $ans_array = json_decode($question->answers);
                    foreach ($ans_array as $ans) {
                        $answers[] = strip_tags($question->removeAnswerTooltip($ans), ['a']);
                    }
                }
            }

            foreach ($answers as $key => $value) {
                if(mb_strpos($value, '!')===0 || ($question->type != 'single_choice' && mb_strpos($value, '#')===0)) {
                    $answers[$key] = mb_substr($value, 1);
                }
                $main_chart[$answers[$key]] = 0;
            }

            $related_question_type = false;

        	if($scale=='dependency') {

                $total = $this->prepareQuery($question_id, $dates);
                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

                if($dates) {

                    $results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
                    $results = $results->get();
                } else {
                    $results = VoxAnswersDependency::where('question_id', $question_id)->where('question_dependency_id', $question->stats_relation_id)->where('answer_id', $answer_id)->where('updated_at', '>=', Carbon::now()->addDays(-7))->get();

                    if($results->isEmpty()) {
                        $results = $this->prepareQuery($question_id, null,[
                            'dependency_answer' => $answer_id,
                            'dependency_question' => $question->stats_relation_id,
                        ]);

                        $results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
                        $results = $results->get();

                        foreach ($results as $result) {
                            $vda = new VoxAnswersDependency;
                            $vda->question_dependency_id = $question->stats_relation_id;
                            $vda->question_id = $question_id;
                            $vda->answer_id = $answer_id;
                            $vda->answer = $result->answer;
                            $vda->cnt = $result->cnt;
                            $vda->save();
                        }
                    }
                }

                foreach ($answers as $key => $value) {
                    $second_chart_array[$value] = 0;
                }

                foreach ($results as $res) {
                    if(!isset( $answers[ $res->$answerField-1 ] )) {
                        continue;
                    }
                    $second_chart_array[ $answers[ $res->$answerField-1 ] ] = $res->cnt;
                }

                foreach ($second_chart_array as $key => $value) {

                    if(mb_strpos($key, '!')===0 || mb_strpos($key, '#')===0) {

                        $second_chart[ $question->removeAnswerTooltip(mb_substr($key, 1)) ] = $value;
                    } else {
                        $second_chart[ $question->removeAnswerTooltip($key) ] = $value;
                    }   
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
        		$results = $this->prepareQuery($question->stats_relation_id, $dates);
        		$results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
        		$results = $results->get();
        		foreach ($results as $res) {
        			$main_chart[ $answers_related[ $res->$answerField-1 ] ] = $res->cnt;
        		}
        		

        	} else if($scale=='gender') {
                // if($question->type != 'multiple_choice') {
                //     $answer_id = null;
                // }                

                //dd($answer_id);
                $total = $this->prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id
                ])->whereNotNull('gender')->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

                $results_main_chart = $results->whereNotNull('gender')->groupBy($answerField, 'gender')->selectRaw($answerField.', gender, COUNT(*) as cnt')->get();

                if( $scale_options ) {
                    $results = $results->whereIn($scale, array_values($scale_options));
                }
        		$results = $results->whereNotNull('gender')->groupBy($answerField, 'gender')->selectRaw($answerField.', gender, COUNT(*) as cnt');
                $results = $results->get();

                foreach ($answers as $key => $value) {
                    $second_chart[$value] = 0;
                    $third_chart[$value] = 0;
                }

                foreach ($results_main_chart as $res_main) {

                    if($question->type == 'number') {
                        $answer_number = $res_main->$answerField;
                    } else {
                        $answer_number = $answers[ $res_main->$answerField-1];
                    }

                    if(!isset( $answer_number )) {
                        continue;
                    }

                    if(!isset($main_chart[ $answer_number ])) {
                        $main_chart[ $answer_number ] = 0;
                    }
                    $main_chart[ $answer_number ] += $res_main->cnt;
                }
                
        		foreach ($results as $res) {

                    if($question->type == 'number') {
                        $answer_number = $res->$answerField;
                    } else {
                        $answer_number = $answers[ $res->$answerField-1];
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
        				$second_chart[ $answer_number ] += $res->cnt; //m
        			}
        			if($res->gender=='m') {
        				$third_chart[ $answer_number ] += $res->cnt; //f
        			}

                    $totalm = $totalf = 0;
                    $totalQuery = $this->prepareQuery($question_id, $dates, [
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

        	} else if($scale=='country_id') {
        		$countries = Country::with('translations')->get()->keyBy('id');
                $total = $this->prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id
                ]);
                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

        		$results = $results->groupBy($answerField, 'country_id')->selectRaw($answerField.', country_id, COUNT(*) as cnt');
        		$results = $results->get();

        		foreach ($results as $res) {

                    if($question->type == 'number') {
                        $answer_number = $res->$answerField;
                    } else {
                        $answer_number = $answers[ $res->$answerField-1];
                    }

                    if(!isset( $answer_number )) {
                        continue;
                    }

        			if(!isset($main_chart[ $answer_number ])) {
        				$main_chart[ $answer_number ] = 0;
        			}
        			$main_chart[ $answer_number ] += $res->cnt;

        			if( $res->country_id ) {
                        $country = $countries->get($res->country_id);
                        $country->code = mb_strtoupper($country->code);
        				if(!isset($second_chart[ $country->code ] )) {
        					$second_chart[ $country->code ] = [
                                'name' => $country->name
                            ];
                            foreach ($answers as $a) {
                                $second_chart[ $country->code ][$a] = 0;
                            }
        				}
                        if(empty($answer_id) || $res->$answerField==$answer_id) {
                            $second_chart[ $country->code ][ $answer_number ] = $res->cnt; //m
                        }
        			}
        		}
        	} else if($scale=='age') {

                $total = $this->prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id, 
                    'scale' => $scale, 
                    'scale_options' => $scale_options
                ]);

                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->whereNotNull('age')->first()->num;
                if( $scale_options ) {
                    $results = $results->whereIn($scale, array_values($scale_options));
                }
        		$results = $results->groupBy($answerField, 'age')->selectRaw($answerField.', age, COUNT(*) as cnt');
        		$results = $results->get();

        		$age_to_group = config('vox.age_groups');

                if (!empty($scale_options)) {
                    foreach ($scale_options as $sv) {
                        $second_chart[ config('vox.age_groups.'.$sv) ] = [];
                        foreach ($answers as $a) {
                            $second_chart[ config('vox.age_groups.'.$sv) ][$a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
                    }
                } else {
    				foreach ($age_to_group as $k => $v) {
    					$second_chart[ $v ] = [];
    					foreach ($answers as $a) {
    						$second_chart[ $v ][$a] = !empty($this->admin) && $total == 0 ? 1 : 0;
    					}
    				}
                }

        		foreach ($results as $res) {

                    if($question->type == 'number') {
                        $answer_number = $res->$answerField;
                    } else {
                        $answer_number = $answers[ $res->$answerField-1];
                    }

                    if(!isset( $answer_number )) {
                        continue;
                    }
                    
        			if(!isset($main_chart[ $answer_number ])) {
        				$main_chart[ $answer_number ] = 0;
        			}
        			$main_chart[ $answer_number ] += $res->cnt;


        			if( $res->age ) {
	        			$second_chart[ $age_to_group[$res->age] ][ $answer_number ] = $res->cnt; //m
        			}
        		}
                
        	} else {

                $total = $this->prepareQuery($question_id, $dates, [
                    'scale_answer_id' => $scale_answer_id, 
                    'scale' => $scale, 
                    'scale_options' => $scale_options
                ]);
                $total = $total->select(DB::raw('count(distinct `user_id`) as num'))->whereNotNull($scale)->first()->num;
                if( $scale_options ) {
                    $results = $results->whereIn($scale, array_values($scale_options));
                }
                
        		$results = $results->groupBy($answerField, $scale)->selectRaw($answerField.', '.$scale.', COUNT(*) as cnt');
        		$results = $results->get();


                $age_to_group = config('vox.details_fields.'.$scale.'.values');
                if (!empty($scale_options)) {
                    foreach ($scale_options as $sv) {
                        $second_chart[ config('vox.details_fields.'.$scale.'.values.'.$sv) ] = [];
                        foreach ($answers as $a) {
                            $second_chart[ config('vox.details_fields.'.$scale.'.values.'.$sv) ][ $a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
                    }
                } else {
            		
    				foreach ($age_to_group as $k => $v) {
                        $second_chart[ $v ] = [];
                        foreach ($answers as $a) {
                            $second_chart[ $v ][$a] = !empty($this->admin) && $total == 0 ? 1 : 0;
                        }
    				}
                }

                //dd( $results->toArray() );
        		foreach ($results as $res) {

                    if($question->type == 'number') {
                        $answer_number = $res->$answerField;
                    } else {
                        $answer_number = $answers[ $res->$answerField-1];
                    }

                    if($res->$scale===null || !isset( $answer_number )) {
                        continue;
                    }


        			if(!isset($main_chart[ $answer_number ])) {
        				$main_chart[ $answer_number ] = 0;
        			}
        			$main_chart[ $answer_number ] += $res->cnt;


        			if( $res->$scale ) {
    	        		$second_chart[ $age_to_group[$res->$scale] ][ $answer_number ] = $res->cnt; //m
        			}
    		    }
        	}

            $main_chart = $this->processArray($main_chart);
            $second_chart = $this->processArray($second_chart);
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

                        if( $q->type == 'single_choice' ) {
                            $cols[] = in_array('relation', $demographics) ? $q->questionWithTooltips() : strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                            $cols2[] = '';
                        } else if( $q->type == 'scale' ) {
                            $list = json_decode($q->answers, true);
                            $cols[] = $q->stats_title.' ['.$list[(Request::input('scale-for') - 1)].']';
                            $cols2[] = '';

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

                        if($q->type == 'scale') {
                            $all_results = $results->where('answer', Request::input('scale-for'))->get();
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

                            } else if( $q->type == 'multiple_choice' ) {
                                $list = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) : json_decode($q->answers, true);

                                $i=1;
                                foreach ($list as $l) {
                                    $thisanswer = $i == $answ->answer;
                                    $row[] = $thisanswer ? '1' : '0';
                                    $i++;
                                }
                            }

                            $rows[] = $row;
                        }

                        $flist['Raw Data'] = $rows;
                        $m_chart = [];

                        $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

                        $breakdown_rows_count = 0;

                        foreach ($answers_array as $key => $value) {
                            $m_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                            $count_people = 0;
                            foreach ($all_results as $k => $v) {
                                if($q->type == 'scale' ) {
                                    if($v->scale == ($key + 1)) {
                                        $count_people++;
                                    }
                                } else {

                                    if($v->answer == ($key + 1)) {
                                        $count_people++;
                                    }
                                }
                            }

                            $m_chart[$key][] = $count_people;                        
                        }

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

                        if(in_array('relation', $demographics)) {

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

                                $all_results = VoxAnswer::whereNull('is_admin')
                                ->where('question_id', $q->id)
                                ->where('is_completed', 1)
                                ->where('is_skipped', 0)
                                ->has('user');

                                if (!empty(Request::input('download-date')) && Request::input('download-date') != 'all') {
                                    $from = Carbon::parse(explode('-', Request::input('download-date'))[0]);
                                    $to = Carbon::parse(explode('-', Request::input('download-date'))[1]);

                                    $all_results = $all_results->where('created_at', '>=', $from)
                                    ->where('created_at', '<=', $to);
                                }

                                $a = $q->stats_answer_id;

                                $all_results = $all_results->whereIn('user_id', function($query) use ($q, $a) {
                                    $query->select('user_id')
                                    ->from('vox_answers')
                                    ->where('question_id', $q->related->id)
                                    ->where('answer', $a);
                                } );

                                $answers_array = $q->vox_scale_id && !empty($scales[$q->vox_scale_id]) ? explode(',', $scales[$q->vox_scale_id]->answers) :  json_decode($q->answers, true);

                                $breakdown_rows_count = count($answers_array);

                                foreach ($answers_array as $key => $value) {
                                    $cur_chart[$key][] = mb_strpos($value, '!')===0 || ($q->type != 'single_choice' && mb_strpos($value, '#')===0) ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                                    $count_people = 0;
                                    foreach ($all_results->get() as $k => $v) {

                                        if($v->answer == ($key + 1)) {
                                            $count_people++;
                                        }
                                    }

                                    $cur_chart[$key][] = $count_people;                        
                                }
                                
                                $results_total = $all_results->select(DB::raw('count(distinct `user_id`) as num'))->first()->num;

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

                                    $all_related_original_results = VoxAnswer::whereNull('is_admin')
                                    ->where('question_id', $q->id)
                                    ->where('is_skipped', 0)
                                    ->has('user')
                                    ->whereIn('user_id', function($query) use ($q, $i) {
                                        $query->select('user_id')
                                        ->from('vox_answers')
                                        ->where('question_id', $q->related->id)
                                        ->where('answer', $i);
                                    });

                                    if (!empty(Request::input('download-date')) && Request::input('download-date') != 'all') {
                                        $from = Carbon::parse(explode('-', Request::input('download-date'))[0]);
                                        $to = Carbon::parse(explode('-', Request::input('download-date'))[1]);

                                        $all_related_original_results = $all_related_original_results->where('created_at', '>=', $from)
                                        ->where('created_at', '<=', $to);
                                    }

                                    foreach ($answers_array as $key => $value) {
                                        $m_original_chart[$key][] = mb_strpos($value, '!')===0 || mb_strpos($value, '#')===0 ? mb_substr($q->removeAnswerTooltip($value), 1) : $q->removeAnswerTooltip($value);

                                        $count_people = 0;
                                        foreach ($all_related_original_results->get() as $k => $v) {
                                            if($v->answer == ($key + 1)) {
                                                $count_people++;
                                            }
                                        }

                                        $m_original_chart[$key][] = $count_people;                        
                                    }

                                    $total_count = $all_related_original_results->select(DB::raw('count(distinct `user_id`) as num'))->first()->num; 

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

                        } else {
                            if(!empty(Request::input('scale-for'))) {
                                $list = json_decode($q->answers, true);
                                $title_stats = strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title).' ['.$list[(Request::input('scale-for') - 1)].']';
                            } else {
                                $title_stats = ($q->type == 'multiple_choice' ? '[Multiple choice] ' : '' ).strip_tags(!empty($q->stats_title_question) ? $q->questionWithoutTooltips() : $q->stats_title);
                            }

                            $cols_q_title_second = [
                                $title_stats
                            ];

                            $rows_breakdown[] = $cols_q_title_second;

                            foreach ($m_chart as $key => $value) {
                                foreach ($value as $k => $v) {
                                    if($k == 1 && $v == 0) {
                                        $value[$k] = '0';
                                    } else {
                                        $value[$k] =  $v;
                                    }
                                }
                                $m_chart[$key] = $value;

                                $m_chart[$key][] = $value[1] == 0 ? '0' : ($value[1] / $total);

                            }

                            usort($m_chart, function($a, $b) {
                                return $a[2] <= $b[2];
                            });


                            $ordered_diez = [];

                            foreach ($m_chart as $key => $value) {

                                if(mb_strpos($value[0], '#')===0) {
                                    $ordered_diez[] = $value;
                                    unset( $m_chart[$key] );
                                }
                            }

                            if(count($ordered_diez)) {

                                if( count($ordered_diez) > 1) {
                                    usort($ordered_diez, function($a, $b) {
                                        return $a[2] <= $b[2];
                                    });

                                    foreach ($ordered_diez as $key => $value) {

                                        $value[0] = mb_substr($value[0], 1);

                                        $m_chart[] = $value;
                                    }
                                } else {
                                    foreach ($ordered_diez as $key => $value) {

                                        $ordered_diez[$key][0] = mb_substr($value[0], 1);
                                    }
                                    $m_chart[] = $ordered_diez[0];
                                }

                                $m_chart = array_values($m_chart);
                            }

                            $rows_breakdown[] = $m_chart;
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
    		return $this->ShowVoxView('stats-survey', array(
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

    			// 'cats' => VoxCategory::get(),
    			// 'start' => $start,
    			// 'end' => $end,
    			// 'country' => $country,
    			// 'countryobj' => $countryobj,
    			// 'prev' => $prev,
    			// 'next' => $next,
    			// 'question' => $question,
    			// 'my_answer' => $my_answer,
    			// 'voxes' => $voxes,
    			// 'answered' => $answered,
    			// 'answer_aggregates' => $answer_aggregates,
    			// 'chart_data' => $chart_data,
    			// 'colors' => $colors,
    			'js' => [
    				'stats.js',
                    'moment.js',
                    'daterangepicker.min.js',
                    'amcharts-core.js',
                    'amcharts-maps.js',
                    'amcharts-worldLow.js',
                    'gstatic-charts-loader.js',
    			],
    			'css' => [
                    'daterangepicker.min.css',
                    'vox-stats.css'
    			],

                'canonical' => $vox->getStatsList(),
                'social_image' => $vox->getSocialImageUrl('stats'),
                'seo_title' => $seo_title,
                'seo_description' => $seo_description,
                'social_title' => $social_title,
                'social_description' => $social_description,
            ));
        }
	}

	private function prepareQuery($question_id, $dates, $options = []) {

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
            } );

        }

        if( isset($options['scale_answer_id']) ) {
            $results = $results->where('answer', $options['scale_answer_id']);
        }

        if( isset($options['scale_options']) && isset( $options['scale'] ) ) {
            //dd($scale_options, $scale);
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

            $pdf_title = strtolower(str_replace(['?', ' ', ':'], [' ', '-', ' '] ,$original_title)).'-dentavox'.mb_substr(microtime(true), 0, 10);

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

        $png_title = strtolower(str_replace(['?', ' ', ':'], [' ', '-', ' '] ,Request::input("stat_title"))).'-dentavox'.$cur_time;

        $folder = storage_path().'/app/public/png/'.$png_title;
        if(!is_dir($folder)) {
            mkdir($folder);
        }

        $picture_title = strtolower(str_replace(['?', ' ', ':'], [' ', '-', ' '] ,Request::input("stat_title"))).'-dentavox-';

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

    public function paginate($items, $perPage = 10, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}