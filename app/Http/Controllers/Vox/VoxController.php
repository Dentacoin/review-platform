<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Services\VoxService as ServicesVox;

use App\Models\VoxCrossCheck;
use App\Models\VoxQuestion;
use App\Models\WhitelistIp;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\VoxRelated;
use App\Models\VoxAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\PageSeo;
use App\Models\Reward;
use App\Models\VpnIp;
use App\Models\Admin;
use App\Models\Email;
use App\Models\User;
use App\Models\Vox;
use App\Models\Dcn;

use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Session;
use Route;
use Hash;
use Auth;
use App;
use Mail;
use DB;

class VoxController extends FrontController {

	/**
     * Single vox page by slug
     */
	public function vox($locale=null, $slug) {
		$vox = Vox::whereTranslationLike('slug', $slug)->with('questions')->first();

		if(Request::isMethod('post')) {
			if (empty($vox) || $vox->id == 11) {
				$ret['success'] = false;

    			return Response::json( $ret );
			}

			return ServicesVox::surveyAnswer($vox, $this->user, false);
			// return $this->surveyAnswer($locale, $vox);
		} else {
			$doVox = ServicesVox::doVox($vox, $this->user, false);

			if(isset($doVox['view'])) {
				if($doVox['view'] == 'vox') {
					$this->current_page = 'questionnaire';
				}
				return $this->ShowVoxView($doVox['view'], $doVox['params']);

			} else if( isset($doVox['url'])) {
				return redirect($doVox['url']);
			}

			// return $this->doVox($vox);
		}
	}

	/**
     * Single vox page for not logged users
     * Single vox page for taken vox
     * Single vox page for restricted vox
     */
	// public function doVox($vox) {

	// 	if (empty($vox) || $vox->id == 11) {
	// 		return redirect( getLangUrl('page-not-found') );
	// 	}

	// 	ini_set('max_execution_time', 0);
 //        set_time_limit(0);
 //        ini_set('memory_limit','1024M');

 //        //to log Dobrina, because of problems
	// 	if(empty($this->user) && !empty($this->admin) && ($this->admin->id) == 11 && !empty($this->admin->user_id)) {
	// 		$adm = User::find($this->admin->user_id);

	//         if(!empty($adm)) {
	//             Auth::login($adm, true);
	//         }
	//         return redirect(url()->current().'?testmode=1');
	//     }

	//     //vox for not logged users
 //        if(!$this->user) {

 //            $seos = PageSeo::find(16);
 //            $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
 //            $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
 //            $social_title = str_replace(':title', $vox->title, $seos->social_title);
 //            $social_description = str_replace(':description', $vox->description, $seos->social_description);

 //            return $this->ShowVoxView('vox-public', array(
	// 			'vox' => $vox,
	// 			'custom_body_class' => 'vox-public',
	// 			'js' => [
	// 				'all-surveys.js',
	// 			],
	// 			'css' => [
	// 				'vox-public-vox.css',
	// 			],
	//             'canonical' => $vox->getLink(),
	//             'social_image' => $vox->getSocialImageUrl('survey'),
	//             'seo_title' => $seo_title,
	//             'seo_description' => $seo_description,
	//             'social_title' => $social_title,
	//             'social_description' => $social_description,
	//         ));
 //        }

 //        //when the user is banned
 //        if($this->user->isBanned('vox')) {
 //            return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
 //        }

 //        //when the user is logged from bad IP
	// 	if(!$this->user->is_dentist && $this->user->platform != 'external' && $this->user->loggedFromBadIp()) {
	// 		$ul = new UserLogin;
 //            $ul->user_id = $this->user->id;
 //            $ul->ip = User::getRealIp();
 //            $ul->platform = 'vox';
 //            $ul->country = \GeoIP::getLocation()->country;

 //            $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
 //            $dd = new DeviceDetector($userAgent);
 //            $dd->parse();

 //            if ($dd->isBot()) {
 //                // handle bots,spiders,crawlers,...
 //                $ul->device = $dd->getBot();
 //            } else {
 //                $ul->device = $dd->getDeviceName();
 //                $ul->brand = $dd->getBrandName();
 //                $ul->model = $dd->getModel();
 //                $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
 //            }
            
 //            $ul->save();
            
 //            $u_id = $this->user->id;

 //            $action = new UserAction;
 //            $action->user_id = $u_id;
 //            $action->action = 'bad_ip';
 //            $action->reason = 'Automatically - Bad IP ( vox questionnaire )';
 //            $action->actioned_at = Carbon::now();
 //            $action->save();

 //            Auth::guard('web')->user()->logoutActions();
 //            Auth::guard('web')->user()->removeTokens();
 //            Auth::guard('web')->logout();
            
	// 		return redirect( 'https://account.dentacoin.com/account-on-hold?platform=dentavox&on-hold-type=bad_ip&key='.urlencode(User::encrypt($u_id)) );
	// 	}

 //        $admin_ids = ['65003']; //Dobrina
 //        $isAdmin = Auth::guard('admin')->user() || in_array($this->user->id, $admin_ids);

 //        if (request()->has('testmode')) {
 //            if(request('testmode')) {
 //                $ses = [
	// 	            'testmode' => true
	// 	        ];
 //            } else {
 //                $ses = [
	// 	            'testmode' => false
	// 	        ];
 //            }
 //            session($ses);
	// 	}
	// 	$testmode = session('testmode') && $isAdmin;

 //        $taken = $this->user->filledVoxes();

	// 	if((!$isAdmin && $vox->type=='hidden') || empty($vox) || !in_array($this->user->status, config('dentist-statuses.approved')) ) {
	// 		return redirect( getLangUrl('page-not-found') );

	// 	} else if( $this->user->madeTest($vox->id) && !(Request::input('goback') && $testmode) ) { //because of GoBack

	// 		$related_voxes = [];
	// 		$related_voxes_ids = [];
	// 		if ($vox->related->isNotEmpty()) {
	// 			foreach ($vox->related as $r) {
	// 				if (!in_array($r->related_vox_id, $taken)) {
	// 					$related_voxes[] = Vox::find($r->related_vox_id);
	// 					$related_voxes_ids[] = $r->related_vox_id;
	// 				}
	// 			}
	// 		}

	// 		$suggested_voxes = $this->user->voxesTargeting()->where('type', 'normal')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

	// 		$suggested_voxes = $this->user->notRestrictedVoxesList($suggested_voxes);

