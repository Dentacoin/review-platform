<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Question;

use Response;
use Request;
use Auth;

class QuestionsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	return $this->showView('questions', array(
            'questions' => Question::with('translations')->orderBy('order', 'ASC')->get()
        ));
    }

    public function add() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {
            $item = new Question;
            $this->saveOrUpdateQuestion($item);
        
            Request::session()->flash('success-message', 'Question added');
            return redirect('cms/trp/'.$this->current_subpage);
        }

        return $this->showView('questions-form');
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $item = Question::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {
                $this->saveOrUpdateQuestion($item);
            
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

    private function saveOrUpdateQuestion($item) {
        $item->order = Question::count()+1;
        $item->type = $this->request->input('type');
        $item->save();

        foreach ($this->langs as $key => $value) {
            if(!empty($this->request->input('label-'.$key))) {
                $translation = $item->translateOrNew($key);
                $translation->question_id = $item->id;
                $translation->label = $this->request->input('label-'.$key);
                // $translation->question = $this->request->input('question-'.$key);

                // if(!empty( $this->request->input('options-1-'.$key) )) {
                //     $opts = [];
                //     $o2 = $this->request->input('options-2-'.$key);
                //     foreach ( $this->request->input('options-1-'.$key) as $k => $o1 ) {
                //         $opts[$k] = [$o1, $o2[$k]];
                //     }

                //     $translation->options = json_encode( $opts );
                // } else {
                    $translation->options = '';                            
                // }
                $translation->save();
            }
        }
    }

    public function reorderQuestions() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $i=1;
        foreach (Request::input('list') as $qid) {
            $question = Question::find($qid);
            if( $question ) {
                $question->order = $i;
                $question->save();
                $i++;
            }
        }

        return Response::json([
            'success' => true
        ]);
    }
}