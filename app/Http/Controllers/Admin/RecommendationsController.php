<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Recommendation;
use App\Models\User;
use Carbon\Carbon;

use Request;
use Route;

class RecommendationsController extends AdminController
{
    public function list() {

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

        $total_count = $recommendations->count();

        $page = max(1,intval(request('page')));
        
        $ppp = 100;
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

        $recommendations = $recommendations->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $current_url = url('cms/vox/recommendations');

        $pagination_link = (!empty($this->request->input('search-user-id')) ? '&search-user-id='.$this->request->input( 'search-user-id' ) : '').(!empty($this->request->input('search-name-user')) ? '&search-name-user='.$this->request->input( 'search-name-user' ) : '').(!empty($this->request->input('search-scale')) ? '&search-scale='.$this->request->input( 'search-scale' ) : '');

        return $this->showView('recommendations', array(
            'recommendations' => $recommendations,
            'search_name_user' => $this->request->input('search-name-user'),
            'search_user_id' => $this->request->input('search-user-id'),
            'search_scale' => $this->request->input('search-scale'),
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
