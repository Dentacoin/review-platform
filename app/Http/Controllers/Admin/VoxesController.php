<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\AdminController;
use App\Models\Vox;
use App\Models\VoxIdea;
use App\Models\VoxQuestion;
use Request;
use Route;


class VoxesController extends AdminController
{

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->types = [
            'hidden' => trans('admin.enums.type.hidden'),
            'normal' => trans('admin.enums.type.normal'), 
            'home' => trans('admin.enums.type.home')
        ];
    }

    public function list( ) {

    	return $this->showView('voxes', array(
            'voxes' => Vox::orderBy('id', 'DESC')->get()
        ));
    }

    public function add( ) {

        if(Request::isMethod('post')) {

            $newvox = new Vox;
            $this->saveOrUpdate($newvox);


            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$newvox->id);
        }

        return $this->showView('voxes-form', array(
            'types' => $this->types
        ));
    }

    public function delete( $id ) {
        Vox::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/'.$this->current_page);
    }

    public function edit( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                $this->saveOrUpdate($item);
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated'));
                return redirect('cms/'.$this->current_page);
            }

            return $this->showView('voxes-form', array(
                'types' => $this->types,
                'item' => $item
            ));
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function add_question( $id ) {
        $item = Vox::find($id);

        if(!empty($item)) {

            $question = new VoxQuestion;
            $question->vox_id = $item->id;
            $this->saveOrUpdateQuestion($question);
        
            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-added'));
            return redirect('cms/'.$this->current_page.'/edit/'.$item->id);

        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function edit_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox->id==$id) {

            //dd($question);

            if(Request::isMethod('post')) {

                $this->saveOrUpdateQuestion($question);
            
                Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-updated'));
                return redirect('cms/'.$this->current_page.'/edit/'.$id);
            }

            return $this->showView('voxes-form-question', array(
                'question' => $question,
                'item' => $question->vox
            ));

        } else {
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }
    }

    public function delete_question( $id, $question_id ) {
        $question = VoxQuestion::find($question_id);

        if(!empty($question) && $question->vox->id==$id) {

            $question->delete();

            Request::session()->flash('success-message', trans('admin.page.'.$this->current_page.'.question-deleted'));
            return redirect('cms/'.$this->current_page.'/edit/'.$id);

        } else {
            return redirect('cms/'.$this->current_page.'/edit/'.$id);
        }
    }

    private function saveOrUpdate($item) {
        $item->reward = $this->request->input('reward');
        $item->duration = $this->request->input('duration');
        $item->type = $this->request->input('type');
        $item->save();

        foreach ($this->langs as $key => $value) {
            if(!empty($this->request->input('title-'.$key))) {
                $translation = $item->translateOrNew($key);
                $translation->vox_id = $item->id;
                $translation->title = $this->request->input('title-'.$key);
                $translation->description = $this->request->input('description-'.$key);
                $translation->save();
            }
        }
        $item->save();

    }

    private function saveOrUpdateQuestion($question) {
        $question->is_control = $this->request->input('is_control');
        $question->go_back = $this->request->input('go_back');
        $question->order = $this->request->input('order');
        $question->save();

        foreach ($this->langs as $key => $value) {
            if(!empty($this->request->input('question-'.$key))) {
                $translation = $question->translateOrNew($key);
                $translation->vox_question_id = $question->id;
                $translation->question = $this->request->input('question-'.$key);

                if(!empty( $this->request->input('answers-'.$key) )) {
                    $translation->answers = json_encode( $this->request->input('answers-'.$key) );
                } else {
                    $translation->answers = '';                            
                }

                $translation->save();
            }
        }
        $question->save();

    }

    public function ideas( ) {

        return $this->showView('vox-ideas', array(
            'ideas' => VoxIdea::orderBy('id', 'DESC')->get()
        ));
    }

}