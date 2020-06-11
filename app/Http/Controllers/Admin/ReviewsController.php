<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Review;
use App\Models\DcnReward;
use App\Models\ReviewAnswer;
use App\Models\User;
use App\Models\UserBan;
use Carbon\Carbon;

use Request;
use Route;

class ReviewsController extends AdminController
{
    public function list() {

        $reviews = Review::orderBy('id', 'DESC');

        if(!empty($this->request->input('search-name-dentist'))) {
            $name = $this->request->input('search-name-dentist');
            $reviews = $reviews->whereHas('dentist', function ($query) use ($name) {
                $query->where('name', 'LIKE', $name.'%');
            });
        }
        if(!empty($this->request->input('search-name-user'))) {
            $name = $this->request->input('search-name-user');
            $reviews = $reviews->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'LIKE', $name.'%');
            });
        }

        if(!empty($this->request->input('search-reviews-from'))) {
            $firstday = new Carbon($this->request->input('search-reviews-from'));
            $reviews = $reviews->where('created_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-reviews-to'))) {
            $firstday = new Carbon($this->request->input('search-reviews-to'));
            $reviews = $reviews->where('created_at', '<=', $firstday);
        }

        if(!empty($this->request->input('search-answer'))) {
            $reviews = $reviews->where('answer', 'like', '%'.$this->request->input('search-answer').'%');
        }

        if(!empty($this->request->input('search-deleted'))) {
            $reviews = $reviews->onlyTrashed();
        }

        if( null !== $this->request->input('results-number')) {
            $results = trim($this->request->input('results-number'));
        } else {
            $results = 50;
        }

        if($results == 0) {
            $reviews = $reviews->take(1000)->get();
        } else {
            $reviews = $reviews->take($results)->get();
        }

        return $this->showView('reviews', array(
            'reviews' => $reviews,
            'search_name_user' => $this->request->input('search-name-user'),
            'search_name_dentist' => $this->request->input('search-name-dentist'),
            'results_number' => $this->request->input('results-number'),
            'search_reviews_from' => $this->request->input('search-reviews-from'),
            'search_reviews_to' => $this->request->input('search-reviews-to'),
            'search_answer' => $this->request->input('search-answer'),
        ));
    }


    public function delete( $id ) {
        $item = Review::find($id);
        
        if(!empty($item)) {
            $uid = $item->user_id;
            $patient = User::where('id', $uid)->withTrashed()->first();

            ReviewAnswer::where([
                ['review_id', $item->id],
            ])
            ->delete();

            $dentist = null;
            $clinic = null;

            if($item->dentist_id) {
                $dentist = User::find($item->dentist_id);
            }

            if($item->clinic_id) {
                $clinic = User::find($item->clinic_id);
            }

            
            $reward_for_review = DcnReward::where('user_id', $patient->id)->where('platform', 'trp')->where('type', 'review')->where('reference_id', $item->id)->first();

            if (!empty($reward_for_review)) {
                $reward_for_review->delete();
            }

            Review::destroy( $id );

            if( !empty($dentist) ) {
                $dentist->recalculateRating();
                $substitutions = [
                    'spam_author_name' => $patient->name,
                ];
                
                $dentist->sendGridTemplate(87, $substitutions, 'trp');
            }

            if( !empty($clinic)) {
                $clinic->recalculateRating();
                $substitutions = [
                    'spam_author_name' => $patient->name,
                ];
                
                $clinic->sendGridTemplate(87, $substitutions, 'trp');
            }

            $ban = new UserBan;
            $ban->user_id = $patient->id;
            $ban->domain = 'trp';
            $ban->type = 'spam-review';
            $ban->save();

            $patient->sendGridTemplate(86, null, 'trp');
        }

        $this->request->session()->flash('success-message', 'Review deleted' );
        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function massdelete(  ) {
        if( Request::input('ids') ) {
            foreach (Request::input('ids') as $r_id) {
                $item = Review::find($r_id);
                
                if(!empty($item)) {
                    $uid = $item->user_id;
                    $patient = User::where('id', $uid)->withTrashed()->first();

                    ReviewAnswer::where([
                        ['review_id', $item->id],
                    ])
                    ->delete();

                    $dentist = null;
                    $clinic = null;

                    if($item->dentist_id) {
                        $dentist = User::find($item->dentist_id);
                    }

                    if($item->clinic_id) {
                        $clinic = User::find($item->clinic_id);
                    }

            
                    $reward_for_review = DcnReward::where('user_id', $patient->id)->where('platform', 'trp')->where('type', 'review')->where('reference_id', $item->id)->first();

                    if (!empty($reward_for_review)) {
                        $reward_for_review->delete();
                    }

                    Review::destroy( $r_id );

                    if( !empty($dentist) ) {
                        $dentist->recalculateRating();
                        $substitutions = [
                            'spam_author_name' => $patient->name,
                        ];
                        
                        $dentist->sendGridTemplate(87, $substitutions, 'trp');
                    }
                    if( !empty($clinic) ) {
                        $clinic->recalculateRating();
                        $substitutions = [
                            'spam_author_name' => $patient->name,
                        ];
                        
                        $clinic->sendGridTemplate(87, $substitutions, 'trp');
                    }

                    $ban = new UserBan;
                    $ban->user_id = $patient->id;
                    $ban->domain = 'trp';
                    $ban->type = 'spam-review';
                    $ban->save();

                    $patient->sendGridTemplate(86, null,'trp');
                }
            }
            //Review::whereIn('id', Request::input('ids'))->delete();            
        }

        $this->request->session()->flash('success-message', 'All selected reviews are now deleted' );
        return redirect('cms/trp/'.$this->current_subpage);
    }


    public function restore( $id ) {
        $item = Review::onlyTrashed()->find($id);

        if(!empty($item)) {            
            $item->restore();
            ReviewAnswer::onlyTrashed()->where([
                ['review_id', $item->id],
            ])
            ->restore();

            $dentist = null;
            $clinic = null;

            if($item->dentist_id) {
                $dentist = User::find($item->dentist_id);
            }

            if($item->clinic_id) {
                $clinic = User::find($item->clinic_id);
            }
            
            if( !empty($dentist) ) {
                $dentist->recalculateRating();
            }
            
            if( !empty($clinic) ) {
                $clinic->recalculateRating();
            }
        }

        $this->request->session()->flash('success-message', 'Review restored' );
        return redirect('cms/trp/'.$this->current_subpage);
    }

}
