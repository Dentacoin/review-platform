<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Input;

use App\Helpers\GeneralHelper;
use App\Helpers\AdminHelper;

use App\Models\Meeting;

use Validator;
use Request;
use Image;
use Auth;

class MeetingsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $meetings = Meeting::orderBy('id', 'asc');

        $total_count = $meetings->count();
        $page = max(1,intval(request('page')));
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $meetings = $meetings->skip( ($page-1)*$ppp )->take($ppp)->get();

        $current_url = url('cms/meetings');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

    	return $this->showView('meetings', array(
            'meetings' => $meetings,
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
        ));
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	$item = Meeting::find($id);

        if(!empty($item)) {

	        if(Request::isMethod('post')) {

                $extensions = ['png', 'jpg', 'jpeg'];

                $validator = Validator::make(Request::all(), [
                    'photo' => array('mimes:'.implode(',', $extensions)),
                    'website-photo' => array('mimes:'.implode(',', $extensions)),
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
                    
                    $this->request->session()->flash('error-message', 'File extension not supported' );
                    return redirect('cms/meetings/edit/'.$id);
                } else {

                    $item->seo_title = $this->request->input('seo_title');
                    $item->seo_description = $this->request->input('seo_description');

                    if(!empty( $this->request->input('checklists') )) {
                        $newchecklists = $this->request->input('checklists');

                        $newchecklistsArr = [];
                        foreach ($newchecklists as $ka => $va) {
                            if(!empty($va)) {
                                $newchecklistsArr[] = $va;
                            }
                        }
                        $item->checklists = json_encode( $newchecklistsArr );
                    }

                    if(!empty( $this->request->input('after_checklist_info') )) {
                        $newchecklists = $this->request->input('after_checklist_info');

                        $newchecklistsArr = [];
                        foreach ($newchecklists as $ka => $va) {
                            if(!empty($va)) {
                                $newchecklistsArr[] = $va;
                            }
                        }
                        $item->after_checklist_info = json_encode( $newchecklistsArr );
                    }

                    $item->checklist_title = $this->request->input('checklist_title');
                    $item->duration = $this->request->input('duration');
                    $item->video_id = $this->request->input('video_id');
                    $item->video_title = $this->request->input('video_title');
                    $item->iframe_id = $this->request->input('iframe_id');
                    $item->website_url = $this->request->input('website_url');
                    $item->save();

                    $allowedExtensions = array('jpg', 'jpeg', 'png');
                    $allowedMimetypes = ['image/jpeg', 'image/png'];

                    if( Input::file('photo') ) {
                        
                        $checkFile = GeneralHelper::checkFile(Input::file('photo'), $allowedExtensions, $allowedMimetypes);

                        if(isset($checkFile['success'])) {
                            $img = Image::make( Input::file('photo') )->orientate();
                            $item->addImage($img);
                        } else {
                            Request::session()->flash('error-message', $checkFile['error']);
                            return redirect('cms/meetings/edit/'.$id);
                        }
                    }

                    if( Input::file('website-photo') ) {

                        $checkFile = GeneralHelper::checkFile(Input::file('website-photo'), $allowedExtensions, $allowedMimetypes);

                        if(isset($checkFile['success'])) {
                            $img = Image::make( Input::file('website-photo') )->orientate();
                            $item->addWebsiteImage($img);
                        } else {
                            Request::session()->flash('error-message', $checkFile['error']);
                            return redirect('cms/meetings/edit/'.$id);
                        }
                    }
                }
	        }

	        return $this->showView('meetings-form', array(
	            'item' => $item,
	        ));
	    } else {
            return redirect('cms/meetings/');
        }
    }
}