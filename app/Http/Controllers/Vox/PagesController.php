<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;
use App\Models\Page;
use App;

class PagesController extends FrontController
{

	public function home($locale=null, $slug=null) {

        $page = Page::translatedIn(App::getLocale())->whereTranslationLike('slug', $slug)->first();
        
        if (!empty($page)) {

            return $this->ShowVoxView('page', array(
                'satic_page' => true,
                'page' => $page,
                'seo_title' => $page->title,
                'seo_description' => $page->description,
                'social_title' => $page->title,
                'social_description' => $page->description,
                'social_image' => $page->getImageUrl(),
            )); 
        }

        return redirect('/');
	}

}