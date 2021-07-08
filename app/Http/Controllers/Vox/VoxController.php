<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Services\VoxService as ServicesVox;

use App\Models\Vox;

use Response;
use Request;

class VoxController extends FrontController {

	/**
     * Single vox page by slug
     */
	public function vox($locale=null, $slug) {
		$vox = Vox::whereTranslationLike('slug', $slug)->with('questions')->first();

		if(Request::isMethod('post')) {
			if (empty($vox) || $vox->id == 11) {
				$ret['success'] = false;

    			return Response::json( $ret );
			}

			return ServicesVox::surveyAnswer($vox, $this->user, false);
		} else {
			$doVox = ServicesVox::doVox($vox, $this->user, false);

			if(isset($doVox['view'])) {
				if($doVox['view'] == 'vox') {
					$this->current_page = 'questionnaire';
				}
				return $this->ShowVoxView($doVox['view'], $doVox['params']);

			} else if( isset($doVox['url'])) {
				return redirect($doVox['url']);
			}
		}
	}

	/**
     * bottom content of single vox page
     */
	public function vox_public_down($locale=null) {
		$featured_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->where('featured', true)->orderBy('sort_order', 'ASC')->take(9)->get();

		if( $featured_voxes->count() < 9 ) {

			$arr_v = [];
			foreach ($featured_voxes as $fv) {
				$arr_v[] = $fv->id;
			}

			$swiper_voxes = Vox::with('translations')->with('categories.category')->with('categories.category.translations')->where('type', 'normal')->whereNotIn('id', $arr_v)->orderBy('sort_order', 'ASC')->take( 9 - $featured_voxes->count() )->get();

			$featured_voxes = $featured_voxes->concat($swiper_voxes);
		}
		return $this->ShowVoxView('template-parts.recent-surveys-vox-public', array(
        	'voxes' => $featured_voxes,
        ));	
	}

	/**
     * Start the vox again from the first question
     */
	public function start_over() {

		return ServicesVox::startOver($this->user->id);
	}

	/**
     * Get next question of the vox
     */
    public function getNextQuestion() {

        return ServicesVox::getNextQuestionFunction($this->admin, $this->user, false, $this->country_id);
    }
}