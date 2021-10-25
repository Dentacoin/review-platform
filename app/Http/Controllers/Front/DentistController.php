<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App\Models\DentistRecommendation;
use App\Models\StopVideoReview;
use App\Models\DentistPageview;
use App\Models\ReviewDownvote;
use App\Models\DentistFbPage;
use App\Models\AnonymousUser;
use App\Models\ReviewUpvote;
use App\Models\ReviewAnswer;
use App\Models\DentistClaim;
use App\Models\UserStrength;
use App\Models\UserInvite;
use App\Models\UserAction;
use App\Models\DcnReward;
use App\Models\UserLogin;
use App\Models\UserTeam;
use App\Models\Question;
use App\Models\UserBan;
use App\Models\OldSlug;
use App\Models\UserAsk;
use App\Models\Country;
use App\Models\PageSeo;
use App\Models\Review;
use App\Models\Reward;
use App\Models\User;

use App\Helpers\GeneralHelper;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Auth;
use Mail;

class DentistController extends FrontController {

    /**
     * show dentist's full review
     */
    public function fullReview($locale=null, $id) {
        $review = Review::find($id);

        if(empty($review)) {
            return '';
        } else {

            $d_id = Request::input('d_id');

            $item = User::find($d_id);

            if(empty($item)) {
                $item = $review->dentist_id ? User::find($review->dentist_id) : User::find($review->clinic_id);
            }           

            return $this->ShowView('popups.detailed-review-content', [
                'item' => $item,
                'review' => $review,
                'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
                'my_downvotes' => !empty($this->user) ? $this->user->unusefulVotesForDenist($item->id) : null,            
            ]);
        }
    }

    /**
     * write a video review
     */
    public function youtube($locale=null) {

        if(!empty($this->user)) {
            $fn = microtime(true).'-'.$this->user->id;
            $fileName   = storage_path(). '/app/public/'.$fn.'.webm';

            if ($this->request->hasFile('qqfile')) {
                $image      = $this->request->file('qqfile');
                copy($image, $fileName);
            } else {
                dd('upload a video first');
            }

            // Define an object that will be used to make all API requests.
            $client = $this->getClient();

            $service = new \Google_Service_YouTube($client);

            if (isset($_SESSION['token'])) {
                $client->setAccessToken($_SESSION['token']);
            }

            if (!$client->getAccessToken()) {
                print("no access token");
                exit;
            }

            $url = $this->videosInsert($client,
                $service,
                $fileName,
                array('snippet.categoryId' => '22',
                       'snippet.defaultLanguage' => '',
                       'snippet.description' => $this->user->getNames().'\'s video review on ',
                       'snippet.tags[]' => '',
                       'snippet.title' => 'Dentist review by '.$this->user->getNames(),
                       'status.embeddable' => '',
                       'status.license' => '',
                       'status.privacyStatus' => 'unlisted',
                       'status.publicStatsViewable' => ''),
                'snippet,status', array());

            return Response::json( [
                'url' => $url
            ] );

        }

        print("no user");
        exit;
    }

    /**
     * show dentist's profile
     * white a review
     */
    public function list($locale=null, $slug, $claim_id = false) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        if ($slug == 'dr-vadivelan-jeyachandran') {
            return redirect( getLangUrl('dentist/vadivelan-jeyachandran'), 301 );
        }

        $review_id = request('review_id');

        if (!empty($this->admin)) {
            $item = User::where('slug', 'LIKE', $slug)->first();
        } else {
            $item = User::where('slug', 'LIKE', $slug)->whereNull('self_deleted')->first();
        }

        if(empty($item)) {
            $old_slug = OldSlug::where('slug', 'LIKE', $slug)->first();

            if (!empty($old_slug)) {
                $item = User::find($old_slug->user_id);
                return redirect( getLangUrl('dentist/'.$item->slug), 301 );
            }
        }

        if(empty($item) || !$item->is_dentist) {
            return redirect( getLangUrl('page-not-found') );
        }

        $isTrusted = !empty($this->user) ? $this->user->wasInvitedBy($item->id) : false;
        $canAskDentist = !empty($this->user) ? $this->user->canAskDentist($item->id) : true;
        $questions = Question::with('translations')->get();

