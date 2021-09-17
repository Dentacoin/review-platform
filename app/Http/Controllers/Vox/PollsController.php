<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App\Services\VoxService as ServicesVox;

use App\Models\PollsMonthlyDescription;
use App\Models\VoxCategory;
use App\Models\PollAnswer;
use App\Models\DcnReward;
use App\Models\VoxScale;
use App\Models\Country;
use App\Models\PageSeo;
use App\Models\Reward;
use App\Models\User;
use App\Models\Poll;

use Response;
use Request;
use Cookie;
use Auth;

class PollsController extends FrontController {

	/**
     * All daily polls page
     */
	public function list($locale=null) {

		$seos = PageSeo::find(14);

		$monthly_descr = PollsMonthlyDescription::where('month', date('n'))->where('year', date('Y'))->first();
		
		return $this->ShowVoxView('daily-polls', array(
			'js' => [
        		'polls.js'
        	],
			'css' => [
        		'vox-daily-polls.css'
        	],
			'social_image' => $seos->getImageUrl(),
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
            'monthly_descr' => $monthly_descr,
        ));
	}

	/**
     * Show daily poll by date
     */
	public function show_popup_poll($locale=null, $date) {
		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);
		$poll_stats = 0;
		$poll_open = 0;

		$seos = PageSeo::find(14);

		$poll = Poll::where('launched_at', $newformat )->first();
		if (!empty($poll)) {
			$social_image = $poll->getSocialCover();

			if($poll->status != 'scheduled') {
				if($poll->status == 'open') {
					$poll_open = true;
				} else {
					$poll_stats = true;
				}
			}
		} else {
			$social_image = $seos->getImageUrl();
		}
		
		return $this->ShowVoxView('daily-polls', array(
			'show_poll' => true,
			'poll' => $poll,
			'poll_open' => $poll_open,
			'poll_stats' => $poll_stats,
			'js' => [
        		'polls.js'
        	],
			'css' => [
        		'vox-daily-polls.css'
        	],
			'social_image' => $social_image,
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'canonical' => getLangUrl('daily-polls/'.$date),
			'noindex' => true,
        ));
	}

	/**
     * Show daily poll stats by date
     */
	public function show_popup_stats_poll($locale=null, $date) {

		$time = strtotime($date);
		$newformat = date('Y-m-d',$time);
		$poll_stats = 0;

		$seos = PageSeo::find(14);

		$poll = Poll::where('launched_at', $newformat )->first();
		if (!empty($poll)) {
			$social_image = $poll->getSocialCover();

			if($poll->status != 'scheduled' && $poll->status != 'open') {
				$poll_stats = true;
			}
		} else {
			$social_image = $seos->getImageUrl();
		}
		
		return $this->ShowVoxView('daily-polls', array(
			'date_poll' => $newformat,
			'poll' => $poll,
			'poll_stats' => $poll_stats,
			'js' => [
        		'polls.js'
        	],
			'css' => [
        		'vox-daily-polls.css'
        	],
			'social_image' => $social_image,
            'seo_title' => $seos->seo_title,
            'seo_description' => $seos->seo_description,
            'social_title' => $seos->social_title,
            'social_description' => $seos->social_description,
			'canonical' => getLangUrl('daily-polls/'.$date.'/stats'),
			'noindex' => true,
        ));
	}

	/**
     * Get daily poll content by id
     */
	public function getPollContent($locale=null, $poll_id) {
    	return ServicesVox::getPollContent($poll_id, $this->user, $this->admin, false);
	}

	/**
     * Get daily poll stats by id
     */
	public function getPollStats($locale=null, $poll_id) {
		return ServicesVox::getPollStats($poll_id, $this->user);
	}

	/**
     * Answer daily poll
     */
	public function dopoll($locale=null, $id) {
		return ServicesVox::doPoll($id, $this->user, $this->admin, false);
	}

	/**
     * Session to hide the daily poll from the pages
     */
	public function hidePoll( $locale=null ) {

		$sess = [
            'hide_poll' => true,
        ];
        session($sess);

	    return Response::json( [
	        'success' => true,
	    ] );
    }

	public function getCalendarHtml() {
		$calendar = new \App\Helpers\Calendar();

		$monthly_descr = PollsMonthlyDescription::where('month', Request::input('month'))->where('year', Request::input('year'))->first();

		return Response::json( [
			'success' => true,
			'html' => $calendar->show(),
        	'monthly_descr' => $monthly_descr ? $monthly_descr->description : null,
		]);
	}
}