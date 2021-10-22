<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Services\VoxService as ServicesVox;

use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\Country;
use App\Models\User;
use App\Models\Vox;

use Carbon\Carbon;

use Response;
use Auth;
use App;

class VoxController extends ApiController {

    public function doVox($slug) {

        if(request('to-lang')) {
            App::setLocale(request('to-lang'));
        }

		$vox = Vox::whereTranslationLike('slug', $slug)->first();
        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

		return ServicesVox::doVox($vox, $user, true);
	}

    public function getNextQuestion() {

        if(request('to-lang')) {
            App::setLocale(request('to-lang'));
        }
        
        $user = Auth::guard('api')->user();
        // $user = User::find(37530);
    	return ServicesVox::getNextQuestionFunction(false, $user, true, false);
    }

    public function surveyAnswer() {
        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

        $is_admin = $user->is_admin;
        $vox = request('vox_id') ? Vox::find(request('vox_id')) : null;

        return ServicesVox::surveyAnswer($vox, $user, true);
    }

	public function startOver() {
		$user = Auth::guard('api')->user();
        // $user = User::find(37530);
		return ServicesVox::startOver($user->id);
	}

	public function welcomeSurvey() {
		$first = Vox::with('questions.translations')->where('type', 'home')->first();

		$total_questions = $first->questions->count() + 3;

		$welcome = $first->convertForResponse();
		$welcome['questions'] = $first->questions->toArray();

		$birth_years = [];
		for($i=(date('Y')-18);$i>=(date('Y')-90);$i--){
            $birth_years[$i] = $i;
		}
		
		return Response::json( array(
			'vox' => $first->convertForResponse(),
			'total_questions' => $total_questions,
			'countries' => ['' => '-'] + Country::with('translations')->get()->pluck('name', 'id')->toArray(),
			'country_id' => $user->country_id ?? ServicesVox::getCountryIdByIp() ?? '',
			'birth_years' => $birth_years,
		) );
	}
//
//        $headers =  getallheaders();
//        foreach($headers as $key=>$val){
//            Log::info('headers: '.$key . ': ' . $val);
//        }
//
//        Log::info('userrr vox: '.json_encode($user));

	public function welcomeSurveyReward() {

		$first = Vox::where('type', 'home')->first();
        $has_test = request('answers');
        
        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

        $ret = [
			'success' => false,
		];

        if( $has_test ) {

            $first_question_ids = $first->questions->pluck('id')->toArray();

            if(!$user->madeTest($first->id)) {

                foreach (json_decode($has_test, true) as $q_id => $a_id) {
                    if($q_id == 'birthyear') {
                        $user->birthyear = $a_id;
                        $user->save();
                    } else if($q_id == 'gender') {
                        $user->gender = $a_id;
                        $user->save();
                    } else if($q_id == 'location') {
                        // $user->gender = $a_id;
                        // $user->save();
                    } else {
                        $answer = new VoxAnswer;
                        $answer->user_id = $user->id;
                        $answer->vox_id = $first->id;
                        $answer->question_id = $q_id;
                        $answer->answer = $a_id;
                        $answer->country_id = $user->country_id;
                        $answer->is_completed = 1;
                        $answer->is_skipped = 0;
                        $answer->save();
                    }
                }
                $reward = new DcnReward;
                $reward->user_id = $user->id;
                $reward->reference_id = $first->id;
                $reward->type = 'survey';
                $reward->reward = $first->getRewardTotal();
                $reward->platform = 'vox';

                $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                $dd = new DeviceDetector($userAgent);
                $dd->parse();

                if ($dd->isBot()) {
                    // handle bots,spiders,crawlers,...
                    $reward->device = $dd->getBot();
                } else {
                    $reward->device = $dd->getDeviceName();
                    $reward->brand = $dd->getBrandName();
                    $reward->model = $dd->getModel();
                    $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                }

                $reward->save();

                $ret = [
					'success' => true,
					'balance' => $user->getTotalBalance(),
				];
            }
        }

        return Response::json( $ret );
	}

	//for the red dots in the fixed menu
	public function dailyLimitReached() {

        $user = Auth::guard('api')->user();
        // $user = User::find(37530);
        $ret = [
			'success' => false,
		];

        if($user) {
			$daily_voxes = DcnReward::where('user_id', $user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->count();

			if($daily_voxes >= 10) {
				$ret = [
					'success' => false,
				];
			} else {
                //all_taken
				$taken = $user->filledVoxes();
                $untaken_voxes = $user->voxesTargeting();
                $untaken_voxes = $untaken_voxes->whereNotIn('id', $taken)->where('type', 'normal')->get();

                if(!$user->notRestrictedVoxesList($untaken_voxes)->count()) {
                    $ret = [
                        'success' => false,
                    ];
                } else {
                    $ret = [
                        'success' => true,
                    ];
                }
			}
        }

        return Response::json( $ret );
	}

    public function dentistRequestSurvey() {
        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

        return ServicesVox::requestSurvey($user, true);
    }

    public function recommendDentavox() {
        $user = Auth::guard('api')->user();
        // $user = User::find(37530);

    	return ServicesVox::recommendDentavox($user, true);
    }
}