        if(Request::isMethod('post')) {
            
            $ret = array(
                'success' => false
            );

            if(!empty($this->user)) {

                $validator_arr = [
                    'answer' => ['required_without:youtube_id'],
                    'youtube_id' => ['required_without:answer'],
                    'treatments' => ['required']
                ];
                foreach ($questions as $question) {

                    if(!empty(Request::input( 'dentist_clinics' )) && Request::input('dentist_clinics') == 'own') {
                        $validator_arr['option.4.0'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.4.1'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.4.2'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.4.3'] = ['required', 'numeric', 'min:1', 'max:5'];

                        $validator_arr['option.6.0'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.6.1'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.6.2'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.6.3'] = ['required', 'numeric', 'min:1', 'max:5'];

                        $validator_arr['option.7.0'] = ['required', 'numeric', 'min:1', 'max:5'];
                        $validator_arr['option.7.1'] = ['required', 'numeric', 'min:1', 'max:5'];
                    } else {
                        $opts = json_decode($question['options'], true);

                        foreach ($opts as $i => $nosense) {
                            $validator_arr['option.'.$question->id.'.'.$i] = ['required', 'numeric', 'min:1', 'max:5'];
                        }                    
                    }
                }

                $validator = Validator::make(Request::all(), $validator_arr);

                if ($validator->fails()) {

                    $msg = $validator->getMessageBag()->toArray();
                    $ret['messages'] = [];
                    foreach ($msg as $field => $errors) {
                        $ret['messages'][$field] = implode(', ', $errors);
                    }           

                    return Response::json( $ret );
                } else {

                    $real_text = strip_tags(Request::input( 'answer' ));
                    $real_text_words = explode(' ', $real_text);
                    if( empty(Request::input( 'youtube_id' )) && (mb_strlen($real_text)<50 || count($real_text_words)<10) ) {
                        $ret['short_text'] = true;
                        return Response::json( $ret );

                    }

                    $ret['valid_input'] = true;

                    if( !$this->user->is_dentist) {

                        $old_review = $this->user->hasReviewTo($item->id);

                        if( $this->user->loggedFromBadIp() ) {
                            $ul = new UserLogin;
                            $ul->user_id = $this->user->id;
                            $ul->ip = User::getRealIp();
                            $ul->platform = 'trp';
                            $ul->country = \GeoIP::getLocation()->country;

                            $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                            $dd = new DeviceDetector($userAgent);
                            $dd->parse();

                            if ($dd->isBot()) {
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
                            $action->reason = 'Automatically - Bad IP (Writing review)';
                            $action->actioned_at = Carbon::now();
                            $action->save();
                            
                            Auth::guard('web')->user()->logoutActions();
                            Auth::guard('web')->user()->removeTokens();
                            Auth::guard('web')->logout();

                            $ret['success'] = false;
                            $ret['valid_input'] = false;
                            $ret['redirect'] = 'https://account.dentacoin.com/account-on-hold?platform=trusted-reviews&on-hold-type=bad_ip&key='.urlencode(GeneralHelper::encrypt($u_id));

                            $token = GeneralHelper::encrypt(session('login-logged-out'));
                            $imgs_urls = [];
                            foreach( config('platforms') as $k => $platform ) {
                                if( !empty($platform['url']) && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  ) {
                                    $imgs_urls[] = '//'.$platform['url'].'/custom-cookie?logout-token='.urlencode($token);
                                }
                            }
                            $imgs_urls[] = '//vox.dentacoin.com/custom-cookie?logout-token='.urlencode($token);

                            $ret['imgs_urls'] = $imgs_urls;

                            return Response::json( $ret );
                        } else if( $this->user->cantSubmitReviewToSameDentist($item->id) ) {
                            ; //dgd
                        } else {

                            if($old_review && $old_review->status=='pending') {
                                $review = $old_review;
                            } else {
                                $review = new Review;
                                $review->user_id = $this->user->id;
                                $review->review_to_id = $item->id;

                                if($item->is_clinic) {
                                    $review->clinic_id = $item->id;
                                    if(!empty(Request::input( 'clinic_dentists' ))) {
                                        $review->dentist_id = Request::input( 'clinic_dentists' );
                                    }
                                } else {
                                    $review->dentist_id = $item->id;
                                    if(!empty(Request::input( 'dentist_clinics' )) && Request::input('dentist_clinics') != 'own') {
                                        $review->clinic_id = Request::input( 'dentist_clinics' );
                                    } else if (!empty(Request::input( 'dentist_clinics' )) && Request::input('dentist_clinics') == 'own') {
                                        $review->team_own_practice = true;
                                    }
                                }
                            }

                            $review->rating = 0;
                            $review->title = strip_tags(Request::input( 'title' ));
                            $review->answer = strip_tags(Request::input( 'answer' ));
                            $review->youtube_id = strip_tags(Request::input( 'youtube_id' ));
                            $review->verified = !empty($isTrusted);
                            $review->status = 'pending';
                            $review->secret_id = null;
                            $review->treatments = Request::input( 'treatments' );
                            $review->save();

                            $item->review_notification = true;
                            $item->save();

                            $answer_rates = [];
                            $answer_three_qs_rates = [];

                            foreach ($questions as $question) {

                                if ($question->id == 4 || $question->id == 6 || $question->id == 7) {
                                    $answer_three_qs_rates[$question->id] = 0;
                                    $options_three = json_decode($question['options'], true);

                                    foreach ($options_three as $i => $n) {
                                        $re = Request::input( 'option.'.$question->id.'.'.$i );
                                        $answer_three_qs_rates[$question->id] += $re;
                                    }

                                    $answer_three_qs_rates[$question->id] /= count($options_three);
                                }

                                $answer_rates[$question->id] = 0;
                                $option_answers = [];
                                $options = json_decode($question['options'], true);

                                foreach ($options as $i => $nosense) {
                                    $r = Request::input( 'option.'.$question->id.'.'.$i );
                                    $option_answers[] = $r;
                                    $answer_rates[$question->id] += $r;
                                }

                                $answer_rates[$question->id] /= count($options);
                                
                                if($old_review && $old_review->status=='pending') {
                                    $answer = ReviewAnswer::where([
                                        ['review_id', $review->id],
                                        ['question_id', $question->id],
                                    ])->first();
                                } else {
                                    $answer = new ReviewAnswer;
                                }
                                $answer->review_id = $review->id;
                                $answer->question_id = $question->id;
                                $answer->options = json_encode($option_answers);
                                $answer->save();
                            }

                            $review->rating = array_sum($answer_rates) / (!empty(Request::input( 'dentist_clinics' )) && Request::input( 'dentist_clinics' ) == 'own' ? 3 : count($answer_rates));
                            $review->team_doctor_rating = !empty(Request::input( 'dentist_clinics' )) || !empty(Request::input( 'clinic_dentists' )) ? array_sum($answer_three_qs_rates) / 3 : null;
                            $review->save();
                            
                            if (!empty($review->dentist_id)) {
                                $the_dentist = User::find($review->dentist_id);
                                $the_dentist->recalculateRating();
                                $the_dentist->hasimage_social = false;
                                $the_dentist->save();
                            }

                            if (!empty($review->clinic_id)) {
                                $the_clinic = User::find($review->clinic_id);
                                $the_clinic->recalculateRating();
                                $the_clinic->hasimage_social = false;
                                $the_clinic->save();
                            }
                            
                            //Send & confirm
                            $is_video = $review->youtube_id ? '_video' : '';
                            $amount = $review->verified ? Reward::getReward('review'.$is_video.'_trusted') : Reward::getReward('review'.$is_video);
                            
                            if(!$is_video && $review->verified) {
                                $reward = new DcnReward();
                                $reward->user_id = $this->user->id;
                                $reward->platform = 'trp';
                                $reward->reward = $amount;
                                $reward->type = 'review';
                                $reward->reference_id = $review->id;

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

                            $review->status = 'accepted';
                            $review->save();

                            if(!$review->youtube_id) {
                                $review->afterSubmitActions();
                            }

                            $invites = UserInvite::where('user_id', $item->id )->where('invited_id', $this->user->id)->where('review', 1)->whereNull('completed')->get();
                            if (!empty($invites)) {
                                foreach ($invites as $invite) {
                                    $invite->completed = true;
                                    $invite->save();
                                }
                            }

                            if($this->patientSuspicious($this->user->id)) {

                                $notifications = $this->user->website_notifications;

                                if(!empty($notifications)) {
                                    
                                    if (($key = array_search('trp', $notifications)) !== false) {
                                        unset($notifications[$key]);
                                    }

                                    $this->user->website_notifications = $notifications;
                                    $this->user->save();
                                }

                                $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

                                $request_body = new \stdClass();
                                $request_body->recipient_emails = [$this->user->email];
                                
                                $trp_group_id = config('email-preferences')['product_news']['trp']['sendgrid_group_id'];
                                $response = $sg->client->asm()->groups()->_($trp_group_id)->suppressions()->post($request_body);

                                $ret['success'] = false;
                                $ret['ban'] = true;
                            }

                            if(Review::where('user_id', $this->user->id)->count() == 1) {
                                $ret['social_profile'] = true;
                            }

                            $ret['success'] = true;
                            $ret['review_id'] = $review->id;
                        }
                    }
                }
            }

            return Response::json( $ret );
        }

        if (!empty($this->user) && ($this->user->id != $item->id)) {

            if (!session('pageview-'.$item->id)) {
                $pageview = new DentistPageview;
                $pageview->dentist_id = $item->id;
                $pageview->user_id = $this->user->id;
                $pageview->ip = User::getRealIp();
                $pageview->save();
            }
            session(['pageview-'.$item->id => true]);
        }

        $reviews = $item->reviews_in();
        if($review_id) {
            $review = Review::find($review_id);
            if(!empty($review) && !empty($reviews)) {
                $rid = $review->id;
                $reviews = $reviews->reject(function ($value, $key) use ($rid) {
                    return $value->id == $rid;
                });
                $reviews = collect([$review])->merge($reviews);
            }
        }

        $aggregated = [];
        if (count($reviews)) {

            $aggregated_count_opt = [];

            foreach ($reviews as $rev) {
                foreach($rev->answers as $answer) {

                    if ($item->my_workplace_approved->isEmpty() || ($item->my_workplace_approved->isNotEmpty() && ($answer->question_id == 4 || $answer->question_id == 6 || $answer->question_id == 7))) {

                        if(!isset($aggregated[$answer->question['label']])) {
                            $aggregated[$answer->question['label']] = 0;
                        }

                        if(!isset($aggregated_count_opt[$answer->question['label']])) {
                            $aggregated_count_opt[$answer->question['label']] = 0;
                        }

                        $arr_sum = array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));

                        if(!empty($arr_sum)) {
                            $aggregated_count_opt[$answer->question['label']] += 1;
                        }

                        $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                    }
                }
            }

            foreach ($aggregated_count_opt as $key => $value) {
                $aggregated[$key] /= $value;
            }
        }

        $aggregated_rates = [];
        $aggregated_rates_total = [];
        $count = $reviews->count();
        if($count) {
            foreach ($reviews as $review) {
                foreach($review->answers as $answer) {
                    if ($item->my_workplace_approved->isEmpty() || ($item->my_workplace_approved->isNotEmpty() && ($answer->question_id == 4 || $answer->question_id == 6 || $answer->question_id == 7))) {

                        if(empty($aggregated_rates[$answer->question->id])) {
                            $aggregated_rates[$answer->question->id] = [];
                        }
                            
                        $opts = json_decode($answer->options, true);
                        foreach(json_decode($answer->question['options'], true) as $i => $option) {
                            if(empty($aggregated_rates[$answer->question->id][$i])) {
                                $aggregated_rates[$answer->question->id][$i] = 0;
                            }
                            $aggregated_rates[$answer->question->id][$i] += $opts[$i];
                        }
                    }
                }
            }

            foreach ($aggregated_rates as $key => $value) {
                foreach ($value as $kk => $vv) {
                    $aggregated_rates[$key][$kk] = $vv/$count;
                }
            }
            foreach ($aggregated_rates as $key => $value) {
                $aggregated_rates_total[$key] = array_sum($value)/count($value);
            }
        }

        $dentist_limit_reached = !empty($this->user) ? $this->user->cantReviewDentist($item->id) : null;
        $has_asked_dentist = $this->user ? $this->user->hasAskedDentist($item->id) : null;

        if( $this->user ) {
            $review_reward = $isTrusted ? Reward::getReward('review_trusted') : Reward::getReward('review');
            $review_reward_video = $isTrusted ? Reward::getReward('review_video_trusted') : Reward::getReward('review_video');
        } else {
            $review_reward = $review_reward_video = 0;
        }

        $social_image = $item->getSocialCover();
        $is_review = false;
        if( request('review_id') && $current_review = $reviews->where('id', request('review_id'))->first() ) {
            if(!empty($current_review->dentist_id) && !empty($current_review->clinic_id)) {
                $current_review->generateSocialCover($item->id);
            }
            
            $social_image = $current_review->getSocialCover($item->id);
            $is_review = true;
        }

        $patient_asks = false;

        if (!empty($this->user) && $this->user->asks->isNotEmpty()) {
            foreach ($this->user->asks as $p_ask) {
                if ($p_ask->status == 'waiting') {
                    $patient_asks = true;
                }
            }
        }

        $strength_arr = null;
        $completed_strength = null;
        if ($this->user) {
            $strength_arr = UserStrength::getStrengthPlatform('trp', $this->user);
            $completed_strength = $this->user->getStrengthCompleted('trp');
        }

        if(!empty($this->user)) {
            $dent_id = $item->id;
            $reviews = Review::where(function($query) use ($dent_id) {
                $query->where( 'dentist_id', $dent_id)->orWhere('clinic_id', $dent_id);
            })->where('user_id', $this->user->id)
            ->first();

            if (!empty($reviews)) {
                $writes_review = true;
            } else {
                $writes_review = false;
            }
            
        } else {
            $writes_review = false;
        }       

        $view_params = [
            'strength_arr' => $strength_arr,
            'completed_strength' => $completed_strength,
            'noIndex' => $item->address ? false : true,
            'item' => $item,
            'is_trusted' => $isTrusted,
            'canAskDentist' => $canAskDentist,
            'my_review' => !empty($this->user) ? $this->user->cantSubmitReviewToSameDentist($item->id) : null,
            'writes_review' => $writes_review,
            'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
            'my_downvotes' => !empty($this->user) ? $this->user->unusefulVotesForDenist($item->id) : null,
            'questions' => $questions,
            'reviews' => $reviews,
            'review_reward' => $review_reward,
            'review_reward_video' => $review_reward_video,
            'review_limit_reached' => !empty($this->user) ? $this->user->getReviewLimits() : null,
            'dentist_limit_reached' => $dentist_limit_reached,
            'has_asked_dentist' => $has_asked_dentist,
            'patient_asks' => $patient_asks,
            'aggregated_rates' => $aggregated_rates,
            'aggregated_rates_total' => $aggregated_rates_total,
            'aggregated' => $aggregated,
            'social_image' => $social_image,
            'canonical' => $item->getLink(),
            'og_url' => $item->getLink().($review_id ? '?review_id='.$review_id : ''),
            'countries' => Country::with('translations')->get(),
            'video_reviews_stopped' => StopVideoReview::find(1)->stopped ? true : false,
            'js' => [
                'user.js',
                'search.js',
            ],
            'css' => [
                'trp-users.css',
            ],
        ];

        if(!empty($this->user)) {
            $view_params['jscdn'][] = '//vjs.zencdn.net/6.4.0/video.min.js';
            $view_params['jscdn'][] = '//cdn.WebRTC-Experiment.com/RecordRTC.js';
            $view_params['jscdn'][] = '//webrtc.github.io/adapter/adapter-latest.js';
            $view_params['js'][] = '../js/videojs.record.min.js';
            $view_params['js'][] = '../js/codemirror.min.js';
            $view_params['js'][] = '../js/codemirror-placeholder.js';
            $view_params['js'][] = 'user-logged.js';
            $view_params['css'][] = 'codemirror.css';
            $view_params['css'][] = 'trp-users-logged.css';
        }

        if(!empty($this->user) && $this->user->id == $item->id) {
            $view_params['js'][] = '../js/jquery-ui.min.js';
            $view_params['js'][] = '../js/croppie.min.js';
            $view_params['css'][] = 'croppie.css';

            $item->review_notification = false;
            $item->save();
        }

        if(!empty($this->user) && $this->user->is_clinic) {
            $view_params['js'][] = 'branch.js';
        }

        if($item->photos->isNotEmpty()) {
            if(!empty($this->user) && $this->user->id == $item->id) {
                $load_lightbox = 'true';
                $view_params['css'][] = 'lightbox.css';
                $view_params['js'][] = '../js/lightbox.js';
            } else {
                if($item->reviews_in()->isEmpty()) {
                    $load_lightbox = 'true';
                    $view_params['css'][] = 'lightbox.css';
                    $view_params['js'][] = '../js/lightbox.js';
                } else {
                    $load_lightbox = 'false';
                }
            }
        } else {
            $load_lightbox = 'true';
        }
        $view_params['load_lightbox'] = $load_lightbox;

        $dont_initialize_flickity = 'false';
        if((!empty($this->user) && $this->user->id == $item->id) || ($item->photos->isNotEmpty() || $item->teamApproved->isNotEmpty() || $item->invites_team_unverified->isNotEmpty())) {

            if(!empty($this->user) && $this->user->id == $item->id) {
                $load_flickity = 'true';
                $view_params['css'][] = 'flickity.min.css';
                $view_params['js'][] = '../js/flickity.min.js';
            } else {
                if($item->reviews_in()->isEmpty()) {
                    $load_flickity = 'true';
                    $view_params['css'][] = 'flickity.min.css';
                    $view_params['js'][] = '../js/flickity.min.js';
                } else {
                    $load_flickity = 'false';
                }
            }
        } else {
            $load_flickity = 'true';
            $dont_initialize_flickity = 'true';
        }
        $view_params['load_flickity'] = $load_flickity;
        $view_params['dont_initialize_flickity'] = $dont_initialize_flickity;

        if( $is_review ) {
            $seos = PageSeo::find(33);

            $seo_title = str_replace(':dentist_name', $item->getNames(), $seos->seo_title);
            $seo_title = str_replace(':user_name', $current_review->user->getNames(), $seo_title);

            $seo_description = str_replace(':review_title', $current_review->title, $seos->seo_description);
            $seo_description = str_replace(':review_text', $current_review->answer, $seo_description);

            $social_title = str_replace(':dentist_name', $item->getNames(), $seos->social_title);
            $social_title = str_replace(':user_name', $current_review->user->getNames(), $social_title);

            $social_description = str_replace(':review_title', $current_review->title, $seos->social_description);
            $social_description = str_replace(':review_text', $current_review->answer, $social_description);

            $view_params['seo_title'] = $seo_title;
            $view_params['seo_description'] = $seo_description;
            $view_params['social_title'] = $social_title;
            $view_params['social_description'] = $social_description;

        } else {
            $seos = PageSeo::find(32);

            $seo_title = str_replace(':name', $item->getNames(), $seos->seo_title);
            $seo_title = str_replace(':country', $item->country ? $item->country->name : '', $seo_title);
            $seo_title = str_replace(':city', $item->city_name ? $item->city_name : '', $seo_title);

            $seo_description = str_replace(':name', $item->getNames(), $seos->seo_description);
            $seo_description = str_replace(':city', $item->city_name ? $item->city_name : '', $seo_description);
            $seo_description = str_replace(':reviews_number', intval($item->ratings), $seo_description);

            $social_title = str_replace(':name', $item->getNames(), $seos->social_title);
            $social_description = str_replace(':name', $item->getNames(), $seos->social_description);

            $view_params['seo_title'] = $seo_title;
            $view_params['seo_description'] = $seo_description;
            $view_params['social_title'] = $social_title;
            $view_params['social_description'] = $social_description;
        }

        $addGM = false;
        if($item->lat && $item->lon) {
            $addGM = true;
        }

        if(!empty($this->user) && $this->user->id==$item->id) {
            $view_params['js'][] = '../js/upload.js';
            $view_params['hours'] = [
            ];
            for($i=0;$i<=23;$i++) {
                $h = str_pad($i, 2, "0", STR_PAD_LEFT);
                $view_params['hours'][$h] = $h;
            }

            $view_params['minutes'] = [
                '00' => '00',
                '10' => '10',
                '20' => '20',
                '30' => '30',
                '40' => '40',
                '50' => '50',
            ];
            $view_params['js'][] = 'address.js';
            $addGM = true;
        }

        //always not loaded now
        if( $addGM && $item->reviews_in()->isEmpty() ) {
            // $view_params['jscdn'][] = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en';
            $view_params['click_on_map'] = 'true';
        } else {
            $view_params['click_on_map'] = 'false';
        }

        $view_params['schema'] = [
            "@context" => "http://schema.org",
            "@type" => 'Dentist',
            "name" => $item->getNames(),
            "image" => $item->getImageUrl(true),
        ];

        if (!empty($item->categories->isNotEmpty())) {
            $view_params['schema']["MedicalSpecialty"] = array_values($item->parseCategories( $this->categories ));
        }

        if (!empty($item->phone)) {
            $view_params['schema']["telephone"] = $item->getFormattedPhone(true);
        }

        if (!empty($item->address)) {
            $view_params['schema']["address"] = [
                "@type" => "PostalAddress",
                "streetAddress" => $item->address,
                "addressLocality" => $item->city_name,
                'addressCountry' => $item->country->code,
            ];
        }

        if (!empty($item->state_name)) {
            $view_params['schema']["address"]["addressRegion"] = $item->state_name;
        }

        if (!empty($item->zip)) {
            $view_params['schema']["address"]["postalCode"] = $item->zip;
        }

        if(!empty($item->lat) && !empty($item->lon) ){
            $view_params['schema']["hasMap"] = "https://www.google.com/maps/@".$item->lat.",".$item->lon.",15z";
        }

        if(!empty($item->short_description)) {
            $short_description = $item->short_description;

        } else {
            if($item->is_clinic) {
                $short_description = trans('trp.page.user.short_description.clinic', ['location' => ($item->city_name ? $item->city_name.', ' : '').($item->state_name ? $item->state_name.', ' : '').($item->country_id ? $item->country->name : '') ]);

                if($item->categories->isNotEmpty()) {
                    $short_description.= ' '.trans('trp.page.user.short_description.categories', ['categories' => strtolower(implode(', ', $item->parseCategories($this->categories))) ]);
                }
            } else {

                $short_description = trans('trp.page.user.short_description.dentist', ['location' => ($item->city_name ? $item->city_name.', ' : '').($item->state_name ? $item->state_name.', ' : '').($item->country_id ? $item->country->name : '') ]);
                
                if($item->categories->isNotEmpty()) {
                    $short_description.= ' '.trans('trp.page.user.short_description.categories', ['categories' => strtolower(implode(', ', $item->parseCategories($this->categories))) ]);
                }
            }
        }

        $view_params['schema']["description"] =  $short_description;

        if (!empty($item->website)) {
            $view_params['schema']["url"] = $item->website;
        }

        if (!empty($item->socials)) {
            $view_params['schema']["sameAs"] = array_values($item->socials);
        }

        if($item->reviews_in_standard()->isNotEmpty() ) {
            $view_params['schema']["aggregateRating"] = [
                "@type" => "AggregateRating",
                "ratingCount" => intval($item->ratings),
                "ratingValue" => $item->avg_rating,
            ];
            
            $item_reviews = [];
            foreach($item->reviews_in_standard() as $review) {
                $item_reviews[] = [
                    "@type" => "Review",
                    "author" => [
                        "@type" => "Person",
                        "name" => $review->user ? $review->user->name : '',
                    ],
                    "datePublished" => $review->created_at->format('Y-m-d'),
                    "reviewBody" => $review->answer,
                    "reviewRating" => [
                        "@type" => "Rating",
                        "bestRating" => 5,
                        "ratingValue" => !empty($review->team_doctor_rating) && ($item->id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating,
                        "worstRating" => 1,
                    ]
                ];
            }
            $view_params['schema']["review"] = $item_reviews;
        }

        if (!empty($item->accepted_payment)) {
            $view_params['schema']["paymentAccepted"] = $item->parseAcceptedPayment( $item->accepted_payment );
        }

        $openingHours = [
            1 => 'Mo',
            2 => 'Tu',
            3 => 'We',
            4 => 'Th',
            5 => 'Fr',
            6 => 'Sa',
            7 => 'Su',
        ];

        $openingHoursSpecification = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        if (!empty($item->work_hours)) {
            $hours_arr = [];
            $hours_specif = [];

            $wh = is_array($item->work_hours) ? $item->work_hours : json_decode(str_replace("\'","",$item->work_hours), true);
            foreach ($wh as $k => $wh) {
                $hours_arr[] = $openingHours[$k].' '.$wh[0].'-'.$wh[1];

                $hours_specif[] = [
                    "@type" => "OpeningHoursSpecification",
                    "dayOfWeek" => "http://schema.org/".$openingHoursSpecification[$k],
                    "opens" => $wh[0],
                    "closes" => $wh[1],
                ];
            }

            $view_params['schema']["openingHours"] = $hours_arr;
            $view_params['schema']["openingHoursSpecification"] = $hours_specif;
        }

        if(!empty($item->lat) && !empty($item->lon) ){
            $view_params['schema']["geo"] = [
                "@type" => "GeoCoordinates",
                "latitude" => $item->lat,
                "longitude" => $item->lon,
            ];
        }

        if (!empty($this->user) && ($this->user->id == $item->id)) {
            $view_params['extra_body_class'] = 'strength-pb';
        }

        $claim_user = !empty($claim_id) ? User::find($claim_id) : null;
        $view_params['claim_user'] = $claim_user;
        
        return $this->ShowView('user', $view_params);
    }

    /**
     * dentist claims his profile
     */
    public function claim_dentist($locale=null, $slug, $id) {

        $user = User::find($id);

        if(!empty($user)) {

            if (!empty($user->old_unclaimed_profile)) {

            } else {

                if($user->status != 'added_approved' && $user->status != 'admin_imported' && $user->status != 'added_by_clinic_unclaimed' && $user->status != 'added_by_dentist_unclaimed') {
                    return redirect( getLangUrl('/') );
                }
            }

            if(Request::isMethod('post')) {
                $validator = Validator::make(Request::all(), [
                    'name' => array('required', 'min:3'),
                    'email' => 'sometimes|required|email',
                    'phone' =>  array('sometimes', 'regex: /^[- +()]*[0-9][- +()0-9]*$/u'),
                    'job' =>  'sometimes|required|string',
                    'explain-related' => 'sometimes|required|string|max:300',
                    'password' => array('required', 'min:6'),
                    'password-repeat' => 'required|same:password',
                    'agree' => 'required|accepted',
                ]);

                if ($validator->fails()) {

                    $msg = $validator->getMessageBag()->toArray();
                    $ret = array(
                        'success' => false,
                        'messages' => array()
                    );

                    foreach ($msg as $field => $errors) {
                        $ret['messages'][$field] = implode(', ', $errors);
                    }

                    return Response::json( $ret );
                } else {

                    if(GeneralHelper::validateLatin(Request::input('name')) == false) {
                        return Response::json( [
                            'success' => false, 
                            'messages' => [
                                'name' => trans('trp.common.invalid-name')
                            ]
                        ] );
                    }

                    if (!empty(Request::input('job'))) {

                        $claimsFromSameIp = DentistClaim::where('ip', 'LIKE', User::getRealIp() )->count();

                        if($claimsFromSameIp > 3) {
                            return Response::json( [
                                'success' => false,
                                'message' => trans('trp.popup.popup-claim-profile.error-ip', ['link' => '<a href="mailto:reviews@dentacoin.com">', 'endlink' => '</a>'])
                            ] );
                        }

                        $fromm = !empty(Request::input('email')) ? 'from site' : 'from mail';

                        $claim = new DentistClaim;
                        $claim->dentist_id = $user->id;
                        $claim->name = Request::input('name');
                        $claim->email = Request::input('email') ? Request::input('email') : $user->email;
                        $claim->phone = !empty(Request::input('phone')) ? Request::input('phone') : '';
                        $claim->password = bcrypt(Request::input('password'));
                        $claim->job = Request::input('job');
                        $claim->explain_related = empty(Request::input('explain-related')) ? Request::input('explain-related') : '';
                        $claim->status = 'waiting';
                        $claim->from_mail = !empty(Request::input('email')) ? false : true;
                        $claim->ip = User::getRealIp();
                        $claim->save();

                        if(!empty(Request::input('email'))) {

                            $existing_anonymous = AnonymousUser::where('email', 'LIKE', Request::input('email'))->first();

                            if(empty($existing_anonymous)) {
                                $new_anonymous_user = new AnonymousUser;
                                $new_anonymous_user->email = Request::input('email');
                                $new_anonymous_user->save();
                            }
                        }

                        if(!empty($claim->phone)) {
                            $mtext = 'Dentist claimed his profile '.$fromm.'<br/>
                            Name: '.$claim->name.' <br/>
                            Phone: '.$claim->phone.' <br/>
                            Email: '.$claim->email.' <br/>
                            Job position: '.$claim->job.' <br/>
                            Explain how dentist is related to this office: '.$claim->explain_related.' <br/>
                            Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id;
                        } else {
                            $mtext = 'Old Added by Patient Dentist claimed his profile '.$fromm.'<br/>
                            Name: '.$claim->name.' <br/>
                            Job position: '.$claim->job.' <br/>
                            Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id;
                        }                    

                        Mail::send([], [], function ($message) use ($mtext, $user) {
                            $sender = config('mail.from.address');
                            $sender_name = config('mail.from.name');

                            $message->from($sender, $sender_name);
                            $message->to( 'betina.bogdanova@dentacoin.com' );
                            $message->to( 'petya.ivanova@dentacoin.com' );
                            $message->replyTo($user->email, $user->getNames());
                            $message->subject('Invited Dentist Claimed His Profile');
                            $message->setBody($mtext, 'text/html'); // for HTML rich messages
                        });

                        return Response::json( [
                            'success' => true,
                            'reload' => false,
                        ] );
                        
                    } else {

                        if($user->status == 'admin_imported') {

                            $claim = new DentistClaim;
                            $claim->dentist_id = $user->id;
                            $claim->name = Request::input('name');
                            $claim->email = $user->email;
                            $claim->phone = '';
                            $claim->password = bcrypt(Request::input('password'));
                            $claim->status = 'approved';
                            $claim->from_mail = true;
                            $claim->ip = User::getRealIp();
                            $claim->save();

                            $mtext = 'Dentist claimed his profile from short link. The profile was automatically approved.<br/>
                            Name: '.$claim->name.' <br/>
                            Email: '.$claim->email.' <br/>
                            Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id;

                            Mail::send([], [], function ($message) use ($mtext, $user) {
                                $sender = config('mail.from.address');
                                $sender_name = config('mail.from.name');

                                $message->from($sender, $sender_name);
                                $message->to( 'betina.bogdanova@dentacoin.com' );
                                $message->to( 'petya.ivanova@dentacoin.com' );
                                $message->subject('Imported Dentist Claimed His Profile');
                                $message->setBody($mtext, 'text/html'); // for HTML rich messages
                            });
                        } else {
                            $user->name = Request::input('name');
                        }

                        $user->phone = Request::input('phone');
                        $user->password = bcrypt(Request::input('password'));
                        if($user->status == 'admin_imported') {
                            $user->status = 'approved';
                        } else {
                            if($user->status == 'added_by_clinic_unclaimed') {
                                $user->status = 'added_by_clinic_claimed';
                            } else {
                                $user->status = 'added_by_dentist_claimed';
                            }
                        }
                        $user->save();

                        $user->product_news = ['dentacoin', 'trp'];
                        $user->save();

                        //add to dcn sendgrid list
                        $sg = new \SendGrid(env('SENDGRID_PASSWORD'));

                        $user_info = new \stdClass();
                        $user_info->email = $user->email;
                        $user_info->title = $user->title ? config('titles')[$user->title] : 'Dr';
                        $user_info->first_name = explode(' ', $user->name)[0];
                        $user_info->last_name = isset(explode(' ', $user->name)[1]) ? explode(' ', $user->name)[1] : '';
                        $user_info->type = 'dentist';
                        $user_info->partner = $user->is_partner ? 'yes' : 'no';

                        $request_body = [
                            $user_info
                        ];
                        $response = $sg->client->contactdb()->recipients()->post($request_body);
                        $recipient_id = isset(json_decode($response->body())->persisted_recipients) ? json_decode($response->body())->persisted_recipients[0] : null;

                        //add to list
                        if($recipient_id) {

                            $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
                            $list_id = config('email-preferences')['product_news']['dentacoin']['sendgrid_list_id'];
                            $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
                        }

                        $existing_anonymous = AnonymousUser::where('email', 'LIKE', $user->email)->first();
                        if(!empty($existing_anonymous)) {
                            AnonymousUser::destroy($existing_anonymous->id);
                        }

                        $user->sendGridTemplate(26, [], 'trp');

                        if($user->status == 'added_by_dentist_claimed') {

                            $dent = User::find($user->invited_by);

                            if(!empty($dent) && $dent->status == 'approved') {
                                
                                $user->sendTemplate(34, [
                                    'dentist-name' => $dent->getNames()
                                ], 'trp');
                            }
                        }

                        Auth::login($user);

                        return Response::json( [
                            'success' => true,
                            'reload' => true,
                        ] );
                    }
                }
            }
            return $this->list($locale, $slug, $user->status == 'admin_imported' && empty(request('long')) ? $id : false);
        }
        return redirect( getLangUrl('page-not-found') );
    }

    /**
     * patient asks a dentist for verification
     */
    public function ask($locale=null, $slug, $verification=null) {

        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(!empty($item)) {

            if(!empty($this->user) && !$this->user->cantReviewDentist($item->id)) {

                $ask = $this->user->canAskDentist($item->id);
                $is_patient_to_dentist = UserInvite::where('user_id', $item->id )->where('invited_id', $this->user->id)->first();

                if(!empty($ask)) {
                    $last_ask = UserAsk::where('user_id', $this->user->id)->where('dentist_id', $item->id)->first();

                    if(!empty($last_ask)) {
                        $last_ask->created_at = Carbon::now();
                        $last_ask->status = 'waiting';
                        if (!empty($verification)) {
                            $last_ask->on_review = true;
                            $last_ask->review_id = $verification;
                        }
                        $last_ask->save();
                    } else {
                        $ask = new UserAsk;
                        $ask->user_id = $this->user->id;
                        $ask->dentist_id = $item->id;
                        $ask->status = 'waiting';
                        if (!empty($verification)) {
                            $ask->on_review = true;
                            $ask->review_id = $verification;
                        }
                        $ask->save();
                    }

                    if (!empty($is_patient_to_dentist)) {

                        $substitutions = [
                            'patient_name' => $this->user->name,
                            "requests_link" => getLangUrl( '/' , null, 'https://reviews.dentacoin.com').'?'. http_build_query(['dcn-gateway-type'=>'dentist-login']),
                        ];

                        $item->sendGridTemplate(71, $substitutions, 'trp');
                    } else {
                        $item->sendTemplate( !empty($verification) ? 63 : 23 ,[
                            'patient_name' => $this->user->name,
                            'invitation_link' => $item->getLink()
                        ], 'trp' );
                    }

                    return Response::json( ['success' => true] );
                }
            }
        }

        return Response::json( ['success' => false] );
    }

    /**
     * review upvote
     */
    public function useful($locale=null, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review) && !empty($this->user)) {
            $myvotes = $this->user->usefulVotesForDenist($review->dentist_id);
            $mydownvotes = $this->user->unusefulVotesForDenist($review->dentist_id);

            $ret = [
                'success' => true,
                'downvote_image' => url('img-trp/thumbs-down.png'),
                'upvote_image' => url('img-trp/thumbs-up.png'),
                'downvote_status' => false,
                'upvote_status' => false,
            ];

            if(!in_array($review_id, $myvotes)) {
                $review->upvotes++;
                $review->save();
                $uv = new ReviewUpvote;
                $uv->review_id = $review_id;
                $uv->user_id = $this->user->id;
                $uv->save();

                $ret['upvote_image'] = url('img-trp/thumbs-up-color.png');
                $ret['upvote_status'] = true;

                if(in_array($review_id, $mydownvotes)) {

                    $review->downvotes--;
                    $review->save();

                    ReviewDownvote::where('review_id', $review_id )->where('user_id', $this->user->id )->delete();
                }
            } else {
                $review->upvotes--;
                $review->save();

                ReviewUpvote::where('review_id', $review_id )->where('user_id', $this->user->id )->delete();
            }

            $ret['downvotes'] = $review->downvotes;
            $ret['upvotes'] = $review->upvotes;
            return Response::json( $ret );
        }

        return Response::json( [
            'success' => false,
        ] );
    }

