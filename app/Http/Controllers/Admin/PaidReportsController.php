<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Input;

use App\Models\PaidReportPhoto;
use App\Models\PaidReport;

use App\Helpers\GeneralHelper;
use App\Helpers\AdminHelper;
use Carbon\Carbon;

use Response;
use Request;
use Image;
use Auth;

class PaidReportsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $reports = PaidReport::with('translations')->orderBy('id', 'desc');

        if(request('search-title')) {
            $s_title = request('search-title');
            $reports = $reports->whereHas('translations', function ($query) use ($s_title) {
                $query->where('title', 'LIKE', '%'.trim($s_title).'%');
            })->orWhereHas('translations', function ($queryy) use ($s_title) {
                $queryy->where('main_title', 'LIKE', '%'.trim($s_title).'%');
            });
        }

        $total_count = $reports->count();
        $page = max(1,intval(request('page')));
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $reports = $reports->skip( ($page-1)*$ppp )->take($ppp)->get();

        $current_url = url('cms/vox/paid-reports');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

    	return $this->showView('paid-reports', array(
            'reports' => $reports,
            'statuses' => PaidReport::$statuses,
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
            'search_title' => request('search-title')
        ));
    }

    public function add() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $newreport = new PaidReport;
            $newreport->price = $this->request->input('price');
            $newreport->pages_count = $this->request->input('pages_count');
            $newreport->status = $this->request->input('status');
            if($this->request->input('status') == 'published') {
                $newreport->launched_at = Carbon::now();
            }
            $newreport->languages = $this->request->input('languages');
            $newreport->download_format = $this->request->input('download_format');
            $newreport->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('title-'.$key))) {
                    $translation = $newreport->translateOrNew($key);
                    $translation->paid_report_id = $newreport->id;
                    $translation->slug = $this->request->input('slug-'.$key);
                    $translation->main_title = $this->request->input('main-title-'.$key);
                    $translation->title = $this->request->input('title-'.$key);
                    $translation->methodology = $this->request->input('methodology-'.$key);
                    $translation->summary = $this->request->input('summary-'.$key);
                    $translation->short_description = $this->request->input('short-description-'.$key);
                    $translation->save();
                }
                
                if(!empty( $this->request->input('checklists-'.$key) )) {
                    $newchecklists = $this->request->input('checklists-'.$key);

                    $newchecklistsArr = [];
                    foreach ($newchecklists as $ka => $va) {
                        if(!empty($va)) {
                            $newchecklistsArr[] = $va;
                        }
                    }
                    $translation = $newreport->translateOrNew($key);
                    $translation->checklists = json_encode( $newchecklistsArr );
                    $translation->save();
                }

                if(!empty( $this->request->input('table_contents-'.$key) )) {
                    $newContents = $this->request->input('table_contents-'.$key);
                    $newMain = $this->request->input('main-'.$key);
                    $newPage = $this->request->input('page-'.$key);

                    $newContentsArr = [];
                    foreach ($newContents as $ka => $va) {
                        if(!empty($va)) {
                            $newContentsArr[] = [
                                'content' => $va,
                                'is_main' => $newMain[$ka],
                                'page' => $newPage[$ka],
                            ];
                        }
                    }
                    $translation = $newreport->translateOrNew($key);
                    $translation->table_contents = json_encode( $newContentsArr );
                    $translation->save();
                }
            }
            $newreport->save();

            $this->addImages($newreport);

            Request::session()->flash('success-message', 'Paid Report Added');
            return redirect('cms/vox/paid-reports');
        }

        return $this->showView('paid-reports-form', array(
	        'statuses' => PaidReport::$statuses,
	        'languages' => PaidReport::$langs,
	        'formats' => PaidReport::$formats,
        ));
    }

    private function addImages($report) {
        
        $allowedExtensions = array('jpg', 'jpeg', 'png');
        $allowedMimetypes = ['image/jpeg', 'image/png'];

        if( Input::file('photo') ) {

            $checkFile = GeneralHelper::checkFile(Input::file('photo'), $allowedExtensions, $allowedMimetypes);

            if(isset($checkFile['success'])) {
                $img = Image::make( Input::file('photo') )->orientate();
                $report->addImage($img);
            } else {
                $this->request->session()->flash('error-message', $checkFile['error'] );
                return redirect('cms/vox/paid-reports');
            }
        }
        
        if( Input::file('photo-social') ) {

            $checkFile = GeneralHelper::checkFile(Input::file('photo-social'), $allowedExtensions, $allowedMimetypes);

            if(isset($checkFile['success'])) {
                $img = Image::make( Input::file('photo-social') )->orientate();
                $report->addImage($img, 'social');
            } else {
                $this->request->session()->flash('error-message', $checkFile['error'] );
                return redirect('cms/vox/paid-reports');
            }
        }
        
        if( Input::file('photo-all-reports') ) {

            $checkFile = GeneralHelper::checkFile(Input::file('photo-all-reports'), $allowedExtensions, $allowedMimetypes);

            if(isset($checkFile['success'])) {
                $img = Image::make( Input::file('photo-all-reports') )->orientate();
                $report->addImage($img, 'all-reports');
            } else {
                $this->request->session()->flash('error-message', $checkFile['error'] );
                return redirect('cms/vox/paid-reports');
            }
        }

        if(!empty(Input::file('gallery'))) {

            foreach(Input::file('gallery') as $k => $sp) {

                $checkFile = GeneralHelper::checkFile($sp, $allowedExtensions, $allowedMimetypes);

                if(isset($checkFile['success'])) {
                    $sample_page = new PaidReportPhoto;
                    $sample_page->paid_report_id = $report->id;
                    $sample_page->save();
                    $img = Image::make( $sp )->orientate();
                    $sample_page->addImage($img);
                } else {
                    $this->request->session()->flash('error-message', $checkFile['error'] );
                    return redirect('cms/vox/paid-reports');
                }                
            }
        }
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	$item = PaidReport::find($id);

        if(!empty($item)) {

	        if(Request::isMethod('post')) {

                $item->price = $this->request->input('price');
                if($item->status == 'draft' && $this->request->input('status') == 'published') {
                    $item->launched_at = Carbon::now();
                }
                $item->status = $this->request->input('status');
                $item->pages_count = $this->request->input('pages_count');
                $item->languages = $this->request->input('languages');
                $item->download_format = $this->request->input('download_format');
                $item->save();

                foreach ($this->langs as $key => $value) {
                    if(!empty($this->request->input('title-'.$key))) {
                        $translation = $item->translateOrNew($key);
                        $translation->paid_report_id = $item->id;
                        $translation->slug = $this->request->input('slug-'.$key);
                        $translation->main_title = $this->request->input('main-title-'.$key);
                        $translation->title = $this->request->input('title-'.$key);
                        $translation->methodology = $this->request->input('methodology-'.$key);
                        $translation->summary = $this->request->input('summary-'.$key);
                        $translation->short_description = $this->request->input('short-description-'.$key);
                        $translation->save();
                    }
                    
                    if(!empty( $this->request->input('checklists-'.$key) )) {
                        $newchecklists = $this->request->input('checklists-'.$key);

                        $newchecklistsArr = [];
                        foreach ($newchecklists as $ka => $va) {
                            if(!empty($va)) {
                                $newchecklistsArr[] = $va;
                            }
                        }
                        $translation = $item->translateOrNew($key);
                        $translation->checklists = json_encode( $newchecklistsArr );
                        $translation->save();
                    }

                    if(!empty( $this->request->input('table_contents-'.$key) )) {
                        
                        $newContents = $this->request->input('table_contents-'.$key);
                        $newMain = $this->request->input('main-'.$key);
                        $newPage = $this->request->input('page-'.$key);

                        $newContentsArr = [];
                        foreach ($newContents as $ka => $va) {
                            if(!empty($va)) {
                                $newContentsArr[] = [
                                    'content' => $va,
                                    'is_main' => $newMain[$ka],
                                    'page' => $newPage[$ka],
                                ];
                            }
                        }
                        $translation = $item->translateOrNew($key);
                        $translation->table_contents = json_encode( $newContentsArr );
                        $translation->save();
                    }
                }
                $item->save();

                $this->addImages($item);
	        }

	        return $this->showView('paid-reports-form', array(
	            'item' => $item,
                'statuses' => PaidReport::$statuses,
                'languages' => PaidReport::$langs,
                'formats' => PaidReport::$formats,
	        ));
	    } else {
            return redirect('cms/vox/paid-reports/');
        }
    }
    
    public function deleteGalleryPhoto( $id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        PaidReportPhoto::destroy($id);

        return Response::json( [
            'success' => true,
        ] );
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        PaidReport::destroy( $id );

        $this->request->session()->flash('success-message', 'Report deleted' );
        return redirect('cms/vox/paid-reports/');
    }
}