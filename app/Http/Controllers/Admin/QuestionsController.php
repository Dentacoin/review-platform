<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Question;
use Request;


class QuestionsController extends AdminController
{
    public function list( ) {

    	return $this->showView('questions', array(
            'questions' => Question::orderBy('order', 'ASC')->get()
        ));
    }

    public function add( ) {

        if(Request::isMethod('post')) {
            $item = new Question;
            $item->order = $this->request->input('order');
            $item->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('question-'.$key))) {
                    $translation = $item->translateOrNew($key);
                    $translation->question_id = $item->id;
                    $translation->question = $this->request->input('question-'.$key);
                    $translation->label = $this->request->input('label-'.$key);
                    if(!empty( $this->request->input('options-1-'.$key) )) {
                        $opts = [];
                        $o2 = $this->request->input('options-2-'.$key);
                        foreach ( $this->request->input('options-1-'.$key) as $k => $o1 ) {
                            $opts[$k] = [$o1, $o2[$k]];
                        }

                        $translation->options = json_encode( $opts );
                    } else {
                        $translation->options = '';                            
                    }
                    $translation->save();
                }
            }
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added'));
            return redirect('cms/'.$this->current_page);
        }

        return $this->showView('questions-form', array(
        ));
    }

    public function delete( $id ) {
        Question::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function edit( $id ) {
        $item = Question::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {
                $item->order = $this->request->input('order');

                foreach ($this->langs as $key => $value) {
                    if(!empty($this->request->input('question-'.$key))) {
                        $translation = $item->translateOrNew($key);
                        $translation->question_id = $item->id;
                        $translation->question = $this->request->input('question-'.$key);
                        $translation->label = $this->request->input('label-'.$key);
                        if(!empty( $this->request->input('options-1-'.$key) )) {
                            $opts = [];
                            $o2 = $this->request->input('options-2-'.$key);
                            foreach ( $this->request->input('options-1-'.$key) as $k => $o1 ) {
                                $opts[$k] = [$o1, $o2[$k]];
                            }

                            $translation->options = json_encode( $opts );
                        } else {
                            $translation->options = '';                            
                        }
                        $translation->save();
                    }
                }
                $item->save();
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/'.$this->current_page);
            }

            return $this->showView('questions-form', array(
                'item' => $item,
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

}