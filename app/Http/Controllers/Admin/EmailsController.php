<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;

use App\Models\EmailTemplate;
use App\Models\Email;
use App\Models\User;

use Illuminate\Http\Request;

class EmailsController extends AdminController
{
    public function list( $what=null ) {
        if(!in_array($what, Email::$template_types)) {
            return redirect('cms/'.$this->current_page.'/'.current(Email::$template_types));
        }

        $templates = EmailTemplate::where('type', $what)->orderBy('updated_at', 'ASC')->get();

    	return $this->showView('emails', array(
            'templates' => $templates,
            'platform' => $what
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
                $user->sendGridTemplate(49);
            }
        }

        $this->request->session()->flash('success-message', 'Emails send');
        return redirect('cms/'.$this->current_page);
    }
}