	//         $seos = PageSeo::find(17);

	//         $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
	//         $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
	//         $social_title = str_replace(':title', $vox->title, $seos->social_title);
	//         $social_description = str_replace(':title', $vox->description, $seos->social_description);

	// 		return $this->showVoxView('vox-taken', [
	// 			'vox' => $vox,
	// 			'related_voxes' => $related_voxes,
	//             'suggested_voxes' => $suggested_voxes,
	//             'seo_title' => $seo_title,
	//             'seo_description' => $seo_description,
	//             'social_title' => $social_title,
	//             'social_description' => $social_description,
 //            	'canonical' => $vox->getLink(),
 //            	'social_image' => $vox->getSocialImageUrl('survey'),
	// 			'js' => [
	// 				'taken-vox.js',
 //                    'swiper.min.js'
	// 			],
 //                'css' => [
 //                    'swiper.min.css'
 //                ],
	// 		]);
	// 	}

	// 	if (!$testmode) {

	// 		if ($this->user->isVoxRestricted($vox) || $vox->voxCountryRestricted($this->user)) {
	// 			$related_voxes = [];
	// 			$related_voxes_ids = [];
	// 			if ($vox->related->isNotEmpty()) {
	// 				foreach ($vox->related as $r) {
	// 					if (!in_array($r->related_vox_id, $taken)) {
	// 						$related_voxes[] = Vox::find($r->related_vox_id);
	// 						$related_voxes_ids[] = $r->related_vox_id;
	// 					}
	// 				}
	// 			}

	// 			$suggested_voxes = $this->user->voxesTargeting()->where('type', 'normal')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

	// 			$suggested_voxes = $this->user->notRestrictedVoxesList($suggested_voxes);

	// 			if ($this->user->isVoxRestricted($vox)) {
	// 				$res_desc = trans('vox.page.restricted-questionnaire.description-target');
	// 			} else {
	// 				$res_desc = trans('vox.page.restricted-questionnaire.description-limit');
	// 			}

	// 			$seos = PageSeo::find(18);

	// 	        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
	// 	        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
	// 	        $social_title = str_replace(':title', $vox->title, $seos->social_title);
	// 	        $social_description = str_replace(':title', $vox->description, $seos->social_description);

	// 			return $this->showVoxView('vox-restricted', [
	// 				'res_desc' => $res_desc,
	// 				'vox' => $vox,
	// 				'related_voxes' => $related_voxes,
	// 	            'suggested_voxes' => $suggested_voxes,
	// 	            'seo_title' => $seo_title,
	// 	            'seo_description' => $seo_description,
	// 	            'social_title' => $social_title,
	// 	            'social_description' => $social_description,
	// 	            'canonical' => $vox->getLink(),
	// 	            'social_image' => $vox->getSocialImageUrl('survey'),
	// 				'js' => [
	// 					'taken-vox.js',
 //                    	'swiper.min.js'
	// 				],
	// 				'css' => [
 //                    	'swiper.min.css'
	// 				],
	// 			]);
	// 		}

	// 		$daily_voxes = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->count();

	// 		if($daily_voxes >= 10) {
	// 			$last_vox = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->orderBy('id', 'desc')->first();

	// 			$time_left = '';

	//             $now = Carbon::now()->subDays(1);
	//             $time_left = $last_vox->created_at->diffInHours($now).':'.
	//             str_pad($last_vox->created_at->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
	//             str_pad($last_vox->created_at->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);

	// 			$seos = PageSeo::find(19);

	// 	        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
	// 	        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
	// 	        $social_title = str_replace(':title', $vox->title, $seos->social_title);
	// 	        $social_description = str_replace(':description', $vox->description, $seos->social_description);

	// 			return $this->ShowVoxView('vox-limit-reached', array(
	// 				'vox' => $vox,
	// 				'time_left' => $time_left,					
	// 	            'canonical' => $vox->getLink(),
	// 	            'social_image' => $vox->getSocialImageUrl('survey'),
	// 	            'seo_title' => $seo_title,
	// 	            'seo_description' => $seo_description,
	// 	            'social_title' => $social_title,
	// 	            'social_description' => $social_description,
	// 			));
	// 		}
	// 	}

	// 	$welcome_vox = '';
	// 	$welcome_vox_question_ids = [];

	// 	if(!session('made-welcome-test')) {
	// 		session([
	// 			'made-welcome-test' => $this->user->madeTest(11),
	// 		]);
	// 	}

	// 	if (!session('made-welcome-test')) {
	// 		$welcome_vox = Vox::with('questions')->find(11);
	// 		$welcome_vox_question_ids = $welcome_vox->questions->pluck('id')->toArray();
	// 	}

	// 	$vox_questions = $vox->questions;
	// 	$crossCheckParams = ServicesVox::getCrossChecks($vox_questions);
 //    	$cross_checks = $crossCheckParams['cross_checks'];
 //    	$cross_checks_references = $crossCheckParams['cross_checks_references'];

	// 	$list = VoxAnswer::select('vox_id', 'question_id', 'user_id', 'answer')
	// 	->where('vox_id', $vox->id)
	// 	->with('question')
	// 	->where('user_id', $this->user->id)
	// 	->orderBy('id', 'ASC')
	// 	->get();

	// 	$answered = $list->count();

	// 	$answered_without_skip_count = 0;
	// 	$answered_without_skip = [];
	// 	foreach ($list as $l) {
	// 		if(!isset( $answered_without_skip[$l->question_id] ) && $l->question && $l->answer > 0 || $l->question->type == 'number' || $l->question->cross_check == 'birthyear' || $l->question->cross_check == 'household_children') {
	// 			$answered_without_skip[$l->question_id] = ['1']; //3
	// 			$answered_without_skip_count++;
	// 		}
	// 	}

	// 	if($testmode) {
	// 		if(Request::input('goback')) {
	// 			$q_id = ServicesVox::goBack($this->user->id, $answered, $list, $vox);

	// 			if(!empty(VoxQuestion::find($q_id))) {

	// 				$vq = VoxQuestion::where('vox_id', $vox->id)->where('order', VoxQuestion::find($q_id)->order-1)->first();
	// 				if(!empty($vq)) {
	// 					$quest_id = $vq->id;
	// 				} else {
	// 					$quest_id = $q_id;
	// 				}
	// 			} else {
	// 				$quest_id = $q_id;
	// 			}

