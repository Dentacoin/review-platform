<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Models\VoxCrossCheck;
use App\Models\VoxQuestion;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\VoxRelated;
use App\Models\VoxAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\PageSeo;
use App\Models\Reward;
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


class VoxController extends FrontController
{

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        parent::__construct($request, $route, $locale);

    	$this->details_fields = config('vox.details_fields');
	}

	public function home($locale=null, $id) {
		$vox = Vox::find($id);

		if (empty($vox)) {
			return redirect( getLangUrl('page-not-found') );
		}		

		return $this->dovox($locale, $vox);
	}
	public function home_slug($locale=null, $slug) {
		$vox = Vox::whereTranslationLike('slug', $slug)->with('questions.translations')->with('questions.vox')->with('categories.category')->with('categories.category.translations')->first();

		if (empty($vox)) {
			return redirect( getLangUrl('page-not-found') );
		}

		return $this->dovox($locale, $vox);
	}
	public function dovox($locale=null, $vox) {

		if(empty($this->user) && !empty($this->admin) && ($this->admin->id) == 11 && !empty($this->admin->user_id)) {
			$adm = User::find($this->admin->user_id);

	        if(!empty($adm)) {
	            Auth::login($adm, true);
	        }
	        return redirect(url()->current().'?testmode=1');
	    }

        $admin_ids = Admin::getAdminProfileIds();
		$isAdmin = Auth::guard('admin')->user() || (!empty($this->user) && in_array($this->user->id, $admin_ids));

		if (!$isAdmin && $vox->type=='hidden') {
			return redirect( getLangUrl('page-not-found') );
		}

		if(!$this->user) {

			session([
	            'vox-redirect-workaround' => str_replace( getLangUrl('/').App::getLocale().'/', '', $vox->getLink())
	        ]);

	        session([
	            'intended' => $vox->getLink(),
	        ]);

	        $seos = PageSeo::find(16);

	        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
	        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
	        $social_title = str_replace(':title', $vox->title, $seos->social_title);
	        $social_description = str_replace(':description', $vox->description, $seos->social_description);

			return $this->ShowVoxView('vox-public', array(
				'voxes' => Vox::where('type', 'normal')->orderBy('sort_order', 'ASC')->take(9)->get(),
				'vox' => $vox,
				'custom_body_class' => 'vox-public',
				'js' => [
					'vox.js'
				],
				'css' => [
					'vox-public-vox.css'
				],
	            'csscdn' => [
	                'https://fonts.googleapis.com/css?family=Lato:700&display=swap&subset=latin-ext',
	                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css',
	            ],
	            'jscdn' => [
	                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js',
	            ],
	            'canonical' => $vox->getLink(),
	            'social_image' => $vox->getSocialImageUrl('survey'),
	            'seo_title' => $seo_title,
	            'seo_description' => $seo_description,
	            'social_title' => $social_title,
	            'social_description' => $social_description,
	        ));
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

		if($this->user->loggedFromBadIp() && !$this->user->is_dentist && $this->user->platform != 'external') {

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

			$action = new UserAction;
            $action->user_id = $this->user->id;
            $action->action = 'deleted';
            $action->reason = 'Automatically - Bad IP ( vox questionnaire )';
            $action->actioned_at = Carbon::now();
            $action->save();

            $this->user->deleteActions();
            User::destroy( $this->user->id );
            
			Request::session()->flash('error-message', 'We have detected suspicious activity from your account.');
			return redirect( getLangUrl('/') );
		}

		if(empty($vox) || ($this->user->status!='approved' && $this->user->status!='added_by_clinic_claimed' && $this->user->status!='test') ) {
			if($this->user->status!='approved' && $this->user->status!='added_by_clinic_claimed' && $this->user->status!='test') {
            	Request::session()->flash('error-message', 'We\'re currently verifying your profile. Meanwhile you won\'t be able to take surveys or edit your profile. Please be patient, we\'ll send you an email once the procedure is completed.');
			}
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

			if ($this->user->country_id) {
				$arrr = [];
				foreach ($suggested_voxes as $vl) {
					$has_started_the_survey = VoxAnswer::where('vox_id', $vl->id)->where('user_id', $this->user->id)->first();

		            if(!empty($vl->country_percentage) && !empty($vl->users_percentage) && array_key_exists($this->user->country_id, $vl->users_percentage) && $vl->users_percentage[$this->user->country_id] > $vl->country_percentage  && empty($has_started_the_survey)) {
		                $arrr[] = $vl->id;
		            }
				}

				if (!empty($arrr)) {
					foreach ($arrr as $ar) {
						$suggested_voxes = $suggested_voxes->filter(function($item) use ($ar) {
						    return $item->id != $ar;
						});
					}
				}
			}

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
					'taken-vox.js'
				],
	            'csscdn' => [
	                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css',
	            ],
	            'jscdn' => [
	                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js',
	            ],
			]);
		}

        if($this->user->isBanned('vox')) {
            return redirect('https://account.dentacoin.com/dentavox?platform=dentavox');
        }

		if (!$testmode) {
			$has_started_the_survey = VoxAnswer::where('vox_id', $vox->id)->where('user_id', $this->user->id)->first();
			if ($this->user->isVoxRestricted($vox) || (!empty($vox->country_percentage) && !empty($vox->users_percentage) && array_key_exists($this->user->country_id, $vox->users_percentage) && $vox->users_percentage[$this->user->country_id] >= $vox->country_percentage && empty($has_started_the_survey))) {
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

				if ($this->user->country_id) {
					$arrr = [];
					foreach ($suggested_voxes as $vl) {
						$has_started_the_survey = VoxAnswer::where('vox_id', $vl->id)->where('user_id', $this->user->id)->first();

			            if(!empty($vl->country_percentage) && !empty($vl->users_percentage) && array_key_exists($this->user->country_id, $vl->users_percentage) && $vl->users_percentage[$this->user->country_id] > $vl->country_percentage  && empty($has_started_the_survey)) {
			                $arrr[] = $vl->id;
			            }
					}

					if (!empty($arrr)) {
						foreach ($arrr as $ar) {
							$suggested_voxes = $suggested_voxes->filter(function($item) use ($ar) {
							    return $item->id != $ar;
							});
						}
					}
				}

				if ($this->user->isVoxRestricted($vox)) {
					$res_desc = 'The target group of this survey consists of respondents with different demographics. No worries: We have plenty of other opportunities for you! ';
				} else {
					$res_desc = 'This survey reached the limit for users with your demographics. Check again later. No worries: We have plenty of other opportunities for you! ';
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
						'taken-vox.js'
					],
		            'csscdn' => [
		                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css',
		            ],
		            'jscdn' => [
		                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js',
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

    	// dd($cross_checks);


		$list = VoxAnswer::where('vox_id', $vox->id)
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

		$not_bot = $testmode || session('not_not-'.$vox->id);


		if(Request::input('goback') && $testmode) {
			$this->goBack($answered, $list, $vox);


            return redirect( $vox->getLink() );
		}

		$slist = VoxScale::get();
		$scales = [];
		foreach ($slist as $sitem) {
			$scales[$sitem->id] = $sitem;
		}

        if(Request::isMethod('post')) {

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
	            }

        	} else {

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

		        		$answer_count = count($question->vox_scale_id && !empty($scales[$question->vox_scale_id]) ? explode(',', $scales[$question->vox_scale_id]->answers) : json_decode($question->answers, true) );

		        		if ($type == 'skip') {
		        			$valid = true;
		        			$a = 0;

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

		        		} else if ($type == 'multiple') {

		        			$valid = true;
		        			$a = Request::input('answer');
		        			foreach ($a as $value) {
		        				if (!($value>=1 && $value<=$answer_count)) {
		        					$valid = false; 
		        					break;
		        				}
		        			}
		        			
		        		} else if($type == 'scale') {
	        				
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
	            					$ban = $this->user->banUser('vox', 'mistakes');
	            					$ret['ban'] = true;
	            					$ret['ban_duration'] = $ban['days'];
	            					$ret['ban_times'] = $ban['times'];
		        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
		        					$titles = [
		        						trans('vox.page.bans.ban-mistakes-title-1'),
		        						trans('vox.page.bans.ban-mistakes-title-2'),
		        						trans('vox.page.bans.ban-mistakes-title-3'),
		        						trans('vox.page.bans.ban-mistakes-title-4', [
		        							'name' => $this->user->getName()
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
							        $answer->save();
							        $answered[$q] = 0;

							        $skips = request('skips');
							        if(is_array($skips)) {
							        	foreach ($skips as $skip_id) {
							        		$skipped = $vox->questions->find($skip_id);
							        		if($skipped->question_trigger=='-1') {
							        			$answer = new VoxAnswer;
										        $answer->user_id = $this->user->id;
										        $answer->vox_id = in_array($q, $welcome_vox_question_ids)===false ? $vox->id : 11;
										        $answer->question_id = $skip_id;
										        $answer->answer = 0;
										        $answer->is_skipped = true;
										        $answer->country_id = $this->user->country_id;
										        $answer->save();
										        $answered[$skip_id] = 0;
							        		}
							        	}
							        }
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
									        $answer->save();
									        $answered[$q] = 0;

							    		}
			        				}


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

								        $answer->save();
			        				}
								    $answered[$q] = $a;
			        			} else if($type == 'scale') {
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
		            					$ban = $this->user->banUser('vox', 'too-fast');
		            					$ret['ban'] = true;
		            					$ret['ban_duration'] = $ban['days'];
		            					$ret['ban_times'] = $ban['times'];
			        					$ret['img'] = url('new-vox-img/ban'.($prev_bans+1).'.png');
			        					$titles = [
		        							trans('vox.page.bans.ban-too-fast-title-1'),
		        							trans('vox.page.bans.ban-too-fast-title-2'),
		        							trans('vox.page.bans.ban-too-fast-title-3'),
		        							trans('vox.page.bans.ban-too-fast-title-4',[
		        								'name' => $this->user->getName()
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
	        					# code...
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

	                            if($this->user->invited_by) {
	                                $inv = UserInvite::where('user_id', $this->user->invited_by)->where('invited_id', $this->user->id)->first();
	                                if(!empty($inv) && !$inv->rewarded) {

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

	                                    $this->user->invitor->sendGridTemplate( 82, [
	                                        'who_joined_name' => $this->user->getName()
	                                    ], 'vox' );
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
				if ((count($this->user->filledVoxes()) == 5 || count($this->user->filledVoxes()) == 10 || count($this->user->filledVoxes()) == 20 || count($this->user->filledVoxes()) == 50) && empty($this->user->fb_recommendation)) {
					$open_recommend = true;
				}

				$ret['recommend'] = $open_recommend;
			}

        	return Response::json( $ret );
        }

        $first_question = null;
        $first_question_num = 0;
        if($not_bot) {
        	if (!empty($welcome_vox)) {
	        	foreach ($welcome_vox->questions as $question) {
		    		$first_question_num++;
		    		if(!isset($answered[$question->id])) {
		    			$first_question = $question->id;
		    			break;
		    		}
		    	}
        	} else {
        		foreach ($vox->questions as $question) {
		    		$first_question_num++;
		    		if(!isset($answered[$question->id])) {
		    			$first_question = $question->id;
		    			break;
		    		}
		    	}
        	}
        } else {
	    	$first_question_num++;
        }


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

        if (($all_surveys->count() - 1) == count($this->user->filledVoxes())) {
        	$done_all = true;
        }

		$related_voxes = [];
		$related_voxes_ids = [];
		if ($vox->related->isNotEmpty()) {
			foreach ($vox->related as $r) {
				if (!in_array($r->related_vox_id, $this->user->filledVoxes())) {
					$related_voxes[] = Vox::find($r->related_vox_id);
					$related_voxes_ids[] = $r->related_vox_id;
				}
			}
		}
		$suggested_voxes = Vox::where('type', 'normal')->with('translations')->with('categories.category')->with('categories.category.translations')->orderBy('sort_order', 'ASC')->whereNotIn('id', $related_voxes_ids)->whereNotIn('id', $this->user->filledVoxes())->take(9)->get();

		if ($this->user->country_id) {
			$arrr = [];
			foreach ($suggested_voxes as $vl) {
				$has_started_the_survey = VoxAnswer::where('vox_id', $vl->id)->where('user_id', $this->user->id)->first();

	            if(!empty($vl->country_percentage) && !empty($vl->users_percentage) && array_key_exists($this->user->country_id, $vl->users_percentage) && $vl->users_percentage[$this->user->country_id] > $vl->country_percentage  && empty($has_started_the_survey)) {
	                $arrr[] = $vl->id;
	            }
			}

			if (!empty($arrr)) {
				foreach ($arrr as $ar) {
					$suggested_voxes = $suggested_voxes->filter(function($item) use ($ar) {
					    return $item->id != $ar;
					});
				}
			}
		}

		$welcome_answers = VoxAnswer::where('vox_id', 11)->where('user_id', $this->user->id)->get();
		$welcome_arr = [];
		//dd($welcome_answers);
		if (!empty($welcome_answers)) {
			//$welcome_arr = $welcome_answers->pluck('question_id', 'answer')->toArray();

			foreach ($welcome_answers as $wa) {
				$welcome_arr[] = $wa->question_id.':'.$wa->answer;
			}
			$welcome_arr = implode(';', $welcome_arr);
		}

		$demogr_arr = [];
		foreach (config('vox.details_fields') as $key => $value) {
			if(!empty($this->user->$key) || $this->user->$key === '0') {
				$i = 0;
				foreach (config('vox.details_fields.'.$key.'.values') as $k => $v) {
					$i++;
					if($k == $this->user->$key) {
						$demogr_arr[] = $key.':'.$i;
					}
				}
				
			}
		}

		if(!empty($this->user->gender)) {
			$demogr_arr[] = 'gender:'.($this->user->gender == 'm' ? '1' : '2');
		}

		if(!empty($this->user->birthyear)) {			
			$age = date('Y') - $this->user->birthyear;
          
            if ($age <= 24) {
                $demogr_arr[] = 'age_groups:1';
            } else if($age <= 34) {
                $demogr_arr[] = 'age_groups:2';
            } else if($age <= 44) {
                $demogr_arr[] = 'age_groups:3';
            } else if($age <= 54) {
                $demogr_arr[] = 'age_groups:4';
            } else if($age <= 64) {
                $demogr_arr[] = 'age_groups:5';
            } else if($age <= 74) {
                $demogr_arr[] = 'age_groups:6';
            } else if($age > 74) {
                $demogr_arr[] = 'age_groups:7';
            }
		}

		$seos = PageSeo::find(15);

        $seo_title = str_replace(':title', $vox->title, $seos->seo_title);
        $seo_description = str_replace(':title', $vox->title, $seos->seo_description);
        $social_title = str_replace(':title', $vox->title, $seos->social_title);
        $social_description = str_replace(':description', $vox->description, $seos->social_description);

		return $this->ShowVoxView('vox', array(
			'welcome_vox' => $welcome_vox,
			'welcome_arr' => $welcome_arr,
			'demogr_arr' => !empty($demogr_arr) ? implode(';', $demogr_arr) : '',
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
			'js' => [
				'vox.js',
        		'flickity.pkgd.min.js'
			],
			'css' => [
				'vox-questionnaries.css',
        		'flickity.min.css'
			],
            'jscdn' => [
                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/js/swiper.min.js',
            ],
            'csscdn' => [
                'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.6/css/swiper.min.css',
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
            'isAdmin' => $isAdmin
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

		return $lastkey;
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
	
}