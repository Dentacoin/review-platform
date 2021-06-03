<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\AdminController;

use App\Models\SupportQuestion;
use App\Models\SupportCategory;
use App\Models\SupportContact;

use Carbon\Carbon;

use Validator;
use Response;
use Request;

class SupportController extends AdminController {

    public function content( ) {
        
        $categories = SupportCategory::get();

        return $this->showView('support', array(
            'categories' => $categories,
        ));
    }

    public function add_content( ) {

        $slug = str_slug($this->request->input('slug'), '-');

        $validator = Validator::make(['slug' => $slug], [
            'slug' => 'required|unique:support_questions,slug|max:128'
        ]);

        $validator = Validator::make($this->request->all(), [
            'question' => array('required'),
            'slug' => array('required'),
            'answer' => array('required'),
            'category_id' => array('required'),
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $item = new SupportQuestion;
            $item->category_id = $this->request->input('category_id');
            $item->is_main = $this->request->input('is_main');
            $item->save();

            $translation = $item->translateOrNew('en');
            $translation->support_question_id = $item->id;
            $translation->question = $this->request->input('question');
            $translation->slug = $this->request->input('slug');
            $translation->content = $this->request->input('answer');
            $translation->save();
        
            return Response::json( ['success' => true, 'q_id' => $item->id] );
        }
    }

    public function delete_content( $id ) {
        SupportQuestion::destroy( $id );

        return Response::json( ['success' => true] );
    }

    public function edit_content( $id ) {
        $item = SupportQuestion::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                foreach ($this->langs as $key => $value) {
                    if(!empty($this->request->input('question-'.$key))) {
                        $translation = $item->translateOrNew($key);
                        $translation->support_question_id = $item->id;
	                    $translation->question = $this->request->input('question-'.$key);
	                    $translation->slug = $this->request->input('slug-'.$key);
	                    $translation->content = $this->request->input('answer-'.$key);
                        $translation->save();
                    }
                }

                $item->category_id = $this->request->input('category_id');
	            $item->is_main = $this->request->input('is_main');
	            $item->save();
            
                Request::session()->flash('success-message', 'Question updated');
                return redirect('cms/support/content');
            }

            return $this->showView('support-edit', [
            	'item' => $item,
                'categories' => SupportCategory::get(),
            ]);
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function categories() {
    	$categories = SupportCategory::get();

        return $this->showView('support-categories', array(
            'categories' => $categories,
        ));
    }

    public function add_category( ) {

        if(Request::isMethod('post')) {
            $item = new SupportCategory;
            $item->name = $this->request->input('category-name-en');
            $item->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('category-name-'.$key))) {
                    $translation = $item->translateOrNew($key);
                    $translation->support_category_id = $item->id;
                    $translation->name = $this->request->input('category-name-'.$key);
                    $translation->save();
                }
            }
        
            Request::session()->flash('success-message', 'Category Added');
            return redirect('cms/support/categories');
        }

        return $this->showView('support-categories-form');
    }

    public function delete_category( $id ) {
        SupportCategory::destroy( $id );

        $this->request->session()->flash('success-message', 'Category Deleted' );
        return redirect('cms/support/categories');
    }

    public function edit_category( $id ) {
        $item = SupportCategory::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                foreach ($this->langs as $key => $value) {
                    if(!empty($this->request->input('category-name-'.$key))) {
                        $translation = $item->translateOrNew($key);
                        $translation->support_category_id = $item->id;
                        $translation->name = $this->request->input('category-name-'.$key);
                        $translation->save();
                    }
                }
            
                Request::session()->flash('success-message', 'Category updated');
                return redirect('cms/support/categories');
            }

            return $this->showView('support-categories-form', [
            	'item' => $item
            ]);
        } else {
            return redirect('cms/'.$this->current_page);
        }
    }

    public function contact() {

        $items = SupportContact::orderBy('id', 'desc');

        if(!empty(request('search-user-id'))) {
            $items = $items->where('user_id', request('search-user-id'));
        }

        if(!empty(request('search-email'))) {
            $items = $items->whereHas('user', function($query) {
                $query->where('email', 'LIKE', '%'.trim(request('search-email')).'%');
            })->orWhere('email', 'LIKE', request('search-email'));
        }

        if(!empty(request('search-name'))) {
            $items = $items->whereHas('user', function($query) {
                $query->where('name', 'LIKE', '%'.trim(request('search-name')).'%');
            });
        }

        if(!empty(request('search-platform'))) {
            $items = $items->where('platform', request('search-platform'));
        }

        if(!empty(request('search-issue'))) {
            $items = $items->where('issue', request('search-issue'));
        }

        if(!empty(request('search-from'))) {
            $firstday = new Carbon(request('search-from'));
            $items = $items->where('created_at', '>=', $firstday);
        }
        if(!empty(request('search-to'))) {
            $firstday = new Carbon(request('search-to'));
            $items = $items->where('created_at', '<=', $firstday->addDays(1));
        }

        $total_count = $items->count();

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

        $items = $items->skip( ($page-1)*$ppp )->take($ppp)->get();

        $pagination_link = '';

        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->ShowView('support-contact', array(
            'items' => $items,
            'search_email' => request('search-email'),
            'search_user_id' => request('search-user-id'),
            'search_name' => request('search-name'),
            'search_platform' => request('search-platform'),
            'search_issue' => request('search-issue'),
            'search_from' => request('search-from'),
            'search_to' => request('search-to'),
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'video_extensions' => ['mp4', 'm3u8', 'ts', 'mov', 'avi', 'wmv', 'qt'],
            'image_extensions' => ['png', 'jpg', 'jpeg'],
        ));
    }

}