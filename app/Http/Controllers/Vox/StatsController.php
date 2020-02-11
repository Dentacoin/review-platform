<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Models\VoxQuestion;
use App\Models\VoxCategory;
use App\Models\VoxAnswer;
use App\Models\VoxScale;
use App\Models\PageSeo;
use App\Models\Country;
use App\Models\User;
use App\Models\Dcn;
use App\Models\Vox;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use Auth;
use App;
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

        $name = !empty(Request::input('survey-search')) ? trim(Request::input('survey-search')) : null;

        if(Request::isMethod('post') && !empty($name)) {

            if (Auth::guard('admin')->user()) {

                $voxes = Vox::with('stats_main_question')
                ->where('has_stats', 1)
                ->with('translations')
                ->whereHas('translations', function ($query) use ($name) {
                    $query->where('title', 'LIKE', '%'.$name.'%');
                })->orderBy('stats_featured', 'DESC')
                ->orderBy('sort_order', 'asc')
                ->get();
            } else {
                $voxes = Vox::where('type', '!=', 'hidden')
                ->with('stats_main_question')
                ->where('has_stats', 1)
                ->with('translations')
                ->whereHas('translations', function ($query) use ($name) {
                    $query->where('title', 'LIKE', '%'.$name.'%');
                })->orderBy('stats_featured', 'DESC')
                ->orderBy('stats_featured', 'DESC')
                ->orderBy('sort_order', 'asc')
                ->get();
            }
        } else {

            if (Auth::guard('admin')->user()) {
                $voxes = Vox::with('stats_main_question')->where('has_stats', 1)->with('translations')->orderBy('stats_featured', 'DESC')->orderBy('sort_order', 'asc')->paginate(10);
            } else {
                $voxes = Vox::where('type', '!=', 'hidden')->with('stats_main_question')->where('has_stats', 1)->with('translations')->orderBy('stats_featured', 'DESC')->orderBy('sort_order', 'asc')->paginate(10);
            }
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

        if(!$vox->has_stats) {
            return redirect( getLangUrl('dental-survey-stats') );
        }

        if(!empty($this->user) && $this->user->isBanned('vox')) {
            return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
        }

        if(Request::isMethod('post')) {
        	$dates = Request::input('timeframe');
            $answer_id = Request::input('answer_id');
            $question_id = Request::input('question_id');
            $scale_answer_id = !empty(Request::input('scale_answer_id')) ? Request::input('scale_answer_id') : null;
        	$question = VoxQuestion::find($question_id);
        	$scale = Request::input('scale');
        	$type = $question->used_for_stats;
            $scale_options = Request::input('scale_options');

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
                $results = $this->prepareQuery($question_id, $dates,[
                    'dependency_answer' => $answer_id,
                    'dependency_question' => $question->stats_relation_id,
                ]);
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
                $ans_array = json_decode($question->answers);
                foreach ($ans_array as $ans) {
                    $answers[] = $question->removeAnswerTooltip($ans);
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

                $results = $results->groupBy($answerField)->selectRaw($answerField.', COUNT(*) as cnt');
                $results = $results->get();

                foreach ($answers as $key => $value) {
                    $second_chart[$value] = 0;
                }

                foreach ($results as $res) {
                    if(!isset( $answers[ $res->$answerField-1 ] )) {
                        continue;
                    }
                    $second_chart[ $answers[ $res->$answerField-1 ] ] = $res->cnt;
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
                    if(mb_strpos($value, '!')===0 || ($question->type != 'single_choice' && mb_strpos($value, '#')===0)) {
                        $answers_related[$key] = mb_substr($value, 1);
                    }
                }
                $main_chart = [];
                foreach ($answers_related as $key => $value) {
                    if(mb_strpos($value, '!')===0 || ($question->type != 'single_choice' && mb_strpos($value, '#')===0)) {
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
                    if(!isset( $answers[ $res_main->$answerField-1 ] )) {
                        continue;
                    }

                    if(!isset($main_chart[ $answers[ $res_main->$answerField-1 ] ])) {
                        $main_chart[ $answers[ $res_main->$answerField-1 ] ] = 0;
                    }
                    $main_chart[ $answers[ $res_main->$answerField-1 ] ] += $res_main->cnt;
                }

                
        		foreach ($results as $res) {
                    if(!isset( $answers[ $res->$answerField-1 ] )) {
                        continue;
                    }

        			if(!isset($second_chart[ $answers[ $res->$answerField-1 ] ])) {
        				//$main_chart[ $answers[ $res->$answerField-1 ] ] = 0;
        				$second_chart[ $answers[ $res->$answerField-1 ] ] = 0; //m
        				$third_chart[ $answers[ $res->$answerField-1 ] ] = 0; //f
        			}
        			//$main_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt;
        			if($res->gender=='f') {
        				$second_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt; //m
        			}
        			if($res->gender=='m') {
        				$third_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt; //f
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
                    if(!isset( $answers[ $res->$answerField-1 ] )) {
                        continue;
                    }

        			if(!isset($main_chart[ $answers[ $res->$answerField-1 ] ])) {
        				$main_chart[ $answers[ $res->$answerField-1 ] ] = 0;
        			}
        			$main_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt;

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
                            $second_chart[ $country->code ][ $answers[ $res->$answerField-1 ] ] = $res->cnt; //m
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
                            $second_chart[ config('vox.age_groups.'.$sv) ][$a] = 0;
                        }
                    }
                } else {
    				foreach ($age_to_group as $k => $v) {
    					$second_chart[ $v ] = [];
    					foreach ($answers as $a) {
    						$second_chart[ $v ][$a] = 0;
    					}
    				}
                }

        		foreach ($results as $res) {
                    if(!isset( $answers[ $res->$answerField-1 ] )) {
                        continue;
                    }
                    
        			if(!isset($main_chart[ $answers[ $res->$answerField-1 ] ])) {
        				$main_chart[ $answers[ $res->$answerField-1 ] ] = 0;
        			}
        			$main_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt;


        			if( $res->age ) {
	        			$second_chart[ $age_to_group[$res->age] ][ $answers[ $res->$answerField-1 ] ] = $res->cnt; //m
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
                            $second_chart[ config('vox.details_fields.'.$scale.'.values.'.$sv) ][ $a] = 0;
                        }
                    }
                } else {
            		
    				foreach ($age_to_group as $k => $v) {
                        $second_chart[ $v ] = [];
                        foreach ($answers as $a) {
                            $second_chart[ $v ][$a] = 0;
                        }
    				}
                }

                //dd( $results->toArray() );
        		foreach ($results as $res) {
                    if($res->$scale===null || !isset( $answers[ $res->$answerField-1 ] )) {
                        continue;
                    }


        			if(!isset($main_chart[ $answers[ $res->$answerField-1 ] ])) {
        				$main_chart[ $answers[ $res->$answerField-1 ] ] = 0;
        			}
        			$main_chart[ $answers[ $res->$answerField-1 ] ] += $res->cnt;


        			if( $res->$scale ) {
    	        		$second_chart[ $age_to_group[$res->$scale] ][ $answers[ $res->$answerField-1 ] ] = $res->cnt; //m
        			}
        		}
        	}

            //dd($second_chart, $this->processArray($second_chart));

            $main_chart = $this->processArray($main_chart);
            $second_chart = $this->processArray($second_chart);
            $third_chart = $this->processArray($third_chart);
            
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
                'multiple_top_answers' => !empty($question->stats_top_answers) ? intval(explode('_', $question->stats_top_answers)[1]) : null,
        	] );
        }

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

        $vox->respondentsCountryCount();

        $respondents_count = $vox->respondentsCount();
        $respondents_country_count = $vox->respondentsCountryCount();

        $blurred_stats = false;
        if (empty($this->user) && $vox->stats_questions->count() > 3) {
            $blurred_stats = true;
        }

        $launched = strtotime($vox->launched_at);
        $launched_date = date('Y-m-d',$launched);

        $seos = PageSeo::find(12);
        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
        $seo_description = str_replace(':respondents_country', $respondents_country_count, $seo_description);
        $seo_description = str_replace(':respondents', $respondents_count, $seo_description);
        $social_title = str_replace(':title', $vox->title, $seos->social_title);
        $social_description = str_replace(':description', $vox->description, $seos->social_description);

		return $this->ShowVoxView('stats-survey', array(
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
			// 'plotly' => true,
			'jscdn' => [
				'https://www.gstatic.com/charts/loader.js',
                '//www.amcharts.com/lib/4/core.js',
                '//www.amcharts.com/lib/4/maps.js',
                '//www.amcharts.com/lib/4/geodata/worldLow.js',
			],
			'js' => [
				'stats.js',
                'moment.js',
                'daterangepicker.min.js',
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

	private function prepareQuery($question_id, $dates, $options = []) {

    	$results = VoxAnswer::where('question_id', $question_id)
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
}