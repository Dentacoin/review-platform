<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Recommendation;

use App\Helpers\AdminHelper;

use Request;
use Auth;

class RecommendationsController extends AdminController {
    
    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $recommendations = Recommendation::orderBy('id', 'DESC');

        if(!empty($this->request->input('search-user-id'))) {
            $id = $this->request->input('search-user-id');
            $recommendations = $recommendations->whereHas('user', function ($query) use ($id) {
                $query->where('id', $id);
            });
        }
        if(!empty($this->request->input('search-name-user'))) {
            $name = $this->request->input('search-name-user');
            $recommendations = $recommendations->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'LIKE', $name.'%');
            });
        }
        if(!empty($this->request->input('search-scale'))) {
            $recommendations = $recommendations->where('scale', $this->request->input('search-scale'));
        }
        if(!empty($this->request->input('with-comment'))) {
            $recommendations = $recommendations->whereNotNull('description');
        }

        $total_count = $recommendations->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $recommendations = $recommendations->skip( ($page-1)*$ppp )->take($ppp)->get();

        $current_url = url('cms/vox/recommendations');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('recommendations', array(
            'recommendations' => $recommendations,
            'search_name_user' => $this->request->input('search-name-user'),
            'search_user_id' => $this->request->input('search-user-id'),
            'search_scale' => $this->request->input('search-scale'),
            'with_comment' => $this->request->input('with-comment'),
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
}