    /**
     * review downvote
     */
    public function unuseful($locale=null, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review)) {
            $myvotes = $this->user->unusefulVotesForDenist($review->dentist_id);
            $myupvotes = $this->user->usefulVotesForDenist($review->dentist_id);

            $ret = [
                'success' => true,
                'downvote_image' => url('img-trp/thumbs-down.png'),
                'upvote_image' => url('img-trp/thumbs-up.png'),
                'downvote_status' => false,
                'upvote_status' => false,
            ];

            if(!in_array($review_id, $myvotes)) {
                $review->downvotes++;
                $review->save();
                $uv = new ReviewDownvote;
                $uv->review_id = $review_id;
                $uv->user_id = $this->user->id;
                $uv->save();

                $ret['downvote_image'] = url('img-trp/thumbs-down-color.png');
                $ret['downvote_status'] = true;

                if(in_array($review_id, $myupvotes)) {
                    
                    $review->upvotes--;
                    $review->save();

                    ReviewUpvote::where('review_id', $review_id )->where('user_id', $this->user->id )->delete();
                }
            } else {
                $review->downvotes--;
                $review->save();

                ReviewDownvote::where('review_id', $review_id )->where('user_id', $this->user->id )->delete();
            }

            $ret['downvotes'] = $review->downvotes;
            $ret['upvotes'] = $review->upvotes;
            return Response::json( $ret );
        }

        return Response::json( [
            'success' => false,
        ] );
    }

    /**
     * review reply
     */
    public function reply($locale=null, $slug, $review_id) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        $review = Review::find($review_id);
        if(!empty($review) && ($this->user->id==$review->clinic_id || $this->user->id==$review->dentist_id ) ) {
            $review->reply = strip_tags(Request::input( 'reply' ));
            $review->save();
            $review->user->sendTemplate(8, [
                'review_id' => $review->id,
                'dentist_id' => $item->id
            ], 'trp');
        }

        return Response::json( ['success' => true, 'reply' => nl2br( $review->reply )] );
    }

    /**
     * Youtube boilerplate
     */
    function videosInsert($client, $service, $media_file, $properties, $part, $params) {
        $params = array_filter($params);
        $propertyObject = $this->createResource($properties); // See full sample for function
        $resource = new \Google_Service_YouTube_Video($propertyObject);
        $client->setDefer(true);
        $request = $service->videos->insert($part, $resource, $params);
        $client->setDefer(false);
        $response = $this->uploadMedia($client, $request, $media_file, 'video/*');
        return $response->id;
    }

    /**
     * Youtube boilerplate
     */
    function getClient() {
        $client = new \Google_Client();
        $client->setApplicationName('API Samples');
        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
        // Set to name/location of your client_secrets.json file.
        $client->setAuthConfig( storage_path() . '/client_secrets.json');
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = storage_path() . '/yt-oauth2.json';
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';

            if (isset($_GET['code'])) {

                $credentialsPath = storage_path() . '/yt-oauth2.json';
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

                // Store the credentials to disk.
                if(!file_exists(dirname($credentialsPath))) {
                    mkdir(dirname($credentialsPath), 0700, true);
                }
                file_put_contents($credentialsPath, json_encode($accessToken));
            }

            return;
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }

    /**
     * Add a property to the resource.
     */
    function addPropertyToResource(&$ref, $property, $value) {
        $keys = explode(".", $property);
        $is_array = false;
        foreach ($keys as $key) {
            // For properties that have array values, convert a name like
            // "snippet.tags[]" to snippet.tags, and set a flag to handle
            // the value as an array.
            if (substr($key, -2) == "[]") {
                $key = substr($key, 0, -2);
                $is_array = true;
            }
            $ref = &$ref[$key];
        }

        // Set the property value. Make sure array values are handled properly.
        if ($is_array && $value) {
            $ref = $value;
            $ref = explode(",", $value);
        } elseif ($is_array) {
            $ref = array();
        } else {
            $ref = $value;
        }
    }

    /**
     * Build a resource based on a list of properties given as key-value pairs.
     */
    function createResource($properties) {
        $resource = array();
        foreach ($properties as $prop => $value) {
            if ($value) {
                $this->addPropertyToResource($resource, $prop, $value);
            }
        }
        return $resource;
    }

    function uploadMedia($client, $request, $filePath, $mimeType) {
        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Create a MediaFileUpload object for resumable uploads.
        // Parameters to MediaFileUpload are:
        // client, request, mimeType, data, resumable, chunksize.
        $media = new \Google_Http_MediaFileUpload(
            $client,
            $request,
            $mimeType,
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));

        //dd(filesize($filePath));
        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($filePath, "rb");
        while (!$status && !feof($handle)) {
          $chunk = fread($handle, $chunkSizeBytes);
          $status = $media->nextChunk($chunk);
        }

        fclose($handle);
        return $status;
    }

    private function patientSuspicious($p_id) {
        $patient = User::find($p_id);

        if (!empty($patient)) {

            $current_month_reviews = Review::where('user_id', $patient->id )->where('created_at', '>', Carbon::now()->subDays(30))->get();
            
            if(!empty($current_month_reviews)) {

                if (count($current_month_reviews) == 3) {
                    $mtext = 'Patient - '.$patient->name.' writes his third review to different dentist. 
Link to patients\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$patient->id;

                    Mail::raw($mtext, function ($message) use ($patient) {
                        $sender = config('mail.from.address');
                        $sender_name = config('mail.from.name');

                        $message->from($sender, $sender_name);
                        $message->to( 'petya.ivanova@dentacoin.com' );
                        $message->to( 'donika.kraeva@dentacoin.com' );
                        $message->replyTo($patient->email, $patient->name);
                        $message->subject('Suspicious Patient Activity - 3 Reviews');
                    });
                }

                if (count($current_month_reviews) >= 6) {
                    $ban = new UserBan;
                    $ban->user_id = $patient->id;
                    $ban->domain = 'trp';
                    $ban->type = 'reviews';
                    $ban->save();

                    $patient->sendGridTemplate(70, null, 'trp');

                    return true;
                }
            }
        }
        return false;
    }

    /**
     * patient recommends dentist to a friend
     */
    public function recommend_dentist() {

        $validator = Validator::make(Request::all(), [
            'email' => ['required', 'email'],
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.failure') ] );
        } else {
            $recommendation = DentistRecommendation::where([
                ['user_id', $this->user->id],
                ['dentist_id', Request::Input('dentist-id')],
                ['friend_email', 'LIKE', Request::Input('email')],
            ])->first();

            $recommended_user = User::find(Request::Input('dentist-id'));
            $type = $recommended_user->is_clinic ? 'dental clinic' : 'dentist';

            $existing_patient = User::withTrashed()->where('email', 'LIKE', Request::Input('email') )->where('is_dentist', 0)->first();

            if($recommendation) {
                return Response::json(['success' => false, 'message' => trans('trp.popup.popup-recommend-dentist.аlready-recommended', ['type' => $type ]) ] );
            } else if(Request::Input('email') == $this->user->email) {
                return Response::json(['success' => false, 'message' => trans('trp.popup.popup-recommend-dentist.recommended-yourself', ['type' => $type ]) ] );
            } else if(!empty($existing_patient) && !empty($existing_patient->deleted_at)) {
                return Response::json(['success' => false, 'message' => trans('trp.page.profile.invite.patient-deleted', ['email' => Request::Input('email') ])] );
            } else {
                $new_recommendation = new DentistRecommendation;
                $new_recommendation->user_id = $this->user->id;
                $new_recommendation->dentist_id = Request::Input('dentist-id');
                $new_recommendation->friend_email = Request::Input('email');
                $new_recommendation->friend_name = Request::Input('name');
                $new_recommendation->save();

                $substitutions = [
                    'type' => $type,
                    'invited_user_name' => Request::Input('name'),
                    'inviting_user_name' => $this->user->name,
                    'recommended_dentist' => $recommended_user->getNames(),
                    'recommend_dentist_link' => $recommended_user->getLink(),
                    'image_recommended_dentist' => $recommended_user->getSocialCover(),
                ];

                $existing_anonymous = AnonymousUser::where('email', 'LIKE', Request::Input('email'))->first();
                
                $unsubscribed = User::isUnsubscribedAnonymous(66, 'trp', Request::Input('email'));

                if(empty($existing_patient)) {

                    if(!empty($existing_anonymous)) {

                        if(!$unsubscribed) {
                            $subscribe_cats = $existing_anonymous->website_notifications;

                            if(!isset($subscribe_cats['trp'])) {

                                $subscribe_cats[] = 'trp';
                                $existing_anonymous->website_notifications = $subscribe_cats;
                                $existing_anonymous->save();
                            }
                        }

                    } else {
                        $new_anonymous_user = new AnonymousUser;
                        $new_anonymous_user->email = Request::Input('email');
                        $new_anonymous_user->website_notifications = ['trp'];
                        $new_anonymous_user->save();
                    }
                }

                $mail = GeneralHelper::unregisteredSendGridTemplate($this->user, Request::Input('email'), Request::Input('name'), 89, $substitutions, 'trp', $unsubscribed, Request::Input('email'));
                $mail->delete();

                return Response::json(['success' => true, 'message' => trans('trp.popup.popup-recommend-dentist.success') ] );
            }            
        }
    }

    /**
     * dentist's facebook tab view
     */
    public function dentist_fb_tab($locale=null) {

        return $this->showView('facebook-tab', [
            'xframe' => true,
        ]);
    }

    /**
     * dentist's facebook tab view params
     */
    public function dentist_fb_tab_reviews($locale=null) {

        if (!empty(Request::input('pageid'))) {
            $dentist_page = DentistFbPage::where('fb_page', 'LIKE', Request::input('pageid'))->first();
            $dentist = User::find($dentist_page->dentist_id);

            if (!empty($dentist)) {

                if ($dentist_page->reviews_type == 'all') {
                    $reviews_obj = $dentist->reviews_in();

                    if ($dentist_page->reviews_count != 'all') {
                        $all_count = intval($dentist_page->reviews_count);
                        $reviews_obj = $dentist->reviews_in()->take($all_count);
                    }
                } else if ($dentist_page->reviews_type == 'trusted') {

                    if ($dentist_page->reviews_count != 'all') {
                        $trusted_count = intval($dentist_page->reviews_count);
                        $reviews_obj = $dentist->reviews_in()->where('verified', 1)->take($trusted_count);
                    } else {
                        $reviews_obj = $dentist->reviews_in()->where('verified', 1);
                    }
                    
                } else if($dentist_page->reviews_type == 'custom') {
                    $reviews_obj = [];
                    $d_id = $dentist->id;
                    foreach (json_decode($dentist_page->reviews_count, true) as $k => $cr) {
                        $reviews_obj[] = Review::where('id', $cr)->where(function($query) use ($d_id) {
                            $query->where( 'dentist_id', $d_id)->orWhere('clinic_id', $d_id);
                        })->first();
                    }
                }

                $reviews = [];

                foreach ($reviews_obj as $review) {
                    $review->patient_avatar = $review->user->getImageUrl(true);
                    $review->date_converted = $review->created_at ? date('d/m/Y', $review->created_at->timestamp) : '-';
                    $review->rating_converted = !empty($review->team_doctor_rating) && ($dentist->id == $review->dentist_id) ? $review->team_doctor_rating/5*100 : $review->rating/5*100;
                    $review->converted_answer = nl2br($review->answer);
                    $review->converted_title = !empty($review->title) ? '<a href="'.$dentist->getLink().'?review_id='.$review->id.'" target="_blank" class="review-title">“'.$review->title.'”</a>' : '';
                    $review->patient_name = !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name;
                    $reviews[] = $review->toArray();
                }

                $ret = [
                    'success' => true,
                    'reviews' => $reviews,
                    'reviews_count' => count($dentist->reviews_in()),
                    'dentist_link' => $dentist->getLink(),
                    'avg_rating' => $dentist->avg_rating,
                    'avg_rating_percantage' => $dentist->avg_rating/5*100,
                ];

                return Response::json( $ret );
            }
        }

        return Response::json( [
            'success' => false,
        ] );
    }

    /**
     * dentist adds a facebook tab
     */
    public function fb_tab($locale=null) {

        $validator = Validator::make(Request::all(), [
            'reviews_type' => array('required'),
            'page' => array('required'),
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'message' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['message'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $exists_p = DentistFbPage::where('dentist_id', $this->user->id)->where('fb_page', 'LIKE', request('page'))->first();

            if (empty($exists_p)) {            
                $dp = new DentistFbPage;
            } else {
                $dp = $exists_p;
            }

            $dp->dentist_id = $this->user->id;
            $dp->fb_page = request('page');
            $dp->reviews_type = request('reviews_type');

            if (request('reviews_type') == 'all') {
                $dp->reviews_count = request('all_reviews');
            } else if(request('reviews_type') == 'trusted') {
                $dp->reviews_count = request('trusted_reviews');
            } else {
                $dp->reviews_count = json_encode(request('custom_reviews'));
            }

            $dp->save();

            $mtext = 'Dentist <a href="'.$this->user->getLink().'">'.$this->user->getNames().'</a> add a FB Tab';

            Mail::send([], [], function ($message) use ($mtext) {
                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( 'petya.ivanova@dentacoin.com' );
                $message->subject('Dentist add a FB Tab');
                $message->setBody($mtext, 'text/html'); // for HTML rich messages
            });

            return Response::json([
                'success' => true,
                'link' => 'https://www.facebook.com/dialog/pagetab?app_id=1906201509652855&redirect_uri='.$this->user->getLink().'?popup=facebook-tab-success',
            ] );
        }
    }

    /**
     * clinic's team reorder
     */
    public function reorderTeams() {

        $list = Request::input('list');
        $i=1;
        foreach ($list as $qid) {
            $user_team = UserTeam::find($qid);
            if( $user_team ) {
                $user_team->team_order = $i;
                $user_team->save();
                $i++;
            }
        }

        return Response::json( ['success' => true] );
    }

    /**
     * dentist verifies review
     */
    public function verifyReview() {
        if(!empty($this->user) && $this->user->is_dentist && !empty(request('review_id'))) {
            $review = Review::find(request('review_id'));

            if(!empty($review) && ($review->dentist_id == $this->user->id || $review->clinic->id == $this->user->id)) {
                $review->verified = true;
                $review->save();

                $main_dentist_id = $review->review_to_id ? $review->review_to_id : ($review->dentist_id ? $review->dentist_id : $review->clinic_id);

                $last_ask = UserAsk::where('dentist_id', $main_dentist_id)->where('user_id', $review->user_id)->first();

                if(empty($last_ask)) {
                    $user_ask = new UserAsk;
                    $user_ask->user_id = $review->user_id;
                    $user_ask->dentist_id = $main_dentist_id;
                    $user_ask->status = 'yes';
                    $user_ask->on_review = true;
                    $user_ask->review_id = $review->id;
                    $user_ask->hidden = true;
                    $user_ask->save();
                }

                $last_invite = UserInvite::where('user_id', $main_dentist_id)->where('invited_id', $review->user_id)->first();
                if (!empty($last_invite)) {
                    $last_invite->created_at = Carbon::now();
                    $last_invite->rewarded = true;
                    $last_invite->save();
                } else {
                    $inv = new UserInvite;
                    $inv->user_id = $main_dentist_id;
                    $inv->invited_email = $review->user->email;
                    $inv->invited_name = $review->user->name;
                    $inv->invited_id = $review->user_id;
                    $inv->platform = 'trp';
                    $inv->rewarded = true;
                    $inv->hidden = true;
                    $inv->save();
                }

                //dentist reward
                $reward_dentist = new DcnReward();
                $reward_dentist->user_id = $main_dentist_id;
                $reward_dentist->platform = 'trp';
                $reward_dentist->reward = Reward::getReward('reward_dentist');
                $reward_dentist->type = 'dentist-review';
                $reward_dentist->reference_id = $review->id;

                $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                $dd = new DeviceDetector($userAgent);
                $dd->parse();

                if ($dd->isBot()) {
                    // handle bots,spiders,crawlers,...
                    $reward_dentist->device = $dd->getBot();
                } else {
                    $reward_dentist->device = $dd->getDeviceName();
                    $reward_dentist->brand = $dd->getBrandName();
                    $reward_dentist->model = $dd->getModel();
                    $reward_dentist->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                }

                $reward_dentist->save();

                //patient reward
                $reward = new DcnReward();
                $reward->user_id = $review->user_id;
                $reward->platform = 'trp';
                $reward->reward = Reward::getReward('review_trusted');
                $reward->type = 'review_trusted';
                $reward->reference_id = $review->id;

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

                return Response::json([
                    'success' => true,
                ] );
            }
        }

        return Response::json([
            'success' => false,
            'review_id' => request('review-id'),
        ] );
    }

	/**
     * bottom content of the dentist page
     */
	public function dentist_down($locale=null) {

        $slug = request('slug');

        if (!empty($this->admin)) {
            $item = User::where('slug', 'LIKE', $slug)->first();
        } else {
            $item = User::where('slug', 'LIKE', $slug)->whereNull('self_deleted')->first();
        }

        if(empty($item)) {
            $old_slug = OldSlug::where('slug', 'LIKE', $slug)->first();

            if (!empty($old_slug)) {
                $item = User::find($old_slug->user_id);
            }
        }

        if(empty($item) || !$item->is_dentist) {
            return '';
        }

		$params = array(
            'item' => $item,
            'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
            'my_downvotes' => !empty($this->user) ? $this->user->unusefulVotesForDenist($item->id) : null,
        );

		return $this->ShowView('user-down', $params);	
	}
} ?>