	//             return redirect( $vox->getLink().'?testmode=1&q-id='.$quest_id );
	// 		}

	// 		if(Request::input('start-from')) {
	// 			$q_id = $this->testAnswers($answered, Request::input('start-from'), $vox);

	// 			if(!empty(VoxQuestion::find($q_id))) {

	// 				$vq = VoxQuestion::where('vox_id', $vox->id)->where('order', VoxQuestion::find($q_id)->order-1)->first();
	// 				if(!empty($vq)) {
	// 					$quest_id = $vq->id;
	// 				} else {
	// 					$quest_id = $q_id;
	// 				}
	// 			} else {
	// 				$quest_id = $q_id;
	// 			}

	//             return redirect( $vox->getLink().'?testmode=1&q-id='.$quest_id );
	// 		}
	// 	}

	// 	if(!session('scales')) {
	// 		$slist = VoxScale::get();
	// 		$scales = [];
	// 		foreach ($slist as $sitem) {
	// 			$scales[$sitem->id] = $sitem;
	// 		}

	// 		session([
	// 			'scales' => $scales,
	// 		]);
	// 	}

 //        $first_question_num = 0;
 //    	if (!empty($welcome_vox)) {
 //    		$first_question_num++;
 //    	} else {
 //        	$first_question_num = $answered + 1;
 //    	}

 //        $total_questions = $vox_questions->count();

 //        if (!$this->user->birthyear) {
 //        	$total_questions++;
 //        }
 //        if (!$this->user->country_id) {
 //        	$total_questions++;
 //        }
 //        if (!$this->user->gender) {
 //        	$total_questions++;
 //        }

 //        foreach (config('vox.details_fields') as $key => $value) {
 //        	if($this->user->$key==null) {
 //        		$total_questions++;		
 //        	}
 //        }

 //        if (!empty($welcome_vox)) {
	//         foreach ($welcome_vox->questions as $key => $value) {
	//         	$total_questions++;		
	//         }
 //        }

	// 	$welcomerules = !session('vox-welcome');
	// 	if($welcomerules) {
 //        	session([
 //        		'vox-welcome' => true
 //        	]);
	// 	}

 //        $done_all = false;
 //        if (Vox::where('type', 'normal')->count() <= count($taken)) {
 //        	$done_all = true;
 //        }

	// 	$related_voxes = [];
	// 	$related_voxes_ids = [];
	// 	if ($vox->related->isNotEmpty()) {
	// 		foreach ($vox->related as $r) {
	// 			if (!in_array($r->related_vox_id, $taken)) {
	// 				$related_voxes_ids[] = $r->related_vox_id;
	// 			}
	// 		}

	// 		if (!empty($related_voxes_ids)) {
	// 			foreach(Vox::with('translations')->with('categories.category')->with('categories.category.translations')->whereIn('id', $related_voxes_ids)->get() as $rv) {
	// 				$related_voxes[] = $rv;
	// 			}
	// 		}
	// 	}
	// 	$suggested_voxes = Vox::where('type', 'normal')->with('translations')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

	// 	$suggested_voxes = $this->user->notRestrictedVoxesList($suggested_voxes);

	// 	$seos = PageSeo::find(15);
 //        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
 //        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
 //        $social_title = str_replace(':title', $vox->title, $seos->social_title);
 //        $social_description = str_replace(':description', $vox->description, $seos->social_description);

	// 	$this->current_page = 'questionnaire';

	// 	return $this->ShowVoxView('vox', array(
	// 		'welcome_vox' => $welcome_vox,
	// 		'related_voxes' => $related_voxes,
 //            'suggested_voxes' => $suggested_voxes,
	// 		'cross_checks' => $cross_checks,
	// 		'cross_checks_references' => $cross_checks_references,
	// 		'welcomerules' => $welcomerules,
	// 		'not_bot' => $testmode || session('not_not-'.$vox->id),
	// 		'details_fields' => config('vox.details_fields'),
	// 		'vox' => $vox,
	// 		'scales' => session('scales'),
	// 		'answered' => $answered,
	// 		'real_questions' => $vox_questions->count(),
	// 		'total_questions' => $total_questions,
	// 		'first_question_num' => $first_question_num,
	// 		'answered_without_skip_count' => $answered_without_skip_count,
	// 		'js' => [
	// 			'all-surveys.js',
	// 			'vox-new.js',
	// 			'../js/lightbox.js',
	// 			'../js/jquery-ui.min.js',
	// 			'../js/jquery-ui-touch.min.js',
 //        		'flickity.pkgd.min.js',
 //                'swiper.min.js'
	// 		],
	// 		'css' => [
	// 			'vox-questionnaries.css',
	// 			'lightbox.css',
 //        		'flickity.min.css',
 //                'swiper.min.css'
	// 		],
 //            'canonical' => $vox->getLink(),
 //            'social_image' => $vox->getSocialImageUrl('survey'),
 //            'seo_title' => $seo_title,
 //            'seo_description' => $seo_description,
 //            'social_title' => $social_title,
 //            'social_description' => $social_description,
 //            'done_all' => $done_all,
 //            'testmode' => $testmode,
 //            'isAdmin' => $isAdmin,
 //        ));
	// }

	/**
     * Answer vox
     */
	// public function surveyAnswer($locale=null, $vox) {

 //    	$ret['success'] = false;

 //        if(empty($this->user) || empty($vox) || !in_array($this->user->status, config('dentist-statuses.approved'))) {
	// 		return Response::json( $ret );
 //        }

 //        $admin_ids = ['65003'];
 //        $isAdmin = Auth::guard('admin')->user() || in_array($this->user->id, $admin_ids);
	// 	$testmode = session('testmode') && $isAdmin;

	// 	if(!$testmode) {
	// 		if($vox->type=='hidden' ) {
	//         	return Response::json( $ret );
	//         }

	// 		if(!$this->user->is_dentist && !empty(VpnIp::where('ip', User::getRealIp())->first())) {
	// 			$ret['is_vpn'] = true;
	// 			return Response::json( $ret );
	// 		}

	// 		if($this->user->isVoxRestricted($vox)) {
	// 			$ret['restricted'] = true;

	// 			return Response::json( $ret );
	// 		}
	// 	}

	// 	$welcome_vox = '';
	// 	$welcome_vox_question_ids = [];

