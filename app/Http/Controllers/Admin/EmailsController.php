<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\StopEmailValidation;
use App\Models\EmailValidation;
use App\Models\EmailTemplate;
use App\Models\InvalidEmail;
use App\Models\OldEmail;
use App\Models\Country;
use App\Models\Review;
use App\Models\Email;
use App\Models\User;

use App\Helpers\AdminHelper;

use Validator;
use Request;
use Auth;

class EmailsController extends AdminController {

    public function list( $what=null ) {

        $with_permissions = !empty($what) && in_array($what, Auth::guard('admin')->user()->email_template_type );

        if($with_permissions) {
            if(!in_array($what, Email::$template_types)) {
                return redirect('cms/'.$this->current_page.'/'.current(Email::$template_types));
            }
            $templates = EmailTemplate::with('translations')->whereNull('not_used')->where('type', $what)->orderBy('id', 'ASC')->get();
        } else {

            if( !in_array(Auth::guard('admin')->user()->role, ['super_admin'])) {
                $this->request->session()->flash('error-message', 'You don\'t have permissions' );
                return redirect('cms/home');            
            }
    
            if(
                !empty(request('search-name')) 
                || !empty(request('search-id')) 
                || !empty(request('search-sendgrid-id')) 
                || !empty(request('search-platform')) 
                || !empty(request('without-category'))
                || !empty(request('search-category'))
            ) {
                $templates = EmailTemplate::with('translations')->whereNull('not_used')->orderBy('id', 'ASC');
    
                if(!empty(request('search-name'))) {
                    $s_name = '%'.trim(request('search-name')).'%';
                    $templates = $templates->where( function($query) use ($s_name) {
                        $query->where('name', 'LIKE', $s_name)
                        ->orWhereHas('translations', function ($queryy) use ($s_name) {
                            $queryy->where('title', 'LIKE', $s_name);
                        });
                    });
    
                    // $templates = $templates->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
                    // $templates = $templates->where('title', 'LIKE', '%'.trim(request('search-name')).'%');
                    // $templates = $templates->where('subject', 'LIKE', '%'.trim(request('search-name')).'%');
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
                $templates = EmailTemplate::with('translations')->whereNull('not_used')->where('type', $what)->orderBy('id', 'ASC')->get();
            }
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

            if(in_array(Auth::guard('admin')->user()->role, ['super_admin']) || in_array($template->type, Auth::guard('admin')->user()->email_template_type)) {
                return $this->showView('emails-edit', array(
                    'item' => $template,
                    'langs' => config('langs')['admin'],
                ));
            } else {
                $this->request->session()->flash('error-message', 'You don\'t have permissions' );
                return redirect('cms/home');       
            }
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function save( $id ) {

        $template = EmailTemplate::find($id);

        if($template) {
            if(in_array(Auth::guard('admin')->user()->role, ['super_admin']) || in_array($template->type, Auth::guard('admin')->user()->email_template_type)) {

                $langs = config('langs')['admin'];
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
                $this->request->session()->flash('error-message', 'You don\'t have permissions' );
                return redirect('cms/home');       
            }
        } else {
            return redirect('cms/home');
        }
    }

    public function add() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) && empty(Auth::guard('admin')->user()->email_template_type)) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');
        }

        $validator = Validator::make($this->request->all(), [
            'name' => array('required'),
            'content_en' => array('required'),
        ]);

        if ($validator->fails()) {
            return redirect('cms/emails/support')
            ->withInput()
            ->withErrors($validator);
        } else {

            $template = new EmailTemplate;
            $template->name = $this->request->input('name');
            $template->note = $this->request->input('note');
            $template->type = $this->request->input('type');
            $template->save();

            $langs = config('langs')['admin'];

            foreach ($langs as $langkey => $lang) {
                $translation = $template->translateOrNew($langkey);
                $translation->email_template_id = $template->id;
                $translation->title = $this->request->input('title_'.$langkey);
                $translation->subject = $this->request->input('subject_'.$langkey);
                $translation->subtitle = $this->request->input('subtitle_'.$langkey);
                $translation->content = $this->request->input('content_'.$langkey);
                $translation->category = $this->request->input('category_'.$langkey);
                $translation->save();
            }
        }

