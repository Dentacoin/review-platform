<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\StopEmailValidation;
use App\Models\EmailValidation;
use App\Models\EmailTemplate;
use App\Models\Country;
use App\Models\Review;
use App\Models\Email;
use App\Models\User;

use Carbon\Carbon;

use Validator;
use Request;

class EmailsController extends AdminController {

    public function list( $what=null ) {
        

        if(!empty(request('search-name')) || !empty(request('search-id')) || !empty(request('search-sendgrid-id')) || !empty(request('search-platform')) || !empty(request('without-category'))) {
            $templates = EmailTemplate::whereNull('not_used')->orderBy('id', 'ASC');

            if(!empty(request('search-name'))) {
                $templates = $templates->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
            }
            if(!empty(request('search-id'))) {
                $templates = $templates->where('id', request('search-id') );
            }
            if(!empty(request('search-sendgrid-id'))) {
                $si = request('search-sendgrid-id');
                $templates = $templates->whereHas('translations', function ($query) use ($si) {
                    $query->where('sendgrid_template_id', 'LIKE', $si);
                });
            }
            if(!empty(request('search-platform'))) {
                $templates = $templates->where('type', request('search-platform') );
            }

            if(!empty(request('without-category'))) {
                $templates = $templates->whereNull('subscribe_category');
            }

            if(!empty(request('search-category'))) {
                $templates = $templates->where('subscribe_category', request('search-category'));
            }

            $templates = $templates->get();
        } else {

            if(!in_array($what, Email::$template_types)) {
                return redirect('cms/'.$this->current_page.'/'.current(Email::$template_types));
            }
            $templates = EmailTemplate::whereNull('not_used')->where('type', $what)->orderBy('id', 'ASC')->get();
        }

    	return $this->showView('emails', array(
            'templates' => $templates,
            'platform' => $what,
            'search_name' => request('search-name'),
            'search_id' => request('search-id'),
            'search_sendgrid_id' => request('search-sendgrid-id'),
            'search_platform' => request('search-platform'),
            'without_category' => request('without-category'),
            'search_category' => request('search-category'),
        ));
    }