	// 	if(!session('made-welcome-test')) {
	// 		session([
	// 			'made-welcome-test' => $this->user->madeTest(11),
	// 		]);
	// 	}

	// 	if (!session('made-welcome-test')) {
	// 		$welcome_vox = Vox::with('questions')->find(11);
	// 		$welcome_vox_question_ids = $welcome_vox->questions->pluck('id')->toArray();
	// 	}

 //    	$crossCheckParams = ServicesVox::getCrossChecks($vox->questions);
 //    	$cross_checks = $crossCheckParams['cross_checks'];
 //    	$cross_checks_references = $crossCheckParams['cross_checks_references'];

	// 	$list = VoxAnswer::select('vox_id', 'question_id', 'user_id', 'answer')
	// 	->where('vox_id', $vox->id)
	// 	// ->with('question')
	// 	->where('user_id', $this->user->id)
	// 	->orderBy('id', 'ASC')
	// 	->get();

	// 	$answered = [];
	// 	foreach ($list as $l) {
	// 		if(!isset( $answered[$l->question_id] )) {
	// 			$answered[$l->question_id] = $l->answer; //3
	// 		} else {
	// 			if(!is_array($answered[$l->question_id])) {
	// 				$answered[$l->question_id] = [ $answered[$l->question_id] ]; // [3]
	// 			}
	// 			$answered[$l->question_id][] = $l->answer; // [3,5,7]
	// 		}
	// 	}

	// 	$not_bot = $testmode || session('not_not-'.$vox->id);

	// 	if(!session('scales')) {
	// 		$slist = VoxScale::get();
	// 		$scales = [];
	// 		foreach ($slist as $sitem) {
	// 			$scales[$sitem->id] = $sitem;
	// 		}

	// 		session([
	// 			'scales' => $scales,
	// 		]);
	// 	}

 //    	if(Request::input('captcha')) {
 //            $captcha = false;
 //            $cpost = [
 //                'secret' => env('CAPTCHA_SECRET'),
 //                'response' => Request::input('captcha'),
 //                'remoteip' => User::getRealIp()
 //            ];
 //            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
 //            curl_setopt($ch, CURLOPT_HEADER, 0);
 //            curl_setopt ($ch, CURLOPT_POST, 1);
 //            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
 //            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
 //            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
 //            $response = curl_exec($ch);
 //            curl_close($ch);
 //            if($response) {
 //                $api_response = json_decode($response, true);

 //                if(!empty($api_response['success'])) {
 //                    $captcha = true;
 //                }
 //            }

 //            if(!$captcha) {
 //            	$ret['captcha_error'] = true;

 //    			return Response::json( $ret );
 //            } else {
 //            	session([
 //            		'not_not-'.$vox->id => true,
 //            		'reward-for-'.$vox->id => $vox->getRewardTotal()
 //            	]);
 //            	$ret['vox_id'] = $vox->id;
 //            }
 //    	}

 //    	$ret = [
 //    		'success' => true,
 //    	];

 //    	$q = Request::input('question');

 //    	if( !isset( $answered[$q] ) && $not_bot ) {

	// 		$type = Request::input('type');
 //        	$found = isset( config('vox.details_fields')[$type] ) || in_array($type, ['gender-question', 'birthyear-question', 'location-question']) ? true : false;

 //        	foreach ($vox->questions as $question) {
 //        		if($question->id == $q) {
 //        			$found = $question;
 //        			break;
 //        		}
 //        	}
 //        	if (!empty($welcome_vox)) {
	//         	foreach ($welcome_vox->questions as $question) {
	//         		if($question->id == $q) {
	//         			$found = $question;
	//         			break;
	//         		}
	//         	}
 //        	}

 //        	if($found) {
 //        		$valid = false;

 //        		$answer_count = in_array($type, ['multiple', 'rank', 'scale', 'single']) ? count($question->vox_scale_id && !empty(session('scales')[$question->vox_scale_id]) ? explode(',', session('scales')[$question->vox_scale_id]->answers) : json_decode($question->answers, true) ) : 0;

 //        		if ($type == 'skip') {
 //        			$valid = true;
 //        			$a = 0;

 //        		} else if($type == 'previous') {
 //        			$valid = true;
 //        			$a = Request::input('answer');
 //        		} else if ( isset( config('vox.details_fields')[$type] ) ) {

 //        			$should_reward = false;
 //        			if($this->user->$type===null) {
 //        				$should_reward = true;
 //        			}

 //        			$this->user->$type = Request::input('answer');
 //        			$this->user->save();
 //        			if( isset( config('vox.stats_scales')[$type] ) ) {
 //        				VoxAnswer::where('user_id', $this->user->id)->update([
	//         				$type => Request::input('answer')
	//         			]);
 //        			}
 //        			$valid = true;
 //        			$a = Request::input('answer');

 //        			if( $should_reward ) {

	//         			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
	//         				array(
	//         					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
	//         				))
	//         			);
 //        			}

 //        		} else if ($type == 'location-question') {

 //        			if($this->user->country_id===null) {
	//         			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
	//         				array(
	//         					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
	//         				))
	//         			);
 //        			}
 //        			//answer = 71,2312
 //        			$country_id = Request::input('answer');
 //        			$this->user->country_id = $country_id;
 //        			VoxAnswer::where('user_id', $this->user->id)->update([
 //        				'country_id' => $country_id
 //        			]);
 //        			$this->user->save();

 //        			$a = $country_id;
 //        			$valid = true;
        		
 //        		} else if ($type == 'birthyear-question') {

 //        			if($this->user->birthyear===null || $this->user->birthyear===0) {
	//         			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
	//         				array(
	//         					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
	//         				))
	//         			);

	//         			$this->user->birthyear = Request::input('answer');
	//         			$this->user->save();
 //        			}

 //        			$agegroup = $this->getAgeGroup(Request::input('answer'));

 //        			VoxAnswer::where('user_id', $this->user->id)->update([
 //        				'age' => $agegroup
 //        			]);

 //        			$valid = true;
 //        			$a = Request::input('answer');

 //        		} else if ($type == 'gender-question') {

 //        			if($this->user->gender===null) {
	//         			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
	//         				array(
	//         					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
	//         				))
	//         			);
 //        			}
 //        			$this->user->gender = Request::input('answer');
 //        			$this->user->save();
 //        			VoxAnswer::where('user_id', $this->user->id)->update([
 //        				'gender' => Request::input('answer')
 //        			]);
 //        			$valid = true;
 //        			$a = Request::input('answer');

