<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

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
	
    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        parent::__construct($request, $route, $locale);

    	$this->details_fields = config('vox.details_fields');
	}

	/**
     * Single vox page by id
     */
	public function home($locale=null, $id) {
		$vox = Vox::find($id);

		if (!empty($vox)) {
			return redirect( getLangUrl('paid-dental-surveys/'.$vox->slug) );
		}

		return redirect( getLangUrl('page-not-found') );
	}

	/**
     * Single vox page by slug
     */
	public function home_slug($locale=null, $slug) {
		$vox = Vox::whereTranslationLike('slug', $slug)->with('questions.translations')->with('questions.vox')->first();

		if (empty($vox)) {
			return redirect( getLangUrl('page-not-found') );
		}

		return $this->dovox($locale, $vox);
	}

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
     * Single vox page for not logged users
     * Single vox page for taken vox
     * Single vox page for restricted vox
     * Answer vox
     */
	public function dovox($locale=null, $vox) {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

		if(empty($this->user) && !empty($this->admin) && ($this->admin->id) == 11 && !empty($this->admin->user_id)) {
			$adm = User::find($this->admin->user_id);

	        if(!empty($adm)) {
	            Auth::login($adm, true);
	        }
	        return redirect(url()->current().'?testmode=1');
	    }

        if(!$this->user) {

            $seos = PageSeo::find(16);

            $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
            $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
            $social_title = str_replace(':title', $vox->title, $seos->social_title);
            $social_description = str_replace(':description', $vox->description, $seos->social_description);

            return $this->ShowVoxView('vox-public', array(
				'vox' => $vox,
				'custom_body_class' => 'vox-public',
				'js' => [
					'all-surveys.js',
				],
				'css' => [
					'vox-public-vox.css',
				],
	            'canonical' => $vox->getLink(),
	            'social_image' => $vox->getSocialImageUrl('survey'),
	            'seo_title' => $seo_title,
	            'seo_description' => $seo_description,
	            'social_title' => $social_title,
	            'social_description' => $social_description,
	        ));
        }

        $admin_ids = ['65003'];
        $isAdmin = Auth::guard('admin')->user() || in_array($this->user->id, $admin_ids);

        if (!$isAdmin && $vox->type=='hidden') {
            return redirect( getLangUrl('page-not-found') );
        }

        if($this->user->isBanned('vox')) {
            return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
        }

        $taken = $this->user->filledVoxes();

        if (request()->has('testmode')) {
            if(request('testmode')) {
                $ses = [
		            'testmode' => true
		        ];
            } else {
                $ses = [
		            'testmode' => false
		        ];
            }
            session($ses);
		}
		$testmode = session('testmode') && $isAdmin;
		$qtype = Request::input('type');

		$this->current_page = 'questionnaire';
		$doing_details = false;
		$doing_asl = false;

		if(($this->user->loggedFromBadIp() && !$this->user->is_dentist && $this->user->platform != 'external')) {

			$ul = new UserLogin;
            $ul->user_id = $this->user->id;
            $ul->ip = User::getRealIp();
            $ul->platform = 'vox';
            $ul->country = \GeoIP::getLocation()->country;

            $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
            $dd = new DeviceDetector($userAgent);
            $dd->parse();

            if ($dd->isBot()) {
                // handle bots,spiders,crawlers,...
                $ul->device = $dd->getBot();
            } else {
                $ul->device = $dd->getDeviceName();
                $ul->brand = $dd->getBrandName();
                $ul->model = $dd->getModel();
                $ul->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
            }
            
            $ul->save();
            
            $u_id = $this->user->id;

            $action = new UserAction;
            $action->user_id = $u_id;
            $action->action = 'bad_ip';
            $action->reason = 'Automatically - Bad IP ( vox questionnaire )';
            $action->actioned_at = Carbon::now();
            $action->save();

            Auth::guard('web')->user()->logoutActions();
            Auth::guard('web')->user()->removeTokens();
            Auth::guard('web')->logout();
            
			return redirect( 'https://account.dentacoin.com/account-on-hold?platform=dentavox&on-hold-type=bad_ip&key='.urlencode(User::encrypt($u_id)) );
		}

		if(empty($vox) || !in_array($this->user->status, config('dentist-statuses.approved')) ) {
			return redirect( getLangUrl('page-not-found') );
		} else if( 
	    	isset( $this->details_fields[$qtype] ) ||
	    	$qtype=='gender-question' ||
	    	$qtype=='birthyear-question' ||
	    	$qtype=='location-question'
		) {
	    	//I'm doing ASL questions!
			$doing_asl = true;
		} else if( $this->user->madeTest($vox->id) && !(Request::input('goback') && $testmode) ) { //because of GoBack

			$related_voxes = [];
			$related_voxes_ids = [];
			if ($vox->related->isNotEmpty()) {
				foreach ($vox->related as $r) {
					if (!in_array($r->related_vox_id, $taken)) {
						$related_voxes[] = Vox::find($r->related_vox_id);
						$related_voxes_ids[] = $r->related_vox_id;
					}
				}
			}

			$suggested_voxes = $this->user->voxesTargeting()->where('type', 'normal')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

			$suggested_voxes = $this->user->notRestrictedVoxesList($suggested_voxes);

	        $seos = PageSeo::find(17);

	        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
	        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
	        $social_title = str_replace(':title', $vox->title, $seos->social_title);
	        $social_description = str_replace(':title', $vox->description, $seos->social_description);

			return $this->showVoxView('taken-survey', [
				'vox' => $vox,
				'related_voxes' => $related_voxes,
	            'suggested_voxes' => $suggested_voxes,
	            'seo_title' => $seo_title,
	            'seo_description' => $seo_description,
	            'social_title' => $social_title,
	            'social_description' => $social_description,
            	'canonical' => $vox->getLink(),
            	'social_image' => $vox->getSocialImageUrl('survey'),
				'js' => [
					'taken-vox.js',
                    'swiper.min.js'
				],
                'css' => [
                    'swiper.min.css'
                ],
			]);
		}

		if (!$testmode) {

			if ($this->user->isVoxRestricted($vox) || $vox->voxCountryRestricted($this->user)) {
				$related_voxes = [];
				$related_voxes_ids = [];
				if ($vox->related->isNotEmpty()) {
					foreach ($vox->related as $r) {
						if (!in_array($r->related_vox_id, $taken)) {
							$related_voxes[] = Vox::find($r->related_vox_id);
							$related_voxes_ids[] = $r->related_vox_id;
						}
					}
				}

				$suggested_voxes = $this->user->voxesTargeting()->where('type', 'normal')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $taken)->take(9)->get();

				$suggested_voxes = $this->user->notRestrictedVoxesList($suggested_voxes);

				if ($this->user->isVoxRestricted($vox)) {
					$res_desc = trans('vox.page.restricted-questionnaire.description-target');
				} else {
					$res_desc = trans('vox.page.restricted-questionnaire.description-limit');
				}

				$seos = PageSeo::find(18);

		        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
		        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
		        $social_title = str_replace(':title', $vox->title, $seos->social_title);
		        $social_description = str_replace(':title', $vox->description, $seos->social_description);

				return $this->showVoxView('restricted-survey', [
					'res_desc' => $res_desc,
					'vox' => $vox,
					'related_voxes' => $related_voxes,
		            'suggested_voxes' => $suggested_voxes,
		            'seo_title' => $seo_title,
		            'seo_description' => $seo_description,
		            'social_title' => $social_title,
		            'social_description' => $social_description,
		            'canonical' => $vox->getLink(),
		            'social_image' => $vox->getSocialImageUrl('survey'),
					'js' => [
						'taken-vox.js',
                    	'swiper.min.js'
					],
					'css' => [
                    	'swiper.min.css'
					],
				]);
			}

			$daily_voxes = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->count();

			if($daily_voxes >= 10) {
				$last_vox = DcnReward::where('user_id', $this->user->id)->where('platform', 'vox')->where('type', 'survey')->where('created_at', '>', Carbon::now()->subDays(1))->orderBy('id', 'desc')->first();

				$time_left = '';

	            $now = Carbon::now()->subDays(1);
	            $time_left = $last_vox->created_at->diffInHours($now).':'.
	            str_pad($last_vox->created_at->diffInMinutes($now)%60, 2, '0', STR_PAD_LEFT).':'.
	            str_pad($last_vox->created_at->diffInSeconds($now)%60, 2, '0', STR_PAD_LEFT);

				$seos = PageSeo::find(19);

		        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
		        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
		        $social_title = str_replace(':title', $vox->title, $seos->social_title);
		        $social_description = str_replace(':description', $vox->description, $seos->social_description);

				return $this->ShowVoxView('daily-limit-reached', array(
					'vox' => $vox,
					'time_left' => $time_left,					
		            'canonical' => $vox->getLink(),
		            'social_image' => $vox->getSocialImageUrl('survey'),
		            'seo_title' => $seo_title,
		            'seo_description' => $seo_description,
		            'social_title' => $social_title,
		            'social_description' => $social_description,
				));
			}
		}

		$first = Vox::where('type', 'home')->first();
		$welcome_vox = '';
		$welcome_vox_question_ids = [];
		if (!$this->user->madeTest($first->id)) {
			$welcome_vox = $first;
			$welcome_vox_question_ids = $welcome_vox->questions->pluck('id')->toArray();
		}

    	$cross_checks = [];
    	$cross_checks_references = [];

    	foreach ($vox->questions as $vq) {
	    	if (!empty($vq->cross_check)) {

	    		if (is_numeric($vq->cross_check)) {
	    			$va = VoxAnswer::where('user_id',$this->user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
	    			$cross_checks[$vq->id] = $va ? $va->answer : null;
	    			$cross_checks_references[$vq->id] = $vq->cross_check;
	    		} else if($vq->cross_check == 'gender') {
	    			$cc = $vq->cross_check;
	    			$cross_checks[$vq->id] = $this->user->$cc == 'm' ? 1 : 2;
	    			$cross_checks_references[$vq->id] = 'gender';
	    		} else if($vq->cross_check == 'birthyear') {
	    			$cc = $vq->cross_check;
	    			$cross_checks[$vq->id] = $this->user->$cc;
	    			$cross_checks_references[$vq->id] = 'birthyear';
	    		} else {
	    			$cc = $vq->cross_check;
	    			$i=0;
	    			foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
	    				if($key==$this->user->$cc) {
	    					$cross_checks[$vq->id] = $i;
	    					$cross_checks_references[$vq->id] = $cc;
	    					break;
	    				}
	    				$i++;
	    			}
	    		}
	    	}
    	}

		$list = VoxAnswer::where('vox_id', $vox->id)
		->with('question')
		->where('user_id', $this->user->id)
		->orderBy('id', 'ASC')
		->get();
		$answered = [];
		foreach ($list as $l) {
			if(!isset( $answered[$l->question_id] )) {
				$answered[$l->question_id] = $l->answer; //3
			} else {
				if(!is_array($answered[$l->question_id])) {
					$answered[$l->question_id] = [ $answered[$l->question_id] ]; // [3]
				}
				$answered[$l->question_id][] = $l->answer; // [3,5,7]
			}
		}

		$answered_without_skip = [];
		foreach ($list as $l) {
			if(!isset( $answered_without_skip[$l->question_id] )) {
				if($l->question) {

					$answered_without_skip[$l->question_id] = ($l->question->type == 'number' && $l->answer == 0) || $l->question->cross_check == 'birthyear' ? 1 : $l->answer; //3
				}
			} else {
				if(!is_array($answered_without_skip[$l->question_id])) {
					$answered_without_skip[$l->question_id] = [ $answered_without_skip[$l->question_id] ]; // [3]
				}
				$answered_without_skip[$l->question_id][] = ($l->question->type == 'number' && $l->answer == 0) || $l->question->cross_check == 'birthyear' ? 1 : $l->answer; // [3,5,7]
			}
		}

		$answered_without_skip_count = 0;
		foreach ($answered_without_skip as $key => $value) {
			if($value != 0) {
				$answered_without_skip_count++;
			}
		}

		$not_bot = $testmode || session('not_not-'.$vox->id);

		//dd($answered);
		if(Request::input('goback') && $testmode) {
			$q_id = $this->goBack($answered, $list, $vox);

			if(!empty(VoxQuestion::find($q_id))) {

				$vq = VoxQuestion::where('vox_id', $vox->id)->where('order', VoxQuestion::find($q_id)->order-1)->first();
				if(!empty($vq)) {
					$quest_id = $vq->id;
				} else {
					$quest_id = $q_id;
				}
			} else {
				$quest_id = $q_id;
			}

            return redirect( $vox->getLink().'?testmode=1&q-id='.$quest_id );
		}

		if(Request::input('start-from') && $testmode) {
			$q_id = $this->testAnswers($answered, Request::input('start-from'), $vox);

			if(!empty(VoxQuestion::find($q_id))) {

				$vq = VoxQuestion::where('vox_id', $vox->id)->where('order', VoxQuestion::find($q_id)->order-1)->first();
				if(!empty($vq)) {
					$quest_id = $vq->id;
				} else {
					$quest_id = $q_id;
				}
			} else {
				$quest_id = $q_id;
			}

            return redirect( $vox->getLink().'?testmode=1&q-id='.$quest_id );
		}

		$slist = VoxScale::get();
		$scales = [];
		foreach ($slist as $sitem) {
			$scales[$sitem->id] = $sitem;
		}

        if(Request::isMethod('post')) {

            ini_set('max_execution_time', 0);
            set_time_limit(0);
            ini_set('memory_limit','1024M');

        	$ret = [
        		'success' => true,
        	];
        	if(Request::input('captcha')) {
	            $captcha = false;
	            $cpost = [
	                'secret' => env('CAPTCHA_SECRET'),
	                'response' => Request::input('captcha'),
	                'remoteip' => User::getRealIp()
	            ];
	            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
	            curl_setopt($ch, CURLOPT_HEADER, 0);
	            curl_setopt ($ch, CURLOPT_POST, 1);
	            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($cpost));
	            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
	            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	            $response = curl_exec($ch);
	            curl_close($ch);
	            if($response) {
	                $api_response = json_decode($response, true);
	                if(!empty($api_response['success'])) {
	                    $captcha = true;
	                }
	            }

	            if(!$captcha) {
	            	$ret['success'] = false;
	            } else {
	            	session([
	            		'not_not-'.$vox->id => true,
	            		'reward-for-'.$vox->id => $vox->getRewardTotal()
	            	]);
	            	$ret['vox_id'] = $vox->id;
	            }

        	} else {

        		if(!$this->user->is_dentist) {
					$using_vpn = VpnIp::where('ip', User::getRealIp())->first();
					$is_whitelist_ip = WhitelistIp::where('for_vpn', 1)->where('ip', 'like', User::getRealIp())->first();
					if(!empty($using_vpn) && empty($is_whitelist_ip)) {
						$ret['is_vpn'] = true;

						return Response::json( $ret );
					}
        		}


	        	$q = Request::input('question');

	        	if( !isset( $answered[$q] ) && $not_bot ) {

		        	$found = $doing_asl ? true : false;
		        	foreach ($vox->questions as $question) {
		        		if($question->id == $q) {
		        			$found = $question;
		        			break;
		        		}
		        	}

		        	if (!empty($welcome_vox)) {
			        	foreach ($welcome_vox->questions as $question) {
			        		if($question->id == $q) {
			        			$found = $question;
			        			break;
			        		}
			        	}
		        	}

		        	if($found) {
		        		$valid = false;
		        		$type = Request::input('type');

		        		$answer_count = $type == 'multiple' || $type == 'rank' || $type == 'scale' || $type == 'single' ? count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true) ) : 0;

		        		if ($type == 'skip') {
		        			$valid = true;
		        			$a = 0;

		        		} else if($type == 'previous') {
		        			$valid = true;
		        			$a = Request::input('answer');
		        		} else if ( isset( $this->details_fields[$type] ) ) {

		        			$should_reward = false;
		        			if($this->user->$type===null) {
		        				$should_reward = true;
		        			}

		        			$this->user->$type = Request::input('answer');
		        			$this->user->save();
		        			if( isset( config('vox.stats_scales')[$type] ) ) {
		        				VoxAnswer::where('user_id', $this->user->id)->update([
			        				$type => Request::input('answer')
			        			]);
		        			}
		        			$valid = true;
		        			$a = Request::input('answer');

		        			if( $should_reward ) {

			        			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
			        				array(
			        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
			        				))
			        			);
		        			}

		        		} else if ($type == 'location-question') {

		        			if($this->user->country_id===null) {
			        			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
			        				array(
			        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
			        				))
			        			);
		        			}
		        			//answer = 71,2312
		        			$country_id = Request::input('answer');
		        			$this->user->country_id = $country_id;
		        			VoxAnswer::where('user_id', $this->user->id)->update([
		        				'country_id' => $country_id
		        			]);
		        			$this->user->save();

		        			$a = $country_id;
		        			$valid = true;
		        		
		        		} else if ($type == 'birthyear-question') {

		        			if($this->user->birthyear===null || $this->user->birthyear===0) {
			        			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
			        				array(
			        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
			        				))
			        			);

			        			$this->user->birthyear = Request::input('answer');
			        			$this->user->save();
		        			}

		        			$agegroup = $this->getAgeGroup(Request::input('answer'));

		        			VoxAnswer::where('user_id', $this->user->id)->update([
		        				'age' => $agegroup
		        			]);

		        			$valid = true;
		        			$a = Request::input('answer');

		        		} else if ($type == 'gender-question') {

		        			if($this->user->gender===null) {
			        			DcnReward::where('user_id', $this->user->id )->where('platform', 'vox')->where('reference_id',$vox->id )->where('type', 'survey')->update(
			        				array(
			        					'reward' => DB::raw('`reward` + '.$vox->getRewardPerQuestion()->dcn
			        				))
			        			);
		        			}
		        			$this->user->gender = Request::input('answer');
		        			$this->user->save();
		        			VoxAnswer::where('user_id', $this->user->id)->update([
		        				'gender' => Request::input('answer')
		        			]);
		        			$valid = true;
		        			$a = Request::input('answer');

		        		} else if ($type == 'multiple' || $type == 'scale' || $type == 'rank') {

		        			$valid = true;
		        			$a = Request::input('answer');
		        			foreach ($a as $k => $value) {
		        				if (!($value>=1 && $value<=$answer_count)) {
		        					$valid = false; 
		        					break;
		        				}
		        			}
		        			
		        		} else if ($type == 'single') {
	        				$a = intval(Request::input('answer'));
	        				$valid = $a>=1 && $a<=$answer_count;

		        		} else if ($type == 'number') {

		        			$cur_question = VoxQuestion::find($q);
		        			$min_num = intval(explode(':',$cur_question->number_limit)[0]);
		        			$max_num = intval(explode(':',$cur_question->number_limit)[1]);
	        				$a = intval(Request::input('answer'));
	        				$valid = $a>=$min_num && $a<=$max_num;
		        		}



		        		if( $valid ) {
		        			VoxAnswer::where('user_id', $this->user->id )->where('vox_id',$vox->id )->where('question_id', $q)->delete();

		        			$is_scam = false;

					        if($question->is_control) {

					        	if ($question->is_control == '-1') {
			        				if($type == 'single') {
						        		$is_scam = end($answered) != $a;
						        	} else if($type == 'multiple') {
						        		$end_answered = [];

						        		if (!is_array(end($answered))) {
						        			$end_answered[] = end($answered);
						        		} else {
						        			$end_answered = end($answered);
						        		}
						        		$is_scam = !empty(array_diff( $end_answered, $a ));
						        	}
					        	} else {
			        				if($type == 'single') {
					        			$is_scam = $question->is_control!=$a;
						        	} else if($type == 'multiple') {
						        		$is_scam = !empty(array_diff( explode(',', $question->is_control), $a ));
						        	}
					        	}
					        }

				        	if($is_scam && !$testmode && !$this->user->is_partner) {
				        		
				        		$wrongs = intval(session('wrongs'));
				        		$wrongs++;
				            	session([
				            		'wrongs' => $wrongs
				            	]);

		        				$ret['wrong'] = true;
		        				$prev_bans = $this->user->getPrevBansCount('vox', 'mistakes');

		        				if($wrongs==1 || ($wrongs==2 && !$prev_bans) ) {
		        					$ret['warning'] = true;
		        					$ret['img'] = url('new-vox-img/mistakes'.($prev_bans+1).'.png');
		        					$titles = [
		        						trans('vox.page.bans.warning-mistakes-title-1'),
		        						trans('vox.page.bans.warning-mistakes-title-2'),
		        						trans('vox.page.bans.warning-mistakes-title-3'),
		        						trans('vox.page.bans.warning-mistakes-title-4'),
			        				];
		        					$contents = [
		        						trans('vox.page.bans.warning-mistakes-content-1'),
		        						trans('vox.page.bans.warning-mistakes-content-2'),
		        						trans('vox.page.bans.warning-mistakes-content-3'),
		        						trans('vox.page.bans.warning-mistakes-content-4'),
		        					];
		        					if( $wrongs==2 && !$prev_bans ) {
		        						$ret['zman'] = url('new-vox-img/mistake2.png');
		        						$ret['title'] = trans('vox.page.bans.warning-mistakes-title-1-second');
		        						$ret['content'] = trans('vox.page.bans.warning-mistakes-content-1-second');
		        					} else {
		        						$ret['zman'] = url('new-vox-img/mistake1.png');
		        						$ret['title'] = $titles[$prev_bans];
			        					$ret['content'] = $contents[$prev_bans];
		        					}

		        					if( $wrongs==1 && !$prev_bans ) {
		        						$ret['action'] = 'roll-back';
		        						$ret['go_back'] = $this->goBack($answered, $list, $vox);
		        					} else {
		        						$ret['action'] = 'start-over';
		        						$ret['go_back'] = $vox->questions->first()->id;
										VoxAnswer::where('vox_id', $vox->id)
										->where('user_id', $this->user->id)
										->delete();
		        					}
		        				} else {
					            	session([
					            		'wrongs' => null
					            	]);
	            					$ban = $this->user->banUser('vox', 'mistakes', $vox->id);
	            					$ret['ban'] = true;
	            					$ret['ban_duration'] = $ban['days'];
	            					$ret['ban_times'] = $ban['times'];
		        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
		        					$titles = [
		        						trans('vox.page.bans.ban-mistakes-title-1'),
		        						trans('vox.page.bans.ban-mistakes-title-2'),
		        						trans('vox.page.bans.ban-mistakes-title-3'),
		        						trans('vox.page.bans.ban-mistakes-title-4', [
		        							'name' => $this->user->getNames()
		        						]),
		        					];
		        					$ret['title'] = $titles[$prev_bans];
		        					$contents = [
		        						trans('vox.page.bans.ban-mistakes-content-1'),
		        						trans('vox.page.bans.ban-mistakes-content-2'),
		        						trans('vox.page.bans.ban-mistakes-content-3'),
		        						trans('vox.page.bans.ban-mistakes-content-4'),
		        					];
		        					$ret['content'] = $contents[$prev_bans];

		        					//Delete all answers
									VoxAnswer::where('vox_id', $vox->id)
									->where('user_id', $this->user->id)
									->delete();
		        				}
				        	} else {

			        			if($type == 'skip') {
			        				$answer = new VoxAnswer;
							        $answer->user_id = $this->user->id;
							        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
							        $answer->question_id = $q;
							        $answer->answer = 0;
							        $answer->is_skipped = true;
							        $answer->country_id = $this->user->country_id;
							        
							        if($testmode) {
							        	$answer->is_admin = true;
							        }
							        $answer->save();
							        $answered[$q] = 0;
							        
			        			} else if($type == 'previous') {
			        				$answer = new VoxAnswer;
							        $answer->user_id = $this->user->id;
							        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
							        $answer->question_id = $q;
						        	$answer->answer = $a;
						        	$this->setupAnswerStats($answer);
							        $answer->country_id = $this->user->country_id;
							        
							        if($testmode) {
							        	$answer->is_admin = true;
							        }
							        $answer->save();
							        $answered[$q] = 0;
							        
			        			} else if($type == 'single') {

									$answer = new VoxAnswer;
							        $answer->user_id = $this->user->id;
							        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
							        if (in_array($q, $welcome_vox_question_ids)===true) {
							        	$answer->is_completed = 1;
							        	$answer->is_skipped = 0;
							        }
							        $answer->question_id = $q;
							        $answer->answer = $a;
							        $answer->country_id = $this->user->country_id;
							        $this->setupAnswerStats($answer);
						        	
							        if($testmode) {
							        	$answer->is_admin = true;
							        }
							        $answer->save();
							        $answered[$q] = $a;

							        if( $found->cross_check ) {
							    		if (is_numeric($found->cross_check)) {
							    			$v_quest = VoxQuestion::where('id', $q )->first();

							    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
								    			$vcc = new VoxCrossCheck;
								    			$vcc->user_id = $this->user->id;
								    			$vcc->question_id = $found->cross_check;
								    			$vcc->old_answer = $cross_checks[$q];
								    			$vcc->save();
								    		}

							    			VoxAnswer::where('user_id',$this->user->id )->where('vox_id', 11)->where('question_id', $found->cross_check )->update([
							    				'answer' => $a,
							    			]);

							    		} else if($found->cross_check == 'gender') {
						    				if (!empty($cross_checks) && $cross_checks[$q] != $a) {
						    					$vcc = new VoxCrossCheck;
								    			$vcc->user_id = $this->user->id;
								    			$vcc->question_id = $found->cross_check;
								    			$vcc->old_answer = $cross_checks[$q];
								    			$vcc->save();
								    		}
								    		// $this->user->gender = $a == 1 ? 'm' : 'f';
							    			// $this->user->save();

							    		} else {
							    			$cc = $found->cross_check;

							    			$i=0;
							    			foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
							    				if($i==$a) {
									    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
										    			$vcc = new VoxCrossCheck;
										    			$vcc->user_id = $this->user->id;
										    			$vcc->question_id = $found->cross_check;
										    			$vcc->old_answer = $cross_checks[$q];
										    			$vcc->save();
										    		}
							    					$this->user->$cc = $key;
							    					$this->user->save();
							    					break;
							    				}
							    				$i++;
							    			}
							    		}
							        }

			        			} else if(isset( $this->details_fields[$type] ) || $type == 'location-question' || $type == 'birthyear-question' || $type == 'gender-question' ) {
			        				$answered[$q] = 1;
			        				$answer = null;

			        				if( !empty($found->cross_check) ) {
			        					if($found->cross_check == 'birthyear') {

							    			if (!empty($cross_checks) && $cross_checks[$q] != $a) {
						    					$vcc = new VoxCrossCheck;
								    			$vcc->user_id = $this->user->id;
								    			$vcc->question_id = $found->cross_check;
								    			$vcc->old_answer = $cross_checks[$q];
								    			$vcc->save();
								    		}
								    		// $this->user->birthyear = $a;
							    			// $this->user->save();

					        				$answer = new VoxAnswer;
									        $answer->user_id = $this->user->id;
									        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
									        if (in_array($q, $welcome_vox_question_ids)===true) {
									        	$answer->is_completed = 1;
							        			$answer->is_skipped = 0;
									        }
									        $answer->question_id = $q;
									        $answer->answer = 0;
									        $answer->country_id = $this->user->country_id;
							        		$this->setupAnswerStats($answer);

									        if($testmode) {
									        	$answer->is_admin = true;
									        }
									        $answer->save();
									        $answered[$q] = 0;
							    		}
			        				}

			        			} else if($type == 'number') {
		        					$answer = new VoxAnswer;
							        $answer->user_id = $this->user->id;
							        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
							        if (in_array($q, $welcome_vox_question_ids)===true) {
							        	$answer->is_completed = 1;
						        		$answer->is_skipped = 0;
							        }
							        $answer->question_id = $q;
							        $answer->answer = $a;
							        $answer->country_id = $this->user->country_id;
						        	$this->setupAnswerStats($answer);
						        
							        if($testmode) {
							        	$answer->is_admin = true;
							        }
							        $answer->save();

								    $answered[$q] = $a;

			        			} else if($type == 'multiple') {
			        				foreach ($a as $value) {
			        					$answer = new VoxAnswer;
								        $answer->user_id = $this->user->id;
								        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
								        if (in_array($q, $welcome_vox_question_ids)===true) {
								        	$answer->is_completed = 1;
							        		$answer->is_skipped = 0;
								        }
								        $answer->question_id = $q;
								        $answer->answer = $value;
								        $answer->country_id = $this->user->country_id;
							        	$this->setupAnswerStats($answer);
							        
								        if($testmode) {
								        	$answer->is_admin = true;
								        }
								        $answer->save();
			        				}
								    $answered[$q] = $a;

			        			} else if($type == 'scale' || $type == 'rank') {
			        				foreach ($a as $k => $value) {
			        					$answer = new VoxAnswer;
								        $answer->user_id = $this->user->id;
								        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
								        if (in_array($q, $welcome_vox_question_ids)===true) {
								        	$answer->is_completed = 1;
							        		$answer->is_skipped = 0;
								        }
								        $answer->question_id = $q;
								        $answer->answer = $k+1;
								        $answer->scale = $value;
								        $answer->country_id = $this->user->country_id;
							        	$this->setupAnswerStats($answer);
								        
								        if($testmode) {
								        	$answer->is_admin = true;
								        }
								        $answer->save();
			        				}
								    $answered[$q] = $a;
			        			}
			        		}

	        				$reallist = $list->filter(function ($value, $key) {
							    return !$value->is_skipped;
							});

	        				$ppp = 10;
		        			if( $reallist->count() && $reallist->count()%$ppp==0 && !$testmode && !$this->user->is_partner ) {

		        				$pagenum = $reallist->count()/$ppp;
		        				$start = $reallist->forPage($pagenum, $ppp)->first();
		        				
						        $diff = Carbon::now()->diffInSeconds( $start->created_at );
						        $normal = $ppp*2;
						        if($normal > $diff) {

						        	$warned_before = session('too-fast');
						        	if(!$warned_before) {
						        		session([
						            		'too-fast' => true
						            	]);
						        	} else {
						        		session([
						            		'too-fast' => null
						            	]);
						        	}

		        					$prev_bans = $this->user->getPrevBansCount('vox', 'too-fast');
			        				$ret['toofast'] = true;
			        				if(!$warned_before) {
			        					$ret['warning'] = true;
			        					$ret['img'] = url('new-vox-img/ban-warning-fast-'.($prev_bans+1).'.png');
			        					$titles = [
		        							trans('vox.page.bans.warning-too-fast-title-1'),
		        							trans('vox.page.bans.warning-too-fast-title-2'),
		        							trans('vox.page.bans.warning-too-fast-title-3'),
		        							trans('vox.page.bans.warning-too-fast-title-4'),
			        					];
			        					$ret['title'] = $titles[$prev_bans];
			        					$contents = [
		        							trans('vox.page.bans.warning-too-fast-content-1'),
		        							trans('vox.page.bans.warning-too-fast-content-2'),
		        							trans('vox.page.bans.warning-too-fast-content-3'),
		        							trans('vox.page.bans.warning-too-fast-content-4'),
			        					];
			        					$ret['content'] = $contents[$prev_bans];

			        				} else {
		            					$ban = $this->user->banUser('vox', 'too-fast', $vox->id);
		            					$ret['ban'] = true;
		            					$ret['ban_duration'] = $ban['days'];
		            					$ret['ban_times'] = $ban['times'];
			        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
			        					$titles = [
		        							trans('vox.page.bans.ban-too-fast-title-1'),
		        							trans('vox.page.bans.ban-too-fast-title-2'),
		        							trans('vox.page.bans.ban-too-fast-title-3'),
		        							trans('vox.page.bans.ban-too-fast-title-4',[
		        								'name' => $this->user->getNames()
		        							]),
			        					];
			        					$ret['title'] = $titles[$prev_bans];
			        					$contents = [
		        							trans('vox.page.bans.ban-too-fast-content-1'),
		        							trans('vox.page.bans.ban-too-fast-content-2'),
		        							trans('vox.page.bans.ban-too-fast-content-3'),
		        							trans('vox.page.bans.ban-too-fast-content-4'),
			        					];
			        					$ret['content'] = $contents[$prev_bans];

			        					//Delete all answers
										VoxAnswer::where('vox_id', $vox->id)
										->where('user_id', $this->user->id)
										->delete();
			        				}
						        }
		        			}

	        				// dd($answered, count($vox->questions));

	        				if (!empty($welcome_vox_question_ids) && $q==end($welcome_vox_question_ids)) {
								$reward = new DcnReward;
						        $reward->user_id = $this->user->id;
						        $reward->reference_id = 11;
						        $reward->type = 'survey';
						        $reward->platform = 'vox';
						        $reward->reward = 100;

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
	        				}

					        if(count($answered) == count($vox->questions)) {
					        	$reward = DcnReward::where('user_id', $this->user->id)->where('reference_id', $vox->id)->where('platform', 'vox')->where('type', 'survey')->first();

					        	if (empty($reward)) {
									$reward = new DcnReward;
							        $reward->user_id = $this->user->id;
							        $reward->reference_id = $vox->id;
							        $reward->platform = 'vox';
							        $reward->type = 'survey';
							    }
						        $reward->reward = $vox->getRewardForUser($this->user->id);
						        $start = $list->first()->created_at;
						        $diff = Carbon::now()->diffInSeconds( $start );
						        $normal = count($vox->questions)*2;
						        $reward->seconds = $diff;

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
		        				$ret['balance'] = $this->user->getTotalBalance('vox');

		        				VoxAnswer::where('user_id', $this->user->id)->where('vox_id', $vox->id)->update(['is_completed' => 1]);

		        				$vox->recalculateUsersPercentage($this->user);

	                            if($this->user->invited_by && !empty($this->user->invitor)) {

	                            	$inv = UserInvite::where('user_id', $this->user->invited_by)
						            ->where(function ($query) {
						                $query->where('platform', '!=', 'trp')
						                ->orWhere('platform', null);
						            })
						            ->where('invited_id', $this->user->id)
						            ->whereNull('rewarded')
						            ->first();

	                                if(!empty($inv) && !$inv->dont_rewarded) {

	                                	$reward = new DcnReward;
								        $reward->user_id = $this->user->invited_by;
								        $reward->reference_id = $this->user->id;
								        $reward->type = 'invitation';
								        $reward->platform = 'vox';
								        $reward->reward = Reward::getReward('reward_invite');

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

	                                    $inv->rewarded = true;
	                                    $inv->save();
	                                    
	                                    if($this->user->invitor->is_dentist) {
		                                    $this->user->invitor->sendGridTemplate( 82, [
		                                        'who_joined_name' => $this->user->getNames()
		                                    ], 'vox' );
	                                    } else {
	                                    	$this->user->invitor->sendGridTemplate( 113, [
		                                        'who_joined_name' => $this->user->getNames()
		                                    ], 'vox' );
	                                    }
	                                }
	                            }

	                            if ($this->user->platform == 'external') {
		                            $curl = curl_init();
									curl_setopt_array($curl, array(
										CURLOPT_RETURNTRANSFER => 1,
										CURLOPT_POST => 1,
										CURLOPT_URL => 'https://hub-app-api.dentacoin.com/internal-api/push-notification/',
										CURLOPT_SSL_VERIFYPEER => 0,
									    CURLOPT_POSTFIELDS => array(
									        'data' => User::encrypt(json_encode(array('type' => 'reward-won', 'id' => $this->user->id, 'value' => Reward::getReward('reward_invite'))))
									    )
									));
									 
									$resp = json_decode(curl_exec($curl));
									curl_close($curl);

		                        } else if(!empty($this->user->patient_of)) {

		                        	$curl = curl_init();
									curl_setopt_array($curl, array(
										CURLOPT_RETURNTRANSFER => 1,
										CURLOPT_POST => 1,
										CURLOPT_URL => 'https://dcn-hub-app-api.dentacoin.com/manage-push-notifications',
										CURLOPT_SSL_VERIFYPEER => 0,
									    CURLOPT_POSTFIELDS => array(
									        'data' => User::encrypt(json_encode(array('type' => 'reward-won', 'id' => $this->user->id, 'value' => Reward::getReward('reward_invite'))))
									    )
									));
									 
									$resp = json_decode(curl_exec($curl));
									curl_close($curl);
		                        }

					        }
		        		} else {
		        			$ret['success'] = false;
		        		}
		        	}
	        	}
        	}

    		if($this->user->isVoxRestricted($vox)) {
    			$ret['success'] = false;
    			$ret['restricted'] = true;
    		}

			if( $ret['success'] ) {
				request()->session()->regenerateToken();
				$ret['token'] = request()->session()->token();

				$open_recommend = false;
				$social_profile = false;
				$filled_voxes = $this->user->filledVoxes();

				if(!$this->user->is_dentist && count($filled_voxes) == 1) {
					$social_profile = true;
				} else if ((count($filled_voxes) == 5 || count($filled_voxes) == 10 || count($filled_voxes) == 20 || count($filled_voxes) == 50) && empty($this->user->fb_recommendation)) {
					$open_recommend = true;
				}

				$ret['recommend'] = $open_recommend;
				$ret['social_profile'] = $social_profile;
				$ret['vox_id'] = $vox->id;
				$ret['question_id'] = !empty($q) ? $q : null;
			}

        	return Response::json( $ret );
        }

        $first_question = null;
        $first_question_num = 0;
        // if($not_bot) {
        	if (!empty($welcome_vox)) {
	        	foreach ($welcome_vox->questions as $question) {
		    		$first_question_num++;
		    		if(!isset($answered[$question->id])) {
		    			$first_question = $question->id;
		    			break;
		    		}
		    	}
        	} else {
        		if($testmode && request('back-q')) {
        			$first_question_num++;
        			$first_question = request('back-q');
        		} else {

	        		foreach ($vox->questions as $question) {
			    		$first_question_num++;
			    		if(!isset($answered[$question->id])) {
			    			$first_question = $question->id;
			    			break;
			    		}
			    	}
        		}
        	}
      //   } else {
	    	// $first_question_num++;
      //   }


        $total_questions = $vox->questions->count();

        if (!$this->user->birthyear) {
        	$total_questions++;
        }
        if (!$this->user->country_id) {
        	$total_questions++;
        }
        if (!$this->user->gender) {
        	$total_questions++;
        }

        foreach ($this->details_fields as $key => $value) {
        	if($this->user->$key==null) {
        		$total_questions++;		
        	}
        }

        if (!empty($welcome_vox)) {
	        foreach ($welcome_vox->questions as $key => $value) {
	        	$total_questions++;		
	        }
        }

        
        $em = new Email;
        $em->user_id = $this->user->id;
        $em->template_id = $this->user->is_dentist ? 27 : 25;
        $em->meta = [
        	'friend_name' => ''
        ];
		list($email_content, $email_title, $email_subtitle, $email_subject) = $em->prepareContent();

		$email_content = preg_replace('#(<a\s.*href=[\'"])(.*?)([\'"].*>)(.*?)(</a>)#', '$2', $email_content);


		$welcomerules = !session('vox-welcome');
		if($welcomerules) {
        	session([
        		'vox-welcome' => true
        	]);
		}

        $all_surveys = Vox::where('type', 'normal')->get();
        $done_all = false;
        $filled_voxes = $this->user->filledVoxes();

        if (($all_surveys->count() - 1) == count($filled_voxes)) {
        	$done_all = true;
        }

		$related_voxes = [];
		$related_voxes_ids = [];
		if ($vox->related->isNotEmpty()) {
			foreach ($vox->related as $r) {
				if (!in_array($r->related_vox_id, $filled_voxes)) {
					$related_voxes_ids[] = $r->related_vox_id;
				}
			}

			if (!empty($related_voxes_ids)) {
				foreach(Vox::with('translations')->with('categories.category')->with('categories.category.translations')->whereIn('id', $related_voxes_ids)->get() as $rv) {
					$related_voxes[] = $rv;
				}
			}
		}
		$suggested_voxes = Vox::where('type', 'normal')->with('translations')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $filled_voxes)->take(9)->get();

		$suggested_voxes = $this->user->notRestrictedVoxesList($suggested_voxes);

		$seos = PageSeo::find(15);

        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
        $social_title = str_replace(':title', $vox->title, $seos->social_title);
        $social_description = str_replace(':description', $vox->description, $seos->social_description);

		return $this->ShowVoxView('vox', array(
			'welcome_vox' => $welcome_vox,
			'related_voxes' => $related_voxes,
            'suggested_voxes' => $suggested_voxes,
			'cross_checks' => $cross_checks,
			'cross_checks_references' => $cross_checks_references,
			'welcomerules' => $welcomerules,
			'not_bot' => $not_bot,
			'details_fields' => $this->details_fields,
			'vox' => $vox,
			'scales' => $scales,
			'answered' => $answered,
			'real_questions' => $vox->questions->count(),
			'total_questions' => $total_questions,
			'first_question' => $first_question,
			'first_question_num' => $first_question_num,
			'answered_without_skip_count' => $answered_without_skip_count,
			'js' => [
				'all-surveys.js',
				'vox-new.js',
				'../js/lightbox.js',
				'../js/jquery-ui.min.js',
				'../js/jquery-ui-touch.min.js',
        		'flickity.pkgd.min.js',
                'swiper.min.js'
			],
			'css' => [
				'vox-questionnaries.css',
				'lightbox.css',
        		'flickity.min.css',
                'swiper.min.css'
			],
            'canonical' => $vox->getLink(),
            'social_image' => $vox->getSocialImageUrl('survey'),
            'seo_title' => $seo_title,
            'seo_description' => $seo_description,
            'social_title' => $social_title,
            'social_description' => $social_description,
            'email_data' => [
            	'title' => $email_subject,
            	'content' => $email_content,
            ],
            'done_all' => $done_all,
            'testmode' => $testmode,
            'isAdmin' => $isAdmin,
        ));
	}

	private function getAgeGroup($by) {

		$years = date('Y') - intval($by);
		$agegroup = 'more';
		if($years<=24) {
			$agegroup = '24';
		} else if($years<=34) {
			$agegroup = '34';
		} else if($years<=44) {
			$agegroup = '44';
		} else if($years<=54) {
			$agegroup = '54';
		} else if($years<=64) {
			$agegroup = '64';
		} else if($years<=74) {
			$agegroup = '74';
		}

		return $agegroup;

	}

	private function goBack($answered, $list, $vox) {

		$lastkey = null;
		if(!empty($answered)) {
			foreach ($list as $aq) {
				if(!$aq->is_skipped) {
					$lastkey = $aq->question_id;
				}
			}

			$found = false;
			foreach ($vox->questions as $question) {
				if($question->id==$lastkey) {
					$found = true;
				}
				if($found) {
					VoxAnswer::where('vox_id', $vox->id)
					->where('user_id', $this->user->id)
					->where('question_id', $question->id)
					->delete();

					DcnReward::where('reference_id', $vox->id)
					->where('platform', 'vox')
					->where('type', 'survey')
					->where('user_id', $this->user->id)
					->delete();
				}
			}
		}

		$lastest_key = VoxAnswer::where('vox_id', $vox->id)
		->where('user_id', $this->user->id)
		->where('question_id', $lastkey)
		->first();

		if(!empty($lastest_key) && $lastest_key->answer == 0) {
			do {
				$this->goBack($answered, $list, $vox);
			} while ( $lastest_key->answer == 0);
		}

		return $lastkey;
	}

	private function testAnswers($answered, $q_id, $vox) {

		if(!empty($answered)) {

			foreach ($vox->questions as $question) {
				if($question->id==$q_id) {
					foreach ($vox->questions as $vq) {
						if($vq->order >= $question->order) {
							VoxAnswer::where('vox_id', $vox->id)
							->where('user_id', $this->user->id)
							->where('question_id', $vq->id)
							->delete();

							DcnReward::where('reference_id', $vox->id)
							->where('platform', 'vox')
							->where('type', 'survey')
							->where('user_id', $this->user->id)
							->delete();
						}
					}
					break;
				}
			}
		}

		return $q_id;
	}

	private function setupAnswerStats(&$answer) {

        foreach (config('vox.stats_scales') as $df => $dv) {
        	if($df=='age') {
				$agegroup = $this->getAgeGroup($this->user->birthyear);
				$answer->$df = $agegroup;
        	} else {
        		if($this->user->$df!==null) {
	        		$answer->$df = $this->user->$df;
	        	}
        	}
        }
	}

	/**
     * Start the vox again from the first question
     */
	public function start_over() {

		$vox = Vox::find(Request::input('vox_id'));

		if (!empty($vox) && !empty(Request::input('user_id'))) {
	        VoxAnswer::where('vox_id', Request::input('vox_id'))
			->where('user_id', Request::input('user_id'))
			->delete();

			$ret = [
				'success' => true,
				'first_q' => $vox->questions->first()->id
			];
		} else {
			$ret = [
				'success' => false,
			];
		}

		return Response::json( $ret );
	}

	/**
     * Get next question of the vox
     */
    public function getNextQuestion() {

        return self::getNextQuestionFunction($this->admin, $this->user, false, $this->country_id);
    }

	public static function getNextQuestionFunction($admin, $user, $for_app, $country_id) {

//        if($for_app) {
//            $user = Auth::guard('api')->user();
//        } else {
//            $user = $this->user;
//        }

        if(!empty($user)) {

            $vox_id = request('vox_id');
            $welcome_vox = Vox::where('type', 'home')->first();
            $question_id = request('question_id');
            if(!empty($question_id)) {
                $cur_question = VoxQuestion::find($question_id);
            }

        	$admin_ids = ['65003'];
            $isAdmin = $for_app ? ( $user->is_admin ? true : false) : (Auth::guard('admin')->user() || in_array($user->id, $admin_ids));
            $testmode = session('testmode') && $isAdmin;

            if(!empty($vox_id)) {
                $vox = Vox::where('id', $vox_id);

                if(empty($testmode)) {
                    $vox = $vox->where('type', 'normal');
                }
                $vox = $vox->first();
            }

            if(!empty($vox_id) && (!empty($vox) || !empty($admin) )) {

                $array = [];

                if (!$user->madeTest($vox->id)) {

                    if (!$user->madeTest($welcome_vox->id)) {
                        // welcome qs
                        $array['welcome_vox'] = true;

                        if(!empty($question_id)) {
                            //question order
                            $next_question = VoxQuestion::where('vox_id', $cur_question->vox_id)->orderBy('order', 'asc')->where('order', '>', $cur_question->order)->first();
                            $array['question'] = $next_question;
                        } else {
                            //first question
                            $question = VoxQuestion::where('vox_id', $welcome_vox->id)->orderBy('order', 'ASC')->first();
                            $array['question'] = $question;
                        }
                    } else if(empty($user->birthyear)) {
                        //demographic qs
                        $array['birthyear_q'] = true;
                    } else if(empty($user->gender)) {
                        //demographic qs
                        $array['gender_q'] = true;
                    } else if(empty($user->country_id)) {
                        //demographic qs
                        $array['country_id_q'] = true;
                    }

                    if(empty($array)) {
                        foreach (config('vox.details_fields') as $key => $info) {
                            if($user->$key==null) {
                                $array['details_question'] = $info;
                                $array['details_question_id'] = $key;
                                break;
                            }
                        }
                    }
                    if(empty($array)) {

                        if(!empty($question_id) && is_numeric($question_id) && $cur_question->vox_id == 11) {
                            $question_id=null;
                        }

                        if(!empty($question_id) && is_numeric($question_id)) {
                            $next_question = VoxQuestion::where('vox_id', $cur_question->vox_id)->orderBy('order', 'asc')->where('order', '>', $cur_question->order)->first();
                            if(!empty($next_question->prev_q_id_answers)) {
                                $prev_q = VoxQuestion::find($next_question->prev_q_id_answers);

                                $prev_answers = VoxAnswer::where('vox_id', $vox_id)->where('question_id', $prev_q->id)->where('user_id', $user->id)->get();
                                if($prev_answers->count() == 1) {

                                    if($prev_answers->first()->answer != 0) {
                                        $prev_q_answers_text = $prev_q->vox_scale_id && !empty($scales[$prev_q->vox_scale_id]) ? explode(',', $scales[$prev_q->vox_scale_id]->answers) :  json_decode($prev_q->answers, true);

                                        if(mb_strpos($prev_q_answers_text[$prev_answers->pluck('answer')->toArray()[0] - 1], '!') !== false) {
                                            return 'skip-dvq:'.$next_question->id;
                                        } else {
                                            return 'skip-dvq:'.$next_question->id.';answer:'.$prev_answers->pluck('answer')->toArray()[0];
                                        }
                                    } else {
                                        return 'skip-dvq:'.$next_question->id;
                                    }

                                } else {
                                    $array['answers_shown'] = $prev_answers->pluck('answer')->toArray();
                                }
                            }

                            if(!empty($next_question->question_trigger)) {

                                if($next_question->question_trigger=='-1') {
                                    foreach ($vox->questions as $originalTrigger) {
                                        if($originalTrigger->id == $next_question->id) {
                                            break;
                                        }

                                        if( $originalTrigger->question_trigger && $originalTrigger->question_trigger!='-1' ) {
                                           $triggers = $originalTrigger->question_trigger;
                                        }
                                    }
                                } else {
                                    $triggers = $next_question->question_trigger;
                                }

                                if(!empty($triggers)) {

                                    $triggers = explode(';', $triggers);
                                    $triggerSuccess = [];

                                    foreach ($triggers as $trigger) {

                                        list($triggerId, $triggerAnswers) = explode(':', $trigger);
                                        if(is_numeric($triggerId)) {
                                            $trigger_question = VoxQuestion::find($triggerId);
                                        } else {
                                            //demographic
                                            $trigger_question = $triggerId;
                                        }

                                     //    if($next_question->id == 18655) {
	                                    //     echo '<br/>Q id: '.$triggerId;
	                                    // }

                                        if(mb_strpos($triggerAnswers, '!')!==false) {
                                            $invert_trigger_logic = true;
                                            $triggerAnswers = substr($triggerAnswers, 1);
                                        } else {
                                            $invert_trigger_logic = false;
                                        }

                                        if(mb_strpos($triggerAnswers, '-')!==false) {

                                            if(mb_strpos($triggerAnswers, ',')!==false) {

                                                $allowedAnswers = [];

                                                $answersArr = explode(',', $triggerAnswers);

                                                foreach ($answersArr as $ar) {
                                                    if(mb_strpos($ar, '-')!==false) {
                                                        list($from, $to) = explode('-', $ar);

                                                        for ($i=$from; $i <= $to ; $i++) {
                                                            $allowedAnswers[] = $i;
                                                        }
                                                    } else {
                                                        $allowedAnswers[] = intval($ar);
                                                    }
                                                }
                                            } else {
                                                list($from, $to) = explode('-', $triggerAnswers);

                                                $allowedAnswers = [];
                                                for ($i=$from; $i <= $to ; $i++) {
                                                    $allowedAnswers[] = $i;
                                                }
                                            }

                                        } else {
                                            $allowedAnswers = explode(',', $triggerAnswers);

                                            // foreach($allowedAnswers as $kk => $vv) {
                                            // 	$allowedAnswers[$kk] = intval($vv);
                                            // }
                                        }


                                        if(!empty($allowedAnswers)) {
                                            $givenAnswers = [];
                                            if(is_object($trigger_question)) {
                                                $user_answers = VoxAnswer::where('user_id', $user->id)->where('question_id', $trigger_question->id)->get();
                                                foreach ($user_answers as $ua) {
                                                    $givenAnswers[] = $ua->answer;
                                                }
                                            } else {
                                                //demographic
                                                $givenAnswers[] = $user->$trigger_question;
                                            }

                                      //       if($next_question->id == 18655) {
		                                    //     echo '<br/>allowedAnswers: '.json_encode($allowedAnswers);
		                                    //     echo '<br/>givenAnswers: '.json_encode($givenAnswers);
		                                    // }

                                            // echo 'Trigger for: '.$triggerId.' / Valid answers '.var_export($allowedAnswers, true).' / Answer: '.var_export($givenAnswers, true).' / Inverted logic: '.($invert_trigger_logic ? 'da' : 'ne').'<br/>';

                                            $int = 0;
                                            foreach ($givenAnswers as $ga) {
                                            	$int++;
                                            	
                                                if(str_contains($ga,',') !== false) {
                                                    $given_answers_array = explode(',', $ga);

                                                    $found = false;
                                                    foreach ($given_answers_array as $key => $value) {
                                                        if(in_array($value, $allowedAnswers)) {
                                                            $found = true;
                                                            break;
                                                        }
                                                    }

                                                    if($invert_trigger_logic) {
                                                        if(!$found) {
                                                            $triggerSuccess[$int] = true;
                                                        } else {
                                                            $triggerSuccess[$int] = false;
                                                        }
                                                    } else {

                                                        if($found) {
                                                            $triggerSuccess[$int] = true;
                                                        } else {
                                                            $triggerSuccess[$int] = false;
                                                        }
                                                    }
                                                } else {
                                                    if(strpos($allowedAnswers[0], '>') !== false) {
                                                        $trg_ans = substr($allowedAnswers[0], 1);

                                                        if($ga > intval($trg_ans)) {
                                                            $triggerSuccess[$int] = true;
                                                        } else {
                                                            $triggerSuccess[$int] = false;
                                                        }
                                                    } else if(strpos($allowedAnswers[0], '<') !== false) {
                                                        $trg_ans = substr($allowedAnswers[0], 1);

                                                        if(intval($ga) < intval($trg_ans)) {
                                                            $triggerSuccess[$int] = true;
                                                        } else {
                                                            $triggerSuccess[$int] = false;
                                                        }
                                                    } else {
                                                        if($invert_trigger_logic) {
                                                            if( !empty($ga) && !in_array($ga, $allowedAnswers) ) {
                                                                $triggerSuccess[$int] = true;
                                                            } else {
                                                                $triggerSuccess[$int] = false;
                                                            }
                                                        } else {
                                                            // echo in_array($ga, $allowedAnswers) ? '<br/>'.$ga.' in _array' : '<br/>'.$ga.' not_in array';

                                                            if( !empty($ga) && in_array($ga, $allowedAnswers) ) {
                                                                $triggerSuccess[$int] = true;
                                                            } else {
                                                                $triggerSuccess[$int] = false;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                      //       if($next_question->id == 18655) {
		                                    //     echo '<br/>Q id - trugger success: '.json_encode($triggerSuccess);
		                                    // }
                                        }
                                    }



                                    // if($next_question->id == 18655) {
                                    // 	dd($triggerSuccess);
                                    // }

                                    if( $next_question->trigger_type == 'or' ) { // ANY of the conditions should be met (A or B or C)
                                        if( !in_array(true, $triggerSuccess) ) {
                                            return 'skip-dvq:'.$next_question->id;
                                        }
                                    }  else { //ALL the conditions should be met (A and B and C)
                                        if( in_array(false, $triggerSuccess) ) {
                                            return 'skip-dvq:'.$next_question->id;
                                        }
                                    }

                                }
                            }

                            $array['question'] = $next_question;
                        } else {
                            $list = VoxAnswer::where('vox_id', $vox->id)->where('user_id', $user->id)->get();
                            $answered = [];

                            foreach ($list as $l) {
                                if(!isset( $answered[$l->question_id] )) {
                                    $answered[$l->question_id] = $l->answer; //3
                                } else {
                                    if(!is_array($answered[$l->question_id])) {
                                        $answered[$l->question_id] = [ $answered[$l->question_id] ]; // [3]
                                    }
                                    $answered[$l->question_id][] = $l->answer; // [3,5,7]
                                }
                            }

                            $questions_list = VoxQuestion::where('vox_id', $vox_id)->orderBy('order', 'ASC');

                            $question = $questions_list->first();
                            if(!isset($answered[$question->id])) {
                                //first question
                                $array['question'] = $question;
                            } else {
                                //first unanswered question
                                $array['question'] = $questions_list->where('order','>', VoxQuestion::find(array_key_last($answered))->order)->first();
                            }
                        }
                    }

                    if($for_app) {
                        $cross_check = false;
                        $cross_check_answer = null;
                        $cross_check_birthyear = false;

                        if(isset($array['question'])) {

                            $vq = $array['question'];
                            if (!empty($vq) && !empty($vq->cross_check)) {
                                $cross_check = true;

                                if (is_numeric($vq->cross_check)) {
                                    $va = VoxAnswer::where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
                                    $cross_check_answer = $va ? $va->answer : null;
                                } else if($vq->cross_check == 'gender') {
                                    $cross_check_answer = $user->gender == 'm' ? 1 : 2;
                                } else if($vq->cross_check == 'birthyear') {
                                    $cross_check_birthyear = true;
                                    $cross_check_answer = $user->birthyear;
                                } else {
                                    $cc = $vq->cross_check;
                                    $i=1;
                                    foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
                                        if($key==$user->$cc) {
                                            $cross_check_answer = $i;
                                            break;
                                        }
                                        $i++;
                                    }
                                }
                            }

                            $array['question'] = $vq->convertForResponse();
                        }
                    } else {

                        $cross_checks = [];
                        $cross_checks_references = [];

                        foreach ($vox->questions as $vq) {
                            if (!empty($vq->cross_check)) {

                                if (is_numeric($vq->cross_check)) {
                                    $va = VoxAnswer::where('user_id',$user->id )->where('vox_id', 11)->where('question_id', $vq->cross_check )->first();
                                    $cross_checks[$vq->id] = $va ? $va->answer : null;
                                    $cross_checks_references[$vq->id] = $vq->cross_check;
                                } else if($vq->cross_check == 'gender') {
                                    $cc = $vq->cross_check;
                                    $cross_checks[$vq->id] = $user->$cc == 'm' ? 1 : 2;
                                    $cross_checks_references[$vq->id] = 'gender';
                                } else if($vq->cross_check == 'birthyear') {
                                    $cc = $vq->cross_check;
                                    $cross_checks[$vq->id] = $user->$cc;
                                    $cross_checks_references[$vq->id] = 'birthyear';
                                } else {
                                    $cc = $vq->cross_check;
                                    $i=0;
                                    foreach (config('vox.details_fields.'.$cc.'.values') as $key => $value) {
                                        if($key==$user->$cc) {
                                            $cross_checks[$vq->id] = $i;
                                            $cross_checks_references[$vq->id] = $cc;
                                            break;
                                        }
                                        $i++;
                                    }
                                }
                            }
                        }
                    }

                    $slist = VoxScale::get();
                    $scales = [];
                    foreach ($slist as $sitem) {
                        $scales[$sitem->id] = $sitem;
                    }

                    if($for_app) {
                        $array['cross_check'] = $cross_check;
                        $array['cross_check_answer'] = $cross_check_answer;
                        $array['cross_check_birthyear'] = $cross_check_birthyear;
                        $array['scales'] = $scales;
                        $array['user'] = $user;
                        $array['country_id'] = $user->country_id ?? app('App\Http\Controllers\API\IndexController')->getCountryIdByIp() ?? '';

                        return Response::json( $array );

                    } else {

                        $array['cross_checks'] = $cross_checks;
                        $array['cross_checks_references'] = $cross_checks_references;
                        $array['scales'] = $scales;
                        $array['user'] = $user;
                        $array['country_id'] = $country_id;

                        return response()->view('vox.template-parts.vox-question', $array, 200)->header('X-Frame-Options', 'DENY');
                    }

                }
            }
        }

        return '';
	}
}