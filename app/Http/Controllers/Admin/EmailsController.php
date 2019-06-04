<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;

use App\Models\EmailTemplate;
use App\Models\Email;

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
                $translation->save();
            }
            $template->save();

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.saved'));
            return redirect('cms/'.$this->current_page.'/'.$template->type);
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

}

//