 //        		} else if ($type == 'multiple' || $type == 'scale' || $type == 'rank') {

 //        			$valid = true;
 //        			$a = Request::input('answer');
 //        			foreach ($a as $k => $value) {
 //        				if (!($value>=1 && $value<=$answer_count)) {
 //        					$valid = false; 
 //        					break;
 //        				}
 //        			}
        			
 //        		} else if ($type == 'single') {
 //    				$a = intval(Request::input('answer'));
 //    				$valid = $a>=1 && $a<=$answer_count;

 //        		} else if ($type == 'number') {
        			
	// 		    	$cur_question_collection = $vox->questions->filter(function ($value, $key) use ($q) {
	// 				    return $value->id == $q;
	// 				});

 //        			$cur_question = $cur_question_collection->first(); //sort/filter
 //        			$min_num = intval(explode(':',$cur_question->number_limit)[0]);
 //        			$max_num = intval(explode(':',$cur_question->number_limit)[1]);
 //    				$a = intval(Request::input('answer'));
 //    				$valid = $a>=$min_num && $a<=$max_num;
 //        		}

 //        		if( $valid ) {
 //        			// VoxAnswer::where('user_id', $this->user->id )->where('vox_id',$vox->id )->where('question_id', $q)->delete();

 //        			$is_scam = false;

	// 		        if($question->is_control) {

	// 		        	if ($question->is_control == '-1') {
	//         				if($type == 'single') {
	// 			        		$is_scam = end($answered) != $a;
	// 			        	} else if($type == 'multiple') {
	// 			        		$end_answered = [];

	// 			        		if (!is_array(end($answered))) {
	// 			        			$end_answered[] = end($answered);
	// 			        		} else {
	// 			        			$end_answered = end($answered);
	// 			        		}
	// 			        		$is_scam = !empty(array_diff( $end_answered, $a ));
	// 			        	}
	// 		        	} else {
	//         				if($type == 'single') {
	// 		        			$is_scam = $question->is_control!=$a;
	// 			        	} else if($type == 'multiple') {
	// 			        		$is_scam = !empty(array_diff( explode(',', $question->is_control), $a ));
	// 			        	}
	// 		        	}
	// 		        }

	// 	        	if($is_scam && !$testmode && !$this->user->is_partner) {
		        		
	// 	        		$wrongs = intval(session('wrongs'));
	// 	        		$wrongs++;
	// 	            	session([
	// 	            		'wrongs' => $wrongs
	// 	            	]);

 //        				$ret['wrong'] = true;
 //        				$prev_bans = $this->user->getPrevBansCount('vox', 'mistakes');

 //        				if($wrongs==1 || ($wrongs==2 && !$prev_bans) ) {
 //        					$ret['warning'] = true;
 //        					$ret['img'] = url('new-vox-img/mistakes'.($prev_bans+1).'.png');
 //        					$titles = [
 //        						trans('vox.page.bans.warning-mistakes-title-1'),
 //        						trans('vox.page.bans.warning-mistakes-title-2'),
 //        						trans('vox.page.bans.warning-mistakes-title-3'),
 //        						trans('vox.page.bans.warning-mistakes-title-4'),
	//         				];
 //        					$contents = [
 //        						trans('vox.page.bans.warning-mistakes-content-1'),
 //        						trans('vox.page.bans.warning-mistakes-content-2'),
 //        						trans('vox.page.bans.warning-mistakes-content-3'),
 //        						trans('vox.page.bans.warning-mistakes-content-4'),
 //        					];
 //        					if( $wrongs==2 && !$prev_bans ) {
 //        						$ret['zman'] = url('new-vox-img/mistake2.png');
 //        						$ret['title'] = trans('vox.page.bans.warning-mistakes-title-1-second');
 //        						$ret['content'] = trans('vox.page.bans.warning-mistakes-content-1-second');
 //        					} else {
 //        						$ret['zman'] = url('new-vox-img/mistake1.png');
 //        						$ret['title'] = $titles[$prev_bans];
	//         					$ret['content'] = $contents[$prev_bans];
 //        					}

 //        					if( $wrongs==1 && !$prev_bans ) {
 //        						$ret['action'] = 'roll-back';
 //        						$ret['go_back'] = ServicesVox::goBack($this->user->id, $answered, $list, $vox);
 //        					} else {
 //        						$ret['action'] = 'start-over';
 //        						$ret['go_back'] = $vox->questions->first()->id;
	// 							VoxAnswer::where('vox_id', $vox->id)
	// 							->where('user_id', $this->user->id)
	// 							->delete();
 //        					}
 //        				} else {
	// 		            	session([
	// 		            		'wrongs' => null
	// 		            	]);
 //        					$ban = $this->user->banUser('vox', 'mistakes', $vox->id);
 //        					$ret['ban'] = true;
 //        					$ret['ban_duration'] = $ban['days'];
 //        					$ret['ban_times'] = $ban['times'];
 //        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
 //        					$titles = [
 //        						trans('vox.page.bans.ban-mistakes-title-1'),
 //        						trans('vox.page.bans.ban-mistakes-title-2'),
 //        						trans('vox.page.bans.ban-mistakes-title-3'),
 //        						trans('vox.page.bans.ban-mistakes-title-4', [
 //        							'name' => $this->user->getNames()
 //        						]),
 //        					];
 //        					$ret['title'] = $titles[$prev_bans];
 //        					$contents = [
 //        						trans('vox.page.bans.ban-mistakes-content-1'),
 //        						trans('vox.page.bans.ban-mistakes-content-2'),
 //        						trans('vox.page.bans.ban-mistakes-content-3'),
 //        						trans('vox.page.bans.ban-mistakes-content-4'),
 //        					];
 //        					$ret['content'] = $contents[$prev_bans];

 //        					//Delete all answers
	// 						VoxAnswer::where('vox_id', $vox->id)
	// 						->where('user_id', $this->user->id)
	// 						->delete();
 //        				}
	// 	        	} else {

	// 	        		if($type == 'skip') {
	//         				$answer = new VoxAnswer;
	// 				        $answer->user_id = $this->user->id;
	// 				        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 				        $answer->question_id = $q;
	// 				        $answer->answer = 0;
	// 				        $answer->is_skipped = true;
	// 				        $answer->country_id = $this->user->country_id;
					        