    public function edit( $id ) {
        $template = EmailTemplate::find($id);
        if($template) {
            return $this->showView('emails-edit', array(
                'item' => $template,
                'langs' => config('langs'),
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function save( $id ) {
        $template = EmailTemplate::find($id);
        if($template) {
            $langs = config('langs');
            foreach ($langs as $langkey => $lang) {
                $translation = $template->translateOrNew($langkey);
                $translation->email_template_id = $template->id;
                $translation->title = $this->request->input('title_'.$langkey);
                $translation->subject = $this->request->input('subject_'.$langkey);
                $translation->subtitle = $this->request->input('subtitle_'.$langkey);
                $translation->content = $this->request->input('content_'.$langkey);
                $translation->sendgrid_template_id = $this->request->input('sendgrid_template_id_'.$langkey);
                $translation->category = $this->request->input('category_'.$langkey);
                $translation->save();
            }
            $template->subscribe_category = $this->request->input('subscribe_category');
            $template->validate_email = $this->request->input('validate-email');
            $template->note = $this->request->input('note');
            $template->save();

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.saved'));
            return redirect('cms/'.$this->current_page.'/'.$template->type);
        } else {
            
        }
    }


    public function engagement_email() {

        //No reviews last 30 days

        //Email1

        $query = "
            SELECT 
                `id`
            FROM 
                users
            WHERE 
                `is_dentist` = 1
                AND `id` NOT IN ( SELECT `dentist_id` FROM `reviews` WHERE `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00' )
                AND `id` NOT IN ( SELECT `clinic_id` FROM `reviews` WHERE `created_at` > '".date('Y-m-d', time() - 86400*30)." 00:00:00' )
                AND `id` NOT IN ( SELECT `user_id` FROM `emails` WHERE `template_id` = 49 AND `created_at` > '".date('Y-m-d', time() - 86400*93)." 00:00:00' )
                AND `created_at` < '".date('Y-m-d', time() - 86400*30)." 00:00:00'
                AND `unsubscribe` is null
                AND `status` = 'approved'
                AND `deleted_at` is null
        ";

        $users = DB::select(
            DB::raw($query), []
        );

        foreach ($users as $u) {
            $user = User::find($u->id);

            if (!empty($user)) {
                $user->sendGridTemplate(49, null, 'trp');
            }
        }

        $this->request->session()->flash('success-message', 'Emails send');
        return redirect('cms/'.$this->current_page);
    }

    public function monthly_email() {

        ///with reviews
        $user = User::find(68690);
    
        $avg_rating = 0;
        foreach($user->reviews_in() as $cur_month_reviews) {

            if (!empty($cur_month_reviews->team_doctor_rating) && ($user->id == $cur_month_reviews->dentist_id)) {
                $avg_rating += $cur_month_reviews->team_doctor_rating;
            } else {
                $avg_rating += $cur_month_reviews->rating;
            }
        }

        $cur_month_rating = number_format($avg_rating / $user->reviews_in()->count(), 2);
        $cur_month_reviews_num = $user->reviews_in()->count();

        $prev_avg_rating = 0;
        foreach($user->reviews_in() as $prev_month_reviews) {
            if (!empty($prev_month_reviews->team_doctor_rating) && ($user->id == $prev_month_reviews->dentist_id)) {
                $prev_avg_rating += $prev_month_reviews->team_doctor_rating;
            } else {
                $prev_avg_rating += $prev_month_reviews->rating;
            }
        }

        $prev_month_rating = !empty($prev_avg_rating) ? $prev_avg_rating / $user->reviews_in()->count() : 0;
        $prev_month_reviews_num = $user->reviews_in()->count();

        if (!empty($prev_month_rating)) {
            
            if ($cur_month_rating < $prev_month_rating) {
                $cur_month_rating_percent = intval((($cur_month_rating - $prev_month_rating) / $prev_month_rating) * -100).'%';
                $change_month = 'lower than last month';
            } else if($cur_month_rating > $prev_month_rating) {
                $cur_month_rating_percent = intval((($cur_month_rating / $prev_month_rating) - 1) * 100).'%';
                $change_month = 'higher than last month';
            } else {
                $cur_month_rating_percent = '';
                $change_month = 'the same as last month';
            }
        } else {
            $cur_month_rating_percent = '100%';
            $change_month = 'higher than last month';
        }


        if (!empty($prev_month_reviews_num)) {
            if ($cur_month_reviews_num < $prev_month_reviews_num) {
                $reviews_num_percent_month = intval((($cur_month_reviews_num - $prev_month_reviews_num) / $prev_month_reviews_num) * -100).'%';
                $change_month_num = 'lower than last month';

            } else if($cur_month_reviews_num > $prev_month_reviews_num) {
                $reviews_num_percent_month = intval((($cur_month_reviews_num / $prev_month_reviews_num) - 1) * 100).'%';
                $change_month_num = 'higher than last month';
            } else {
                $reviews_num_percent_month = '';
                $change_month_num = 'the same as last month';
            }
        } else {
            $reviews_num_percent_month = '100%';
            $change_month_num = 'higher than last month';
        }


        //status?
        $country_id = $user->country->id;
        $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
            $query->where('country_id', $country_id);
        })->get();

        $country_rating = 0;
        foreach ($country_reviews as $c_review) {
            $country_rating += $c_review->rating;
        }

        $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

        if (!empty($avg_country_rating)) {
            if ($cur_month_rating < $avg_country_rating) {
                $cur_country_month_rating_percent = intval((($cur_month_rating - $avg_country_rating) / $avg_country_rating) * -100).'%';
                $change_country = 'lower than the average';
            } else if($cur_month_rating > $avg_country_rating) {
                $cur_country_month_rating_percent = intval((($cur_month_rating / $avg_country_rating) - 1) * 100).'%';
                $change_country = 'higher than the average';
            } else {
                $cur_country_month_rating_percent = '0%';
                $change_country = 'same as average';
            }
        } else {
            $cur_month_rating_percent = '100%';
            $change_country = 'higher than the average';
        }


        // $top3_dentists_query = User::where('is_dentist', 1)->where('status', 'approved')->where('country_id', $user->country_id)->orderby('avg_rating', 'desc')->take(3)->get();

        // $top3_dentists = [];
        // foreach ($top3_dentists_query as $top3_dentist) {
        //  $top3_dentists[] = '<a href="'.$top3_dentist->getLink().'">'.$top3_dentist->getNames().'</a>';
        // }

        $user->sendGridTemplate(90, [
            'score_last_month_aver' => $cur_month_rating,
            'score_percent_month' => $cur_month_rating_percent,
            'change_month' => $change_month,
            'reviews_last_month_num' => $cur_month_reviews_num.($cur_month_reviews_num > 1 ? ' reviews' : ' review'),
            'score_percent_country' => $cur_country_month_rating_percent,
            'change_country' => $change_country,
            'reviews_num_percent_month' => $reviews_num_percent_month,
            'change_month_num' => $change_month_num,
            // 'top3-dentists' => implode('<br/>',$top3_dentists)
        ], 'trp');



        ///without reviews

        $user_no_reviews = User::find(77812);

        if($user_no_reviews->country_id) {
            $country_id = $user_no_reviews->country->id;
            $country_reviews = Review::whereHas('user', function ($query) use ($country_id) {
                $query->where('country_id', $country_id);
            })->get();

            if ($country_reviews->count()) {
                $country_rating = 0;
                foreach ($country_reviews as $c_review) {
                    $country_rating += $c_review->rating;
                }

                $avg_country_rating = number_format($country_rating / $country_reviews->count(), 2);

                $compare_with_others = 'Other dentists in '.Country::find($user_no_reviews->country_id)->name.' achieved average rating score: '.$avg_country_rating.'. Are you ready to challenge them?';
            } else {
                $compare_with_others = 'Don\'t miss the chance to stand out from other dentists in '.Country::find($user_no_reviews->country_id)->name.' this month! Invite your patients to post a review and boost your monthly performance!';
            }

            $month = \Carbon\Carbon::now();

            $user_no_reviews->sendGridTemplate(91, [
                'month' => $month->subMonth()->format('F'),
                'compare_with_others' => $compare_with_others,
            ], 'trp');
        }

        $this->request->session()->flash('success-message', 'Emails send');
        return redirect('cms/'.$this->current_page);
    }

    public function list_validations( ) {

        $validations = EmailValidation::orderBy('id', 'desc');

        if(!empty(request('search-email'))) {
            $validations = $validations->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
        }
        if(request('search-valid') == 'valid') {
            $validations = $validations->where('valid', 1 );
        } else if(request('search-valid') == 'invalid') {
            $validations = $validations->where('valid', 0 );
        }

        $total_count = $validations->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 50;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
          $start = 1;
          $end   = $total_pages;
        } else {
          if(($page - $adjacents) > 1) { 
            if(($page + $adjacents) < $total_pages) { 
              $start = ($page - $adjacents);            
              $end   = ($page + $adjacents);         
            } else {             
              $start = ($total_pages - (1+($adjacents*2)));  
              $end   = $total_pages;               
            }
          } else {               
            $start = 1;                                
            $end   = (1+($adjacents * 2));             
          }
        }

        $validations = $validations->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('email-validations', array(
            'validations' => $validations,
            'search_email' => request('search-email'),
            'search_valid' => request('search-valid'),
            'stopped_validations' => StopEmailValidation::find(1)->stopped,
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function stop_validations() {

        $item = StopEmailValidation::find(1);
        $item->stopped = true;
        $item->save();

        $this->request->session()->flash('success-message', 'Validations are inactive!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations');
    }

    public function start_validations() {

        $item = StopEmailValidation::find(1);
        $item->stopped = false;
        $item->save();

        $this->request->session()->flash('success-message', 'Validations are active!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations');
    }

    public function mark_valid($id) {

        $item = EmailValidation::find($id);

        if($item) {

            $item->valid = true;
            $item->save();

            $this->request->session()->flash('success-message', 'Marked as valid!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations');
    }

}