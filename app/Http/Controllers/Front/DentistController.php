<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use Mail;
use Response;
use Request;
use Validator;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\UserBan;
use App\Models\City;
use App\Models\Country;
use App\Models\Question;
use App\Models\Secret;
use App\Models\Review;
use App\Models\ReviewUpvote;
use App\Models\ReviewDownvote;
use App\Models\ReviewAnswer;
use App\Models\UserInvite;
use App\Models\UserAsk;
use App\Models\Dcn;
use App\Models\Reward;
use App\Models\DcnReward;
use App\Models\UserStrength;
use App\Models\DentistPageview;
use Carbon\Carbon;
use Auth;
use Cloutier\PhpIpfsApi\IPFS;


class DentistController extends FrontController
{
    public function confirmReview($locale=null, $slug, $secret) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }

        $old_review = $this->user->hasReviewTo($item->id);
        //dd($old_review);
        if($old_review && $old_review->status=='pending' && $old_review->secret->secret==$secret) {
            $old_review->status = 'accepted';
            $old_review->secret->used = true;
            $old_review->secret->save();
            $old_review->save();

                        
            $item->sendTemplate(6, [
                'review_id' => $old_review->id,
            ]);

            if( $old_review->dentist_id ) {
                $old_review->dentist->recalculateRating();                
            }
            if( $old_review->clinic_id ) {
                $old_review->clinic->recalculateRating();                
            }
            
            Request::session()->flash('success-message', trans('trp.page.dentist.review-submitted'));

            return Response::json( [
                'success' => true,
            ] );
        }
        
        return Response::json( [
            'success' => false,
        ] );
    }

    public function fullReview($locale=null, $id) {
        $review = Review::find($id);

        if(empty($review)) {
            return '';
        } else {
            $item = $review->dentist_id ? User::find($review->dentist_id) : User::find($review->clinic_id);

            return $this->ShowView('popups.detailed-review-content', [
                'item' => $item,
                'review' => $review,
                'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
                'my_downvotes' => !empty($this->user) ? $this->user->unusefulVotesForDenist($item->id) : null,            
            ]);
        }
    }

    public function youtube($locale=null) {

        $fn = microtime(true).'-'.$this->user->id;
        $fileName   = storage_path(). '/app/public/'.$fn.'.webm';
        //echo 'https://reviews.dentacoin.com/storage/qqfile.webm';
        //dd($fileName);

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
                   'snippet.description' => $this->user->getName().'\'s video review on ',
                   'snippet.tags[]' => '',
                   'snippet.title' => 'Dentist review by '.$this->user->getName(),
                   'status.embeddable' => '',
                   'status.license' => '',
                   'status.privacyStatus' => 'unlisted',
                   'status.publicStatsViewable' => ''),
            'snippet,status', array());



        return Response::json( [
            'url' => $url
        ] );
    }



    public function list($locale=null, $slug) {

        if(!empty($this->user) && $this->user->isBanned('trp')) {
            return redirect('https://account.dentacoin.com/trusted-reviews?platform=trusted-reviews');
        }

        if ($slug == 'dr-vadivelan-jeyachandran') {
            return redirect( getLangUrl('dentist/vadivelan-jeyachandran'), 301 );
        }

        // $cmt = $list = User::where('is_dentist', 1)->whereNotNull('zip')->whereNull('state_name')->count();
        // echo 'TOTAL: '.$cmt.'<br/>';
        // $list = User::where('is_dentist', 1)->whereNotNull('zip')->whereNull('state_name')->take(100)->get();
        // foreach ($list as $user) {
        //     echo $user->country->name.', '.$user->address.'<br/>';
        //     $user->address = $user->address.'';
        // }
        // exit;

        $review_id = request('review_id');
        $item = User::where('slug', 'LIKE', $slug)->first();

        if(empty($item) || !$item->is_dentist) {
            return redirect( getLangUrl('page-not-found') );
        }

        session([
            'intended-sess' => $item->getLink(),
        ]);

        //$item->recalculateRating();
        $isTrusted = !empty($this->user) ? $this->user->wasInvitedBy($item->id) : false;

        $canAskDentist = !empty($this->user) ? $this->user->canAskDentist($item->id) : true;

        $questions = Question::with('translations')->get();


        if(Request::isMethod('post')) {

            $ret = array(
                'success' => false
            );
            $validator_arr = [
                'answer' => ['required_without:youtube_id'],
                'youtube_id' => ['required_without:answer'],
                'treatments' => ['required']
            ];
            foreach ($questions as $question) {
                if($question->id == 4 && $item->is_clinic && empty( Request::input( 'clinic_dentists' ) )  ) {
                    continue;
                }

                    
                $opts = json_decode($question['options'], true);

                foreach ($opts as $i => $nosense) {
                }

                $validator_arr['option.'.$question->id.'.'.$i] = ['required', 'numeric', 'min:1', 'max:5'];
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
                    // if($old_review && $old_review->status=='accepted') {
                    //     ; //dgd
                    // }
                    if( $this->user->loggedFromBadIp() ) {
                        ; //dgd
                    // } else if( $this->user->getReviewLimits() ) {
                    //     ; //dgd
                    } else if( $this->user->cantSubmitReviewToSameDentist($item->id) ) {
                        ; //dgd
                    } else {

                        $secret = Secret::getNext();

                        if($old_review && $old_review->status=='pending') {
                            $review = $old_review;
                        } else {
                            $review = new Review;
                            $review->user_id = $this->user->id;
                            if($item->is_clinic) {
                                $review->clinic_id = $item->id;
                                if(!empty(Request::input( 'clinic_dentists' ))) {
                                    $review->dentist_id = Request::input( 'clinic_dentists' );
                                }
                            } else {
                                $review->dentist_id = $item->id;
                                if(!empty(Request::input( 'dentist_clinics' ))) {
                                    $review->clinic_id = Request::input( 'dentist_clinics' );
                                }
                            }
                            
                        }

                        $review->rating = 0;
                        $review->title = strip_tags(Request::input( 'title' ));
                        $review->answer = strip_tags(Request::input( 'answer' ));
                        $review->youtube_id = strip_tags(Request::input( 'youtube_id' ));
                        $review->verified = !empty($isTrusted);
                        $review->status = 'pending';
                        $review->secret_id = $secret->id;
                        $review->treatments = Request::input( 'treatments' );
                        $review->save();

                        $total = 0;
                        $answer_rates = [];
                        $crypto_data = [];
                        $crypto_data['answer'] = strip_tags(Request::input( 'answer' ));
                        foreach ($questions as $question) {
                            
                            if($question->id == 4 && $item->is_clinic && empty( Request::input( 'clinic_dentists' ) )  ) {
                                continue;
                            }

                            $crypto_data['question-'.$question->id] = [];
                            $answer_rates[$question->id] = 0;
                            $option_answers = [];
                            $options = json_decode($question['options'], true);
                            foreach ($options as $i => $nosense) {
                                $r = Request::input( 'option.'.$question->id.'.'.$i );;
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
                            $crypto_data['question-'.$question->id] = $option_answers;
                            $answer->save();
                        }

                        $review->rating = array_sum($answer_rates) / count($answer_rates);
                        $review->save();

                        $ipfs = new IPFS("127.0.0.1", "8080", "5001");
                        $review->ipfs = $ipfs->add(json_encode($crypto_data));
                        $review->save();

                        
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
                                $reward->os = $dd->getOs()['name'];
                            }
                            $reward->save();                            
                        }

                        $review->status = 'accepted';
                        $review->secret->used = true;
                        $review->secret->save();
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
                            $ret['success'] = false;
                            $ret['ban'] = true;
                        }

                        $ret['success'] = true;

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

            foreach ($reviews as $rev) {
                foreach($rev->answers as $answer) {
                    //echo $rev->answers->count().'  '.$answer->question['label'].' '.array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true)).'<br>';
                    if(!isset($aggregated[$answer->question['label']])) {
                        $aggregated[$answer->question['label']] = 0;
                    }

                    $aggregated[$answer->question['label']] += array_sum(json_decode($answer->options, true)) / count(json_decode($answer->options, true));
                }

                if ($rev->answers->count() == 9 && array_key_exists("Doctor",$aggregated) ) {
                    $aggregated['Doctor'] += 5;
                }
            }

            foreach ($aggregated as $key => $value) {
                $aggregated[$key] /= $reviews->count();
            }
        }


        $aggregated_rates = [];
        $aggregated_rates_total = [];
        $count = $reviews->count();
        if($count) {
            foreach ($reviews as $review) {
                foreach($review->answers as $answer) {
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
            $current_review->generateSocialCover();
            $social_image = $current_review->getSocialCover();
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

        $view_params = [
            'strength_arr' => $strength_arr,
            'completed_strength' => $completed_strength,
            'noIndex' => $item->address ? false : true,
            'item' => $item,
            'is_trusted' => $isTrusted,
            'canAskDentist' => $canAskDentist,
            'my_review' => !empty($this->user) ? $this->user->cantSubmitReviewToSameDentist($item->id) : null,
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
            'canonical' => $item->getLink().($review_id ? '?review_id='.$review_id : ''),
            'countries' => Country::with('translations')->get(),
            'js' => [
                'videojs.record.min.js',
                'user.js',
                'search.js',
            ],
            'jscdn' => [
                'https://www.amcharts.com/lib/4/core.js',
                'https://www.amcharts.com/lib/4/themes/animated.js',
                'https://www.amcharts.com/lib/4/charts.js',
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.32.0/codemirror.min.js',
                'https://codemirror.net/addon/display/placeholder.js',
            ],
            'css' => [
                'codemirror.css',
            ],
        ];

        if( $is_review ) {
            $view_params['seo_title'] = trans('trp.seo.review.title', [
                'dentist_name' => $item->getName(),
                'user_name' => $current_review->user->getName(),
            ]);
            $view_params['social_title'] = trans('trp.social.review.title', [
                'dentist_name' => $item->getName(),
                'user_name' => $current_review->user->getName(),
            ]);
            $view_params['seo_description'] = trans('trp.seo.review.description', [
                'review_title' => $current_review->title,
                'review_text' => $current_review->answer,
            ]);
            $view_params['social_description'] = trans('trp.social.review.description', [
                'review_title' => $current_review->title,
                'review_text' => $current_review->answer,
            ]);

        } else {
            $view_params['seo_title'] = trans('trp.seo.dentist.title', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city_name ? $item->city_name : '',
                'reviews_number' => intval($item->ratings),
            ]);
            $view_params['social_title'] = trans('trp.social.dentist.title', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city_name ? $item->city_name : '',
                'reviews_number' => intval($item->ratings),
            ]);

            $view_params['seo_description'] = trans('trp.seo.dentist.description', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city_name ? $item->city_name : '',
                'reviews_number' => intval($item->ratings),
            ]);
            $view_params['social_description'] = trans('trp.social.dentist.description', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city_name ? $item->city_name : '',
                'reviews_number' => intval($item->ratings),
            ]);
        }
        
        

        $addGM = false;
        if($item->lat && $item->lon) {
            $addGM = true;
        }

        if(!empty($this->user) && $this->user->id==$item->id) {
            $view_params['js'][] = 'upload.js';
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


        if(!empty($this->user) && !$this->user->civic_id) {
            $view_params['js'][] = 'civic.js';
            $view_params['jscdn'][] = 'https://hosted-sip.civic.com/js/civic.sip.min.js';
            $view_params['csscdn'] = [
                'https://hosted-sip.civic.com/css/civic-modal.min.css',
            ];

        }

        if( $addGM ) {
            $view_params['jscdn'][] = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en';
        }


        $view_params['schema'] = [
            "@context" => "http://schema.org",
            "@type" => 'Dentist',
            "name" => $item->getName(),
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

        if(!empty($item->short_description)){
            $view_params['schema']["description"] = $item->short_description;
        }

        if (!empty($item->website)) {
            $view_params['schema']["url"] = $item->website;
        }

        if (!empty($item->socials)) {
            $view_params['schema']["sameAs"] = array_values($item->socials);
        }

        $view_params['schema']["aggregateRating"] = [
            "@type" => "AggregateRating",
            "ratingCount" => intval($item->ratings),
            "ratingValue" => $item->avg_rating,
        ];

        if($item->reviews_in_standard()->isNotEmpty() ) {
            $item_reviews = [];
            foreach($item->reviews_in_standard() as $review) {
                $item_reviews[] = [
                    "@type" => "Review",
                    "author" => [
                        "@type" => "Person",
                        "name" => $review->user->name,
                    ],
                    "datePublished" => $review->created_at->format('Y-m-d'),
                    "reviewBody" => $review->answer,
                    "reviewRating" => [
                        "@type" => "Rating",
                        "bestRating" => 5,
                        "ratingValue" => $review->rating,
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

        
        return $this->ShowView('user', $view_params);

    }


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
                        $last_ask->on_review = true;
                        $last_ask->save();
                    } else {
                        $ask = new UserAsk;
                        $ask->user_id = $this->user->id;
                        $ask->dentist_id = $item->id;
                        $ask->status = 'waiting';
                        if (!empty($verification)) {
                            $ask->on_review = true;
                        }
                        $ask->save();
                    }

                    if (!empty($is_patient_to_dentist)) {

                        $substitutions = [
                            'patient_name' => $this->user->name,
                            "requests_link" => getLangUrl( '/' , null, 'https://reviews.dentacoin.com').'?'. http_build_query(['popup'=>'popup-login-dentist']),
                        ];

                        $item->sendGridTemplate(71, $substitutions);
                    } else {
                        $item->sendTemplate( !empty($verification) ? 63 : 23 ,[
                            'patient_name' => $this->user->name,
                            'invitation_link' => $item->getLink()
                        ] );
                    }


                    return Response::json( ['success' => true] );
                }
            }

        }

        return Response::json( ['success' => false] );
    }

    public function useful($locale=null, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review)) {
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

    public function reply($locale=null, $slug, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review) && ($this->user->id==$review->clinic_id || $this->user->id==$review->dentist_id ) ) {
            $review->reply = strip_tags(Request::input( 'reply' ));
            $review->save();
            $review->user->sendTemplate(8, [
                'review_id' => $review->id,
            ]);
        }

        return Response::json( ['success' => true, 'reply' => nl2br( $review->reply )] );
    }


    //
    //Youtube boilerplate
    //



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



    function getClient() {
        $client = new \Google_Client();
        $client->setApplicationName('API Samples');
        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
        // Set to name/location of your client_secrets.json file.
        $client->setAuthConfig( storage_path() . '/client_secrets.json');
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = storage_path() . 'yt-oauth2.json';
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';


            if (isset($_GET['code'])) {


                $credentialsPath = storage_path() . 'yt-oauth2.json';
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

    // Add a property to the resource.
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

    // Build a resource based on a list of properties given as key-value pairs.
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


    public function patientSuspicious($p_id) {
        $patient = User::find($p_id);

        if (!empty($patient)) {

            $current_month_reviews = Review::where('user_id', $patient->id )->where('created_at', '>', Carbon::now()->subDays(30))->get();
            
            if(!empty($current_month_reviews)) {

                if (count($current_month_reviews) == 3) {
                    $mtext = 'Patient - '.$patient->name.' writes his third review to different dentist. 
Link to patients\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$patient->id;

                    Mail::raw($mtext, function ($message) use ($patient) {
                        $receiver = 'petya.ivanova@dentacoin.com';
                        $receiver = 'donika.kraeva@dentacoin.com';
                        $receiver = 'gergana@youpluswe.com';
                        $sender = config('mail.from.address');
                        $sender_name = config('mail.from.name');

                        $message->from($sender, $sender_name);
                        $message->to( $receiver );
                        //$message->to( 'dokinator@gmail.com' );
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

                    $patient->sendGridTemplate(70);

                    return true;
                }
            }
        }
        return false;
    }

    

}