	// 				        if($testmode) {
	// 				        	$answer->is_admin = true;
	// 				        }
	// 				        $answer->save();
	// 				        $answered[$q] = 0;
					        
	//         			} else if($type == 'previous') {
	//         				$answer = new VoxAnswer;
	// 				        $answer->user_id = $this->user->id;
	// 				        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 				        $answer->question_id = $q;
	// 			        	$answer->answer = $a;
	// 			        	$this->setupAnswerStats($answer);
	// 				        $answer->country_id = $this->user->country_id;
					        
	// 				        if($testmode) {
	// 				        	$answer->is_admin = true;
	// 				        }
	// 				        $answer->save();
	// 				        $answered[$q] = 0;
					        
	//         			} else if($type == 'single') {

	// 						$answer = new VoxAnswer;
	// 				        $answer->user_id = $this->user->id;
	// 				        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 				        if (in_array($q, $welcome_vox_question_ids)===true) {
	// 				        	$answer->is_completed = 1;
	// 				        	$answer->is_skipped = 0;
	// 				        }
	// 				        $answer->question_id = $q;
	// 				        $answer->answer = $a;
	// 				        $answer->country_id = $this->user->country_id;

	// 				        $this->setupAnswerStats($answer);
				        	
	// 				        if($testmode) {
	// 				        	$answer->is_admin = true;
	// 				        }
	// 				        $answer->save();
	// 				        $answered[$q] = $a;

	// 				        if( $found->cross_check ) {
	// 				    		if (is_numeric($found->cross_check)) {
	// 				    			$v_quest = VoxQuestion::where('id', $q )->first();

	// 				    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
	// 					    			$vcc = new VoxCrossCheck;
	// 					    			$vcc->user_id = $this->user->id;
	// 					    			$vcc->question_id = $found->cross_check;
	// 					    			$vcc->old_answer = $cross_checks[$q];
	// 					    			$vcc->save();
	// 					    		}

	// 				    			VoxAnswer::where('user_id',$this->user->id )->where('vox_id', 11)->where('question_id', $found->cross_check )->update([
	// 				    				'answer' => $a,
	// 				    			]);

	// 				    		} else if($found->cross_check == 'gender') {
	// 			    				if (!empty($cross_checks) && $cross_checks[$q] != $a) {
	// 			    					$vcc = new VoxCrossCheck;
	// 					    			$vcc->user_id = $this->user->id;
	// 					    			$vcc->question_id = $found->cross_check;
	// 					    			$vcc->old_answer = $cross_checks[$q];
	// 					    			$vcc->save();
	// 					    		}
	// 					    		// $this->user->gender = $a == 1 ? 'm' : 'f';
	// 				    			// $this->user->save();

	// 				    		} else {
	// 				    			$cc = $found->cross_check;

	// 				    			$i=0;
	// 				    			foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
	// 				    				if($i==$a) {
	// 						    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
	// 							    			$vcc = new VoxCrossCheck;
	// 							    			$vcc->user_id = $this->user->id;
	// 							    			$vcc->question_id = $found->cross_check;
	// 							    			$vcc->old_answer = $cross_checks[$q];
	// 							    			$vcc->save();
	// 							    		}
	// 				    					$this->user->$cc = $key;
	// 				    					$this->user->save();
	// 				    					break;
	// 				    				}
	// 				    				$i++;
	// 				    			}
	// 				    		}
	// 				        }

	//         			} else if(isset( config('vox.details_fields')[$type] ) || $type == 'location-question' || $type == 'birthyear-question' || $type == 'gender-question' ) {
	//         				$answered[$q] = 1;
	//         				$answer = null;

	//         				if( !empty($found->cross_check) ) {
	//         					if($found->cross_check == 'birthyear') {

	// 				    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
	// 			    					$vcc = new VoxCrossCheck;
	// 					    			$vcc->user_id = $this->user->id;
	// 					    			$vcc->question_id = $found->cross_check;
	// 					    			$vcc->old_answer = $cross_checks[$q];
	// 					    			$vcc->save();
	// 					    		}
	// 					    		// $this->user->birthyear = $a;
	// 				    			// $this->user->save();

	// 		        				$answer = new VoxAnswer;
	// 						        $answer->user_id = $this->user->id;
	// 						        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 						        if (in_array($q, $welcome_vox_question_ids)===true) {
	// 						        	$answer->is_completed = 1;
	// 				        			$answer->is_skipped = 0;
	// 						        }
	// 						        $answer->question_id = $q;
	// 						        $answer->answer = 0;
	// 						        $answer->country_id = $this->user->country_id;
	// 				        		$this->setupAnswerStats($answer);

	// 						        if($testmode) {
	// 						        	$answer->is_admin = true;
	// 						        }
	// 						        $answer->save();
	// 						        $answered[$q] = 0;
	// 				    		}
	//         				}

	//         			} else if($type == 'number') {
 //        					$answer = new VoxAnswer;
	// 				        $answer->user_id = $this->user->id;
	// 				        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 				        if (in_array($q, $welcome_vox_question_ids)===true) {
	// 				        	$answer->is_completed = 1;
	// 			        		$answer->is_skipped = 0;
	// 				        }
	// 				        $answer->question_id = $q;
	// 				        $answer->answer = $a;
	// 				        $answer->country_id = $this->user->country_id;
	// 			        	$this->setupAnswerStats($answer);
				        
	// 				        if($testmode) {
	// 				        	$answer->is_admin = true;
	// 				        }
	// 				        $answer->save();

	// 					    $answered[$q] = $a;

	//         			} else if($type == 'multiple') {
	//         				foreach ($a as $value) {
	//         					$answer = new VoxAnswer;
	// 					        $answer->user_id = $this->user->id;
	// 					        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 					        if (in_array($q, $welcome_vox_question_ids)===true) {
	// 					        	$answer->is_completed = 1;
	// 				        		$answer->is_skipped = 0;
	// 					        }
	// 					        $answer->question_id = $q;
	// 					        $answer->answer = $value;
	// 					        $answer->country_id = $this->user->country_id;
	// 				        	$this->setupAnswerStats($answer);
					        
	// 					        if($testmode) {
	// 					        	$answer->is_admin = true;
	// 					        }
	// 					        $answer->save();
	//         				}
	// 					    $answered[$q] = $a;

