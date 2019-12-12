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

        if( null !== $this->request->input('results-number')) {
            $results = trim($this->request->input('results-number'));
        } else {
            $results = 500;
        }

        if($results == 0) {
            $recommendations = $recommendations->take(1000)->get();
        } else {
            $recommendations = $recommendations->take($results)->get();
        }

        return $this->showView('recommendations', array(
            'recommendations' => $recommendations,
            'search_name_user' => $this->request->input('search-name-user'),
            'search_user_id' => $this->request->input('search-user-id'),
            'results_number' => $this->request->input('results-number'),
        ));
    }

}
