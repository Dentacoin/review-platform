<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Models\Country;
use App\Models\User;
use App\Models\Vox;

use Response;
use Request;
use Auth;

class StatsController extends ApiController {

	public function allStats() {

		$voxes = Vox::with('stats_main_question')->where('has_stats', 1);

		if(request('search_title')) {
			$searchTitle = trim(Request::input('search_title'));
			$titles = preg_split('/\s+/', $searchTitle, -1, PREG_SPLIT_NO_EMPTY);

			$voxes->whereHas('translations', function ($query) use ($titles) {
				foreach ($titles as $title) {
					$query->where('title', 'LIKE', '%'.$title.'%')->where('locale', 'LIKE', 'en');
		        }
			});
		}

		$voxes = $voxes->get();

		$voxes = $voxes->sortByDesc(function ($vox, $key) {
            if($vox->stats_featured) {
                return 10000000000 + ($vox->launched_at ? $vox->launched_at->timestamp : 0);
            } else {

                return 10000 + ($vox->launched_at ? $vox->launched_at->timestamp : 0);
            }
        });

		foreach ($voxes as $fv) {
			$fv->thumb = $fv->getImageUrl(true);
		}

        $get = request()->query();
        unset($get['page']);
        unset($get['submit']);

		$voxes = $this->paginate($voxes, 10, request('slice') ?? 1)->appends($get);

		return Response::json( array(
			'voxes' => $voxes,			
			'countries' => Country::with('translations')->get(),
		) );
	}

    private function paginate($items, $perPage, $page, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $pageItems = $perPage;
        $user = Auth::guard('api')->user();
        if(!empty($user) && $user->is_dentist && $page == 1) {
        	$pageItems = $pageItems - 1;
        }
        return new LengthAwarePaginator($items->forPage($page, $pageItems), $items->count(), $pageItems, $page, $options);
    }
    
}