        $this->request->session()->flash('success-message', 'Saved');
        return redirect('cms/emails/support');
    }


    public function engagement_email() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
                AND `status` IN ('approved','added_by_clinic_claimed','added_by_dentist_claimed')
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        ///with reviews
        $user = User::find(68690);
        $userReviewsIn = $user->reviews_in();
        $userReviewsInCount = $userReviewsIn->count();
    
        $avg_rating = 0;
        foreach($userReviewsIn as $cur_month_reviews) {

            if (!empty($cur_month_reviews->team_doctor_rating) && ($user->id == $cur_month_reviews->dentist_id)) {
                $avg_rating += $cur_month_reviews->team_doctor_rating;
            } else {
                $avg_rating += $cur_month_reviews->rating;
            }
        }

        $cur_month_rating = number_format($avg_rating / $userReviewsInCount, 2);
        $cur_month_reviews_num = $userReviewsInCount;

        $prev_avg_rating = 0;
        foreach($userReviewsIn as $prev_month_reviews) {
            if (!empty($prev_month_reviews->team_doctor_rating) && ($user->id == $prev_month_reviews->dentist_id)) {
                $prev_avg_rating += $prev_month_reviews->team_doctor_rating;
            } else {
                $prev_avg_rating += $prev_month_reviews->rating;
            }
        }

        $prev_month_rating = !empty($prev_avg_rating) ? $prev_avg_rating / $userReviewsInCount : 0;
        $prev_month_reviews_num = $userReviewsInCount;

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
        $country_id = $user->country_id;
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
            $country_id = $user_no_reviews->country_id;
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $validations = $validations->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = StopEmailValidation::find(1);
        $item->stopped = true;
        $item->save();

        $this->request->session()->flash('success-message', 'Validations are inactive!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations/email_validations');
    }

    public function start_validations() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = StopEmailValidation::find(1);
        $item->stopped = false;
        $item->save();

        $this->request->session()->flash('success-message', 'Validations are active!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations/email_validations');
    }

    public function mark_valid($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = EmailValidation::find($id);

        if($item) {
            $item->valid = true;
            $item->save();

            $this->request->session()->flash('success-message', 'Marked as valid!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations/email_validations');
    }


    public function invalid_emails( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $invalids = InvalidEmail::with('user')->orderBy('id', 'asc');

        if(!empty(request('search-email'))) {
            $invalids = $invalids->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
        }
        if(request('search-user-id')) {
            $invalids = $invalids->where('user_id', request('search-user-id') );
        }
        if(request('search-type')) {
            if(request('search-type') == 'not_deleted') {

                $invalids = $invalids->whereHas('user', function($user) {
                    $user->whereNull('deleted_at');
                });
            } else if(request('search-type') == 'deleted') {
                $invalids = $invalids->whereHas('user', function($user) {
                    $user->whereNotNull('deleted_at');
                });
            }
        }

        if(!empty(request('christmas-email'))) {
            $invalids = $invalids->whereHas('user', function($user) {
                $user->whereHas('emails', function($email) {
                    $email->where('template_id', 115);
                });
            });
        }

        $total_count = $invalids->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 50;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $invalids = $invalids->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('invalid-emails', array(
            'invalids' => $invalids,
            'search_email' => request('search-email'),
            'search_user_id' => request('search-user-id'),
            'search_type' => request('search-type'),
            'christmas_email' => request('christmas-email'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function invalid_delete($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = InvalidEmail::find($id);

        if($item) {
            $item->delete();
            $this->request->session()->flash('success-message', 'Deleted!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations/invalid_emails');
    }

    public function invalid_new() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(!empty(request('id')) && !empty(request('new-email'))) {

            $item = InvalidEmail::find(request('id'));

            if($item) {
                $item->new_email = request('new-email');
                $item->save();

                $user = User::withTrashed()->find($item->user_id);
                $user->email = $item->new_email;
                $user->save();

                $this->request->session()->flash('success-message', 'Email changed!' );
            }
        }
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations/invalid_emails');
    }


    public function old_emails( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $olds = OldEmail::with('user')->orderBy('id', 'desc');

        if(!empty(request('search-email'))) {
            $olds = $olds->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
        }
        if(request('search-user-id')) {
            $olds = $olds->where('user_id', request('search-user-id') );
        }

        $total_count = $olds->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 50;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $olds = $olds->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('old-emails', array(
            'olds' => $olds,
            'search_email' => request('search-email'),
            'search_user_id' => request('search-user-id'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
        ));
    }

    public function old_emails_delete($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = OldEmail::find($id);

        if($item) {
            $item->forceDelete();
            $this->request->session()->flash('success-message', 'Deleted!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/email_validations/old_emails');
    }
}