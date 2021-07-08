<?php

namespace App\Http\Controllers\Vox;

use App\Http\Controllers\FrontController;

use App\Models\Vox;

use App;

class SitemapController extends FrontController {

	/**
     * sitemaps for DentaVox
     */
	public function links($locale=null) {

		$links = [
			getLangUrl('/'),
			getLangUrl('welcome-survey'),
			getLangUrl('dental-survey-stats'),
			getLangUrl('daily-polls'),
			getLangUrl('faq'),
		];

		$voxes = Vox::where('type', 'normal')->get();

		foreach ($voxes as $vox) {
			$links[] = $vox->getLink();

			if (!empty($vox->translate(App::getLocale(), true)->slug) && $vox->has_stats) {
				$links[] = $vox->getStatsList();
			}
		}

		$content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		foreach ($links as $link) {
			$content .= '<url><loc>'.$link.'</loc></url>';
		}
		$content .= '</urlset>';

		return response($content, 200)
            ->header('Content-Type', 'application/xml');        
	}

	/**
     * sitemaps list for DentaVox & DentaVox blog
     */
	public function sitemap($locale=null) {

        $content = '<?xml version="1.0" encoding="UTF-8"?>
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		   <sitemap>
		      <loc>https://dentavox.dentacoin.com/sitemap-dentavox.xml</loc>
		      <lastmod>2019-11-10</lastmod>
		   </sitemap>
		   <sitemap>
		      <loc>https://dentavox.dentacoin.com/blog/sitemap_index.xml</loc>
		      <lastmod>2019-11-10</lastmod>
		   </sitemap>
		</sitemapindex>';
        
		return response($content, 200)
            ->header('Content-Type', 'application/xml');
	}

}