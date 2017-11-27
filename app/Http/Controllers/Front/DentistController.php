<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use Validator;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use App\Models\City;
use App\Models\Country;
use App\Models\Question;
use App\Models\Secret;
use App\Models\Review;
use App\Models\ReviewUpvote;
use App\Models\ReviewAnswer;
use App\Models\Dcn;
use Carbon\Carbon;
use Auth;
use Cloutier\PhpIpfsApi\IPFS;


class DentistController extends FrontController
{
    public function test($locale=null) {
        $ipfs = new IPFS("127.0.0.1", "8080", "5001");
        $hash = $ipfs->add(json_encode([
            'bla' => 123,
            'foo' => 'bar',
            'obj' => [
                'a' => 1,
                'b' => 2,
            ]
        ]));
        dd($hash);
    }

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

            $old_review->dentist->recalculateRating();
            
            Request::session()->flash('success-message', trans('front.page.dentist.review-submitted'));

            return Response::json( [
                'success' => true,
            ] );
        }
        
        return Response::json( [
            'success' => false,
        ] );
    }


    public function list($locale=null, $slug, $review_id=null) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }

        //$item->recalculateRating();

        $questions = Question::get();

        if(Request::isMethod('post')) {

            $ret = array(
                'success' => false
            );
            $validator_arr = [
                'answer' => ['required', 'string']
            ];
            foreach ($questions as $question) {
                $opts = json_decode($question['options'], true);
                foreach ($opts as $i => $nosense) {
                }
                $validator_arr['option.'.$question->id.'.'.$i] = ['required', 'numeric', 'min:1', 'max:5'];
            }
            $my_dcn_address = $this->user->my_address();
            if(!$my_dcn_address) {
                $validator_arr['reviewer-address'] = ['required', 'string', 'size:42'];
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
                $ret['valid_input'] = true;

                if( (($this->user->phone_verified && $this->user->is_verified) || $this->user->fb_id) && !$this->user->is_dentist) {

                    $old_review = $this->user->hasReviewTo($item->id);
                    if($old_review && $old_review->status=='accepted') {
                        ; //dgd
                    } else if( $this->user->getReviewLimits() ) {
                        ; //dgd
                    } else {

                        $secret = Secret::getNext();

                        if($old_review && $old_review->status=='pending') {
                            $review = $old_review;
                        } else {
                            $review = new Review;
                            $review->user_id = $this->user->id;
                            $review->dentist_id = $item->id;
                            
                        }

                        $review->rating = 0;
                        $review->answer = strip_tags(Request::input( 'answer' ));
                        $review->verified = $this->user->invited_by == $item->id;
                        $review->status = 'pending';
                        $review->secret_id = $secret->id;
                        $review->save();

                        $total = 0;
                        $answer_rates = [];
                        $crypto_data = [];
                        $crypto_data['answer'] = strip_tags(Request::input( 'answer' ));
                        foreach ($questions as $question) {
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
                            
                            if($old_review) {
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
                        $amount = $review->verified ? 25000 : 5000;
                        $ra = $my_dcn_address ? $my_dcn_address : Request::input('reviewer-address');
                        

                        $ret = Dcn::send($this->user, $ra, $amount);
                        $ret['valid_input'] = true; //was overridden
                        if($ret['success']) {

                            $review->reward_tx = $ret['message'];
                            $review->reward_address = $ra;
                            $review->status = 'accepted';
                            $review->secret->used = true;
                            $review->secret->save();
                            $review->save();
                                        
                            $item->sendTemplate(6, [
                                'review_id' => $review->id,
                            ]);

                            $review->dentist->recalculateRating();
                        }

                    }
                }
            }

            return Response::json( $ret );
        }



        $reviews = $item->reviews_in;
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

        $my_dcn_address = !empty($this->user) ? $this->user->my_address() : null;

        return $this->ShowView('dentist', [
            'item' => $item,
            'my_review' => !empty($this->user) ? $this->user->hasReviewTo($item->id) : null,
            'my_upvotes' => !empty($this->user) ? $this->user->usefulVotesForDenist($item->id) : null,
            'questions' => $questions,
            'reviews' => $reviews,
            'my_dcn_address' => $my_dcn_address,
            'review_limit_reached' => !empty($this->user) ? $this->user->getReviewLimits() : null,
            'aggregated_rates' => $aggregated_rates,
            'aggregated_rates_total' => $aggregated_rates_total ,
            'seo_title' => trans('front.seo.dentist.title', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city ? $item->city->name : '',
            ]),
            'social_title' => trans('front.social.dentist.title', [
                'name' => $item->getName(),
                'country' => $item->country ? $item->country->name : '',
                'city' => $item->city ? $item->city->name : '',
            ]),
            'canonical' => $item->getLink().($review_id ? '/'.$review_id : ''),
            'js' => [
                'dentist.js',
                'dApp.js'
            ],
        ]);

    }

    public function claim($locale=null, $slug) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }

        $phone = ltrim( str_replace(' ', '', Request::input( 'phone' )), '0');

        if($phone==$item->phone) {
            $vc = rand(100000, 999999);
            $item->verification_code = $vc;
            $item->save();

            $sms_text = trans('front.common.sms-claim', ['code' => $vc]);

            $item->sendSMS($sms_text);

            return Response::json( ['success' => true] );
        }


        return Response::json( ['success' => false] );
    }


    public function code($locale=null, $slug) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }

        $code = trim( Request::input( 'code' ) );

        if($item->verification_code && $item->phone && $code==$item->verification_code) {
            $item->phone_verified = true;
            $item->phone_verified_on = Carbon::now();
            $item->save();

            return Response::json( ['success' => true] );
        }


        return Response::json( ['success' => false] );
    }


    public function email($locale=null, $slug) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }


        if(trim( Request::input( 'email' ) ) == $item->email) {
            $item->sendTemplate( 9 );

            return Response::json( ['success' => true] );
        }


        return Response::json( ['success' => false] ); //, 'bla' => Request::input( 'email' ).' -- '.$item->email

    }

    public function password($locale=null, $slug) {
        $item = User::where('slug', 'LIKE', $slug)->firstOrFail();

        if(empty($item)) {
            return redirect( getLangUrl('dentists') );
        }

        $validator = Validator::make(Request::all(), [
            'password' => array('required', 'min:6'),
            'password-repeat' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return Response::json( ['success' => false] );
        } else {
            $item->password = bcrypt(Request::input('password'));
            $item->is_verified = true;
            $item->verified_on = Carbon::now();
            $item->save();

            Auth::login($item, true);

            Request::session()->flash('success-message', trans('front.page.claim.success'));

            return Response::json( [
                'success' => true,
                'url' => getLangUrl('profile'),
            ] );
        }


    }

    public function useful($locale=null, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review)) {
            $myvotes = $this->user->usefulVotesForDenist($review->dentist_id);
            if(!in_array($review_id, $myvotes)) {
                if($this->user->phone_verified || $this->user->fb_id) {
                    $review->upvotes++;
                    $review->save();
                    $uv = new ReviewUpvote;
                    $uv->review_id = $review_id;
                    $uv->user_id = $this->user->id;
                    $uv->save();
                }
            }
        }

        return Response::json( ['success' => true] );
    }

    public function reply($locale=null, $slug, $review_id) {
        $review = Review::find($review_id);
        if(!empty($review) && $this->user->id==$review->dentist_id) {
            $review->reply = strip_tags(Request::input( 'reply' ));
            $review->save();
            $review->user->sendTemplate(8, [
                'review_id' => $review->id,
            ]);
        }

        return Response::json( ['success' => true, 'reply' => nl2br( $review->reply )] );
    }

}