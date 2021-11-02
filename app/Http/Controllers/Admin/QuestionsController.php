<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Question;

use Request;
use Auth;

class QuestionsController extends AdminController {

    public function list( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	return $this->showView('questions', array(
            'questions' => Question::orderBy('order', 'ASC')->get()
        ));
    }

    public function add( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
        
            Request::session()->flash('success-message', 'Question added');
            return redirect('cms/trp/'.$this->current_subpage);
        }

        return $this->showView('questions-form');
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        Question::destroy( $id );

        $this->request->session()->flash('success-message', 'Question deleted' );
        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
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
            
                Request::session()->flash('success-message', 'Question updated');
                return redirect('cms/trp/'.$this->current_subpage);
            }

            return $this->showView('questions-form', array(
                'item' => $item,
            ));
        } else {
            return redirect('cms/trp/'.$this->current_subpage);
        }
    }
}