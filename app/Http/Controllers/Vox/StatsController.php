<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Validator;
use Response;
use Request;
use Route;
use Hash;
use Mail;
use DB;
use Carbon\Carbon;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;
use App\Models\VoxReward;
use App\Models\VoxAnswer;
use App\Models\VoxQuestion;
use App\Models\VoxCategory;


class StatsController extends FrontController
{
	public function home($locale=null, $id=null, $question_id=null) {
        
		$vox = Vox::find($id);
		if(empty($vox)) {
			return redirect( getLangUrl('/') );
		}

		$question = null;
		$next = null;
		$prev = null;
		if(!empty($question_id)) {
			$question = $vox->questions->find($question_id);
		}
		if(empty($question)) {
			$question = $vox->questions->first();	
		}

		$next_q = VoxQuestion::where('vox_id', $id)->whereNull('is_control')->where('order', '>', $question->order)->first();
		$prev_q = VoxQuestion::where('vox_id', $id)->whereNull('is_control')->where('order', '<', $question->order)->orderBy('id', 'desc')->first();

		if ($next_q) {
			$next = $next_q->id;
		} else {
			$first_q = VoxQuestion::where('vox_id', $id)->whereNull('is_control')->where('order', '<', $question->order)->first();
			$next = $first_q->id;
		}

		if($prev_q) {
			$prev = $prev_q->id;
		} else {
			$last_q = VoxQuestion::where('vox_id', $id)->whereNull('is_control')->where('order', '>', $question->order)->orderBy('id', 'desc')->first();
			$prev = $last_q->id;
		}
		
		// $voxarr = $vox->questions->toArray();
		// foreach ($voxarr as $k => $v) {
		// 	if($v['id'] == $question->id) {
		// 		$prev = $k==0 ? $voxarr[ count($voxarr)-1 ]['id'] : $voxarr[ $k-1 ]['id'];
		// 		$next = $k== count($voxarr)-1 ? $voxarr[0]['id'] : $voxarr[ $k+1 ]['id'];
		// 		break;
		// 	}
		// }


		$colors = [
			'#8FD694',
			'#002626',
			'#0E4749',
			'#95C623',
			'#E55812',
			//'#EFE7DA',
			'#624CAB',
			'#DB5461',
			'#000000',
			'#FF0000',
			'#00FF00',
			'#0000FF',
		];

		$voxes = Vox::where('type', 'normal')->get();
		$voxes = $voxes->reject(function($element) use($vox) {
		    return $element->id == $vox->id;
		});
		$voxes->prepend($vox);

		$answered = [];
		if($this->user) {

	        if($this->user->isBanned('vox')) {
	            return redirect(getLangUrl('profile/bans'));
	        }

			$answered = VoxReward::where('user_id', $this->user->id)->get()->pluck('vox_id')->toArray();
		}


		$start = Request::input('start');
		if($start) {
			try {
				$startobj = new Carbon($start);			
			} catch( \Exception $e) {}			
		}
		if(empty($startobj)) {
			$startobj = Carbon::now()->subDays(14);
			$start = $startobj->format('d-m-Y'); 			
		}


		$end = Request::input('end');
		if($end) {
			try {
				$endobj = new Carbon($end.' 23:59:59');			
			} catch( \Exception $e) {}
		}
		if(empty($endobj)) {
			$endobj = new Carbon();
			$end = $endobj->format('d-m-Y'); 
		}
		
		$country = Request::input('country');
		$countryobj = Country::find($country);
		if(empty($countryobj)) {
			$country = null;
		}

		$diff = $endobj->diffInDays($startobj);
		$dates = [];

		if($diff>31) {
			$dategroup = ' DATE_FORMAT(`vox_answers`.`created_at`, "%Y-%m") `date`';
		} else {
			$dategroup = ' DATE_FORMAT(`vox_answers`.`created_at`, "%Y-%m-%d") `date`';
		}

		$curdate = $startobj->copy();
		while($curdate->lte($endobj)) {
			$dates[] = $curdate->format( $diff>31 ? 'Y-m' : 'Y-m-d');
			if($diff>31) {
				$curdate->addMonth();
			} else {
				$curdate->addDay();
			}
		}

		// $allrewards = VoxReward::where('vox_id', $vox->id)->get();
		// foreach ($allrewards as $r) {
		// 	VoxAnswer::where('vox_id', $vox->id)->where('user_id', $r->user_id)->update(['is_completed' => true]);
		// }

		$t = microtime(true);

		$answer_res = DB::table('vox_answers')
		->join('users', 'users.id', '=', 'vox_answers.user_id')
	    ->selectRaw('answer, COUNT(*) as cnt, '.$dategroup)
	    ->where('vox_answers.vox_id', $vox->id)
	    ->where('question_id', $question->id)
	    ->where('is_completed', true)
	    ->where('vox_answers.created_at', '>=', $startobj)
	    ->where('vox_answers.created_at', '<=', $endobj)
	    ->whereNull('users.deleted_at');

		if($country) {
			$answer_res = $answer_res->where('country_id', $country);			
		}

		$answer_res = $answer_res->groupBy('date', 'answer')
		    ->get();

		//dd( microtime(true) - $t);

		$answer_data = [];
		$answer_aggregates = [];
		$chart_data = [];
		foreach ($answer_res as $res) {
			if(!isset( $answer_data[$res->date] )) {
				$answer_data[$res->date] = [];
			}
			if(!isset( $answer_aggregates[$res->answer] )) {
				$answer_aggregates[$res->answer] = 0;
			}

			$answer_data[$res->date][$res->answer] = $res->cnt;
			$answer_aggregates[$res->answer] += $res->cnt;
		}

		$chart_data = [];
		$answers = json_decode($question->answers, true);
		foreach ($answers as $i => $ans) {
			$ans_data = [
	            'x' => [],
	            'y' => [],
	            'type' => 'scatter',
	            'name' => $ans,
	            'line' => [
	            	'color' =>  $colors[$i]
	            ]
	        ];
	        foreach ($dates as $date) {
	        	$ans_data['x'][] = $date;
	        	$ans_data['y'][] = isset($answer_data[$date][($i+1)]) ? $answer_data[$date][($i+1)] : 0;
	        }
			$chart_data[] = $ans_data;
		}

		$my_answer = null;
		if($this->user) {
			$my_answer = VoxAnswer::where([
				['user_id', $this->user->id],
				['question_id', $question->id],
			])->first();
			if(!empty($my_answer)) {
				$my_answer = $my_answer->answer;
			}
			//dd($my_answer);
		}

		return $this->ShowVoxView('stats', array(
			'cats' => VoxCategory::get(),
			'start' => $start,
			'end' => $end,
			'country' => $country,
			'countryobj' => $countryobj,
			'vox' => $vox,
			'prev' => $prev,
			'next' => $next,
			'question' => $question,
			'my_answer' => $my_answer,
			'voxes' => $voxes,
			'answered' => $answered,
			'answer_aggregates' => $answer_aggregates,
			'chart_data' => $chart_data,
			'colors' => $colors,
			'plotly' => true,
			'js' => [
				'stats.js',
			],
            'seo_title' => trans('vox.seo.stats.title', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
            'seo_description' => trans('vox.seo.stats.description', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
            'social_title' => trans('vox.social.stats.title', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
            'social_description' => trans('vox.social.stats.description', [
                'title' => $vox->title,
                'description' => $vox->description
            ]),
        ));
	}
}