	//         			} else if($type == 'scale' || $type == 'rank') {
	//         				foreach ($a as $k => $value) {
	//         					$answer = new VoxAnswer;
	// 					        $answer->user_id = $this->user->id;
	// 					        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
	// 					        if (in_array($q, $welcome_vox_question_ids)===true) {
	// 					        	$answer->is_completed = 1;
	// 				        		$answer->is_skipped = 0;
	// 					        }
	// 					        $answer->question_id = $q;
	// 					        $answer->answer = $k+1;
	// 					        $answer->scale = $value;
	// 					        $answer->country_id = $this->user->country_id;
	// 				        	$this->setupAnswerStats($answer);
						        
	// 					        if($testmode) {
	// 					        	$answer->is_admin = true;
	// 					        }
	// 					        $answer->save();
	//         				}
	// 					    $answered[$q] = $a;
	//         			}
	//         		}

 //    				$reallist = $list->filter(function ($value, $key) {
	// 				    return !$value->is_skipped;
	// 				});

 //    				$ppp = 10;
 //        			if( $reallist->count() && $reallist->count()%$ppp==0 && !$testmode && !$this->user->is_partner ) {

 //        				$pagenum = $reallist->count()/$ppp;
 //        				$start = $reallist->forPage($pagenum, $ppp)->first();
        				
	// 			        $diff = Carbon::now()->diffInSeconds( $start->created_at );
	// 			        $normal = $ppp*2;
	// 			        if($normal > $diff) {

	// 			        	$warned_before = session('too-fast');
	// 			        	if(!$warned_before) {
	// 			        		session([
	// 			            		'too-fast' => true
	// 			            	]);
	// 			        	} else {
	// 			        		session([
	// 			            		'too-fast' => null
	// 			            	]);
	// 			        	}

 //        					$prev_bans = $this->user->getPrevBansCount('vox', 'too-fast');
	//         				$ret['toofast'] = true;
	//         				if(!$warned_before) {
	//         					$ret['warning'] = true;
	//         					$ret['img'] = url('new-vox-img/ban-warning-fast-'.($prev_bans+1).'.png');
	//         					$titles = [
 //        							trans('vox.page.bans.warning-too-fast-title-1'),
 //        							trans('vox.page.bans.warning-too-fast-title-2'),
 //        							trans('vox.page.bans.warning-too-fast-title-3'),
 //        							trans('vox.page.bans.warning-too-fast-title-4'),
	//         					];
	//         					$ret['title'] = $titles[$prev_bans];
	//         					$contents = [
 //        							trans('vox.page.bans.warning-too-fast-content-1'),
 //        							trans('vox.page.bans.warning-too-fast-content-2'),
 //        							trans('vox.page.bans.warning-too-fast-content-3'),
 //        							trans('vox.page.bans.warning-too-fast-content-4'),
	//         					];
	//         					$ret['content'] = $contents[$prev_bans];

	//         				} else {
 //            					$ban = $this->user->banUser('vox', 'too-fast', $vox->id);
 //            					$ret['ban'] = true;
 //            					$ret['ban_duration'] = $ban['days'];
 //            					$ret['ban_times'] = $ban['times'];
	//         					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
	//         					$titles = [
 //        							trans('vox.page.bans.ban-too-fast-title-1'),
 //        							trans('vox.page.bans.ban-too-fast-title-2'),
 //        							trans('vox.page.bans.ban-too-fast-title-3'),
 //        							trans('vox.page.bans.ban-too-fast-title-4',[
 //        								'name' => $this->user->getNames()
 //        							]),
	//         					];
	//         					$ret['title'] = $titles[$prev_bans];
	//         					$contents = [
 //        							trans('vox.page.bans.ban-too-fast-content-1'),
 //        							trans('vox.page.bans.ban-too-fast-content-2'),
 //        							trans('vox.page.bans.ban-too-fast-content-3'),
 //        							trans('vox.page.bans.ban-too-fast-content-4'),
	//         					];
	//         					$ret['content'] = $contents[$prev_bans];

	//         					//Delete all answers
	// 							VoxAnswer::where('vox_id', $vox->id)
	// 							->where('user_id', $this->user->id)
	// 							->delete();
	//         				}
	// 			        }
 //        			}

 //    				if (!empty($welcome_vox_question_ids) && $q==end($welcome_vox_question_ids)) {
	// 					$reward = new DcnReward;
	// 			        $reward->user_id = $this->user->id;
	// 			        $reward->reference_id = 11;
	// 			        $reward->type = 'survey';
	// 			        $reward->platform = 'vox';
	// 			        $reward->reward = 100;

	// 			        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
	// 	                $dd = new DeviceDetector($userAgent);
	// 	                $dd->parse();

	// 	                if ($dd->isBot()) {
	// 	                    // handle bots,spiders,crawlers,...
	// 	                    $reward->device = $dd->getBot();
	// 	                } else {
	// 	                    $reward->device = $dd->getDeviceName();
	// 	                    $reward->brand = $dd->getBrandName();
	// 	                    $reward->model = $dd->getModel();
 //        					$reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
	// 	                }

	// 			        $reward->save();
 //    				}

	// 		        if(count($answered) == count($vox->questions)) {

	// 		        	session([
	// 						'scales' => null,
	// 					]);

	// 					if( $this->user->madeTest($vox->id) && !(Request::input('goback') && $testmode) ) {
	// 						return Response::json( [
	// 							'success' => false
	// 						] );
	// 					}

	// 					$answered_without_skip_count = 0;
	// 					$answered_without_skip = [];
	// 					foreach ($list as $l) {
	// 						if(!isset( $answered_without_skip[$l->question_id] ) && $l->question && $l->answer > 0 || $l->question->type == 'number' || $l->question->cross_check == 'birthyear' || $l->question->cross_check == 'household_children') {
	// 							$answered_without_skip[$l->question_id] = ['1']; //3
	// 							$answered_without_skip_count++;
	// 						}
	// 					}

	// 					$reward = new DcnReward;
	// 			        $reward->user_id = $this->user->id;
	// 			        $reward->reference_id = $vox->id;
	// 			        $reward->platform = 'vox';
	// 			        $reward->type = 'survey';
	// 			        $reward->reward = $vox->getRewardForUser($this->user, $answered_without_skip_count);
	// 			        $start = $list->first()->created_at;
	// 			        $diff = Carbon::now()->diffInSeconds( $start );
	// 			        $normal = count($vox->questions)*2;
	// 			        $reward->seconds = $diff;

