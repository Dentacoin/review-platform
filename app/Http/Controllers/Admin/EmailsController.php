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

        if($what=='vox') {
            $templates = EmailTemplate::whereIn('id', Email::$vox_tempalates)->get();
        } else {
            $templates = EmailTemplate::whereNotIn('id', Email::$vox_tempalates)->get();
        }

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
                $translation->save();
            }
            $template->save();

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.saved'));
            return redirect('cms/'.$this->current_page);
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

}

//