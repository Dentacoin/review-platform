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


class StatsController extends FrontController
{
	public function home($locale=null, $id) {
		$vox = Vox::find($id);
		if(empty($vox)) {
			return redirect( getLangUrl('/') );
		}

		$colors = [
			'#8FD694',
			'#002626',
			'#0E4749',
			'#95C623',
			'#E55812',
			'#EFE7DA',
			'#624CAB',
			'#DB5461',
		];

		$voxes = Vox::where('type', 'normal')->get();
		$answered = [];
		if($this->user) {
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
			$dategroup = ' DATE_FORMAT(`created_at`, "%Y-%m") `date`';
		} else {
			$dategroup = ' DATE_FORMAT(`created_at`, "%Y-%m-%d") `date`';
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

		$answer_res = DB::table('vox_answers')
		    ->selectRaw('question_id, answer, COUNT(*) as cnt, '.$dategroup)
		    ->where('vox_id', $vox->id)
		    ->where('created_at', '>=', $startobj)
		    ->where('created_at', '<=', $endobj);

		if($country) {
			$answer_res = $answer_res->where('country_id', $country);			
		}

		$answer_res = $answer_res->groupBy('question_id', 'date', 'answer')
		    ->get();

		$answer_data = [];
		$answer_aggregates = [];
		$chart_data = [];
		foreach ($answer_res as $res) {
			if(!isset($answer_data[$res->question_id])) {
				$answer_data[$res->question_id] = [];
			}
			if(!isset($answer_aggregates[$res->question_id])) {
				$answer_aggregates[$res->question_id] = [];
			}
			
			if(!isset( $answer_data[$res->question_id][$res->date] )) {
				$answer_data[$res->question_id][$res->date] = [];
			}
			if(!isset( $answer_aggregates[$res->question_id][$res->answer] )) {
				$answer_aggregates[$res->question_id][$res->answer] = 0;
			}

			$answer_data[$res->question_id][$res->date][$res->answer] = $res->cnt;
			$answer_aggregates[$res->question_id][$res->answer] += $res->cnt;
		}

		foreach ($vox->questions as $queston) {
			$chart_data[$queston->id] = [];
			$answers = json_decode($queston->answers, true);
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
		        	$ans_data['y'][] = isset($answer_data[$queston->id][$date][($i+1)]) ? $answer_data[$queston->id][$date][($i+1)] : 0;
		        }
				$chart_data[$queston->id][] = $ans_data;
			}
		}

		return $this->ShowVoxView('stats', array(
			'start' => $start,
			'end' => $end,
			'country' => $country,
			'countryobj' => $countryobj,
			'vox' => $vox,
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