	// 			        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
	// 	                $dd = new DeviceDetector($userAgent);
	// 	                $dd->parse();

	// 	                if ($dd->isBot()) {
	// 	                    // handle bots,spiders,crawlers,...
	// 	                    $reward->device = $dd->getBot();
	// 	                } else {
	// 	                    $reward->device = $dd->getDeviceName();
	// 	                    $reward->brand = $dd->getBrandName();
	// 	                    $reward->model = $dd->getModel();
 //        					$reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
	// 	                }

	// 			        $reward->save();
 //        				$ret['balance'] = $this->user->getTotalBalance('vox');

	// 					$open_recommend = false;
	// 					$social_profile = false;
	// 					$filled_voxes = $this->user->filledVoxesCount();

	// 					if(!$this->user->is_dentist && $filled_voxes == 1) {
	// 						$social_profile = true;
	// 					} else if (($filled_voxes == 5 || $filled_voxes == 10 || $filled_voxes == 20 || $filled_voxes == 50) && empty($this->user->fb_recommendation)) {
	// 						$open_recommend = true;
	// 					}

	// 					$ret['recommend'] = $open_recommend;
	// 					$ret['social_profile'] = $social_profile;

 //        				VoxAnswer::where('user_id', $this->user->id)->where('vox_id', $vox->id)->update(['is_completed' => 1]);

 //        				$vox->recalculateUsersPercentage($this->user);

 //                        if($this->user->invited_by && !empty($this->user->invitor)) {

 //                        	$inv = UserInvite::where('user_id', $this->user->invited_by)
	// 			            ->where(function ($query) {
	// 			                $query->where('platform', '!=', 'trp')
	// 			                ->orWhere('platform', null);
	// 			            })
	// 			            ->where('invited_id', $this->user->id)
	// 			            ->whereNull('rewarded')
	// 			            ->first();

 //                            if(!empty($inv) && !$inv->dont_rewarded) {

 //                            	$reward = new DcnReward;
	// 					        $reward->user_id = $this->user->invited_by;
	// 					        $reward->reference_id = $this->user->id;
	// 					        $reward->type = 'invitation';
	// 					        $reward->platform = 'vox';
	// 					        $reward->reward = Reward::getReward('reward_invite');

	// 					        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
	// 			                $dd = new DeviceDetector($userAgent);
	// 			                $dd->parse();

	// 			                if ($dd->isBot()) {
	// 			                    // handle bots,spiders,crawlers,...
	// 			                    $reward->device = $dd->getBot();
	// 			                } else {
	// 			                    $reward->device = $dd->getDeviceName();
	// 			                    $reward->brand = $dd->getBrandName();
	// 			                    $reward->model = $dd->getModel();
 //        							$reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
	// 			                }

	// 					        $reward->save();

 //                                $inv->rewarded = true;
 //                                $inv->save();
                                
 //                                if($this->user->invitor->is_dentist) {
 //                                    $this->user->invitor->sendGridTemplate( 82, [
 //                                        'who_joined_name' => $this->user->getNames()
 //                                    ], 'vox' );
 //                                } else {
 //                                	$this->user->invitor->sendGridTemplate( 113, [
 //                                        'who_joined_name' => $this->user->getNames()
 //                                    ], 'vox' );
 //                                }
 //                            }
 //                        }

 //                        if ($this->user->platform == 'external') {
 //                            $curl = curl_init();
	// 						curl_setopt_array($curl, array(
	// 							CURLOPT_RETURNTRANSFER => 1,
	// 							CURLOPT_POST => 1,
	// 							CURLOPT_URL => 'https://hub-app-api.dentacoin.com/internal-api/push-notification/',
	// 							CURLOPT_SSL_VERIFYPEER => 0,
	// 						    CURLOPT_POSTFIELDS => array(
	// 						        'data' => User::encrypt(json_encode(array('type' => 'reward-won', 'id' => $this->user->id, 'value' => Reward::getReward('reward_invite'))))
	// 						    )
	// 						));
							 
	// 						$resp = json_decode(curl_exec($curl));
	// 						curl_close($curl);

 //                        } else if(!empty($this->user->patient_of)) {

 //                        	$curl = curl_init();
	// 						curl_setopt_array($curl, array(
	// 							CURLOPT_RETURNTRANSFER => 1,
	// 							CURLOPT_POST => 1,
	// 							CURLOPT_URL => 'https://dcn-hub-app-api.dentacoin.com/manage-push-notifications',
	// 							CURLOPT_SSL_VERIFYPEER => 0,
	// 						    CURLOPT_POSTFIELDS => array(
	// 						        'data' => User::encrypt(json_encode(array('type' => 'reward-won', 'id' => $this->user->id, 'value' => Reward::getReward('reward_invite'))))
	// 						    )
	// 						));
							 
	// 						$resp = json_decode(curl_exec($curl));
	// 						curl_close($curl);
 //                        }

	// 		        }
 //        		} else {
 //        			$ret['success'] = false;
 //        		}
 //        	}
 //    	}

	// 	if( $ret['success'] ) {
	// 		request()->session()->regenerateToken();
	// 		$ret['token'] = request()->session()->token();
	// 		$ret['vox_id'] = $vox->id;
	// 		$ret['question_id'] = !empty($q) ? $q : null;
	// 	}

 //    	return Response::json( $ret );
	// }

	/**
     * bottom content of single vox page
     */
	public function vox_public_down($locale=null) {
		$featured_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->where('featured', true)->orderBy('sort_order', 'ASC')->take(9)->get();

		if( $featured_voxes->count() < 9 ) {

			$arr_v = [];
			foreach ($featured_voxes as $fv) {
				$arr_v[] = $fv->id;
			}

			$swiper_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->whereNotIn('id', $arr_v)->orderBy('sort_order', 'ASC')->take( 9 - $featured_voxes->count() )->get();

			$featured_voxes = $featured_voxes->concat($swiper_voxes);
		}
		return $this->ShowVoxView('template-parts.recent-surveys-vox-public', array(
        	'voxes' => $featured_voxes,
        ));	
	}

	/**
     * Start the vox again from the first question
     */
	public function start_over() {

		return ServicesVox::startOver($this->user->id);
	}

	/**
     * Get next question of the vox
     */
    public function getNextQuestion() {

        return ServicesVox::getNextQuestionFunction($this->admin, $this->user, false, $this->country_id);
    }
}