<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\ReviewAnswer;
use App\Models\DcnReward;
use App\Models\UserBan;
use App\Models\Review;
use App\Models\User;

use App\Helpers\AdminHelper;
use Carbon\Carbon;

use Request;
use Auth;

class ReviewsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $reviews = Review::orderBy('id', 'DESC');

        if(!empty($this->request->input('id'))) {
            $reviews = $reviews->where('id', $this->request->input('id'));
        }

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

        $total_count = $reviews->count();
        $page = max(1,intval(request('page')));
        $ppp = 50;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $reviews = $reviews->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('reviews', array(
            'reviews' => $reviews,
            'id' => $this->request->input('id'),
            'search_name_user' => $this->request->input('search-name-user'),
            'search_name_dentist' => $this->request->input('search-name-dentist'),
            'search_reviews_from' => $this->request->input('search-reviews-from'),
            'search_reviews_to' => $this->request->input('search-reviews-to'),
            'search_answer' => $this->request->input('search-answer'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Review::find($id);
        
        if(!empty($item)) {
            $this->deleteReview($item);
            $this->request->session()->flash('success-message', 'Review deleted' );
        }

        $this->request->session()->flash('error-message', 'Review not found' );
        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function massdelete() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            foreach (Request::input('ids') as $r_id) {
                $item = Review::find($r_id);
                
                if(!empty($item)) {
                    $this->deleteReview($item);
                }
            }
        }

        $this->request->session()->flash('success-message', 'All selected reviews are now deleted' );
        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function restore( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $item = Review::onlyTrashed()->find($id);

        if(!empty($item)) {            
            $item->restore();

            ReviewAnswer::onlyTrashed()->where([
                ['review_id', $item->id],
            ])->restore();

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

    private function deleteReview($item) {
        $uid = $item->user_id;
        $patient = User::where('id', $uid)->withTrashed()->first();

        ReviewAnswer::where([
            ['review_id', $item->id],
        ])->delete();

        $dentist = null;
        $clinic = null;

        if($item->dentist_id) {
            $dentist = User::find($item->dentist_id);
        }

        if($item->clinic_id) {
            $clinic = User::find($item->clinic_id);
        }

        $reward_for_review = DcnReward::where('user_id', $patient->id)
        ->where('platform', 'trp')
        ->where('type', 'review')
        ->where('reference_id', $item->id)
        ->first();

        if (!empty($reward_for_review)) {
            $reward_for_review->delete();
        }

        Review::destroy( $item->id );

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

        $notifications = $patient->website_notifications;
        if(!empty($notifications)) {
            if(($key = array_search('trp', $notifications)) !== false) {
                unset($notifications[$key]);
            }

            $patient->website_notifications = $notifications;
            $patient->save();
        }

        $request_body = new \stdClass();
        $request_body->recipient_emails = [$patient->email];
        $trp_group_id = config('email-preferences')['product_news']['trp']['sendgrid_group_id'];

        $sg = new \SendGrid(env('SENDGRID_PASSWORD'));
        $sg->client->asm()->groups()->_($trp_group_id)->suppressions()->post($request_body);

        $patient->sendGridTemplate(86, null,'trp');
    }
}