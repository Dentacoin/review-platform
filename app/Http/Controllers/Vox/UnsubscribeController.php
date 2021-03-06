<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use App\Models\IncompleteRegistration;
use App\Models\UnclaimedDentist;
use App\Models\UserInvite;
use App\Models\PageSeo;
use App\Models\User;

use Mail;

class UnsubscribeController extends FrontController {

	/**
     * oldest unsubscribe link
     */
	public function unsubscribe($locale=null, $user_id, $hash) {

		$user = User::find($user_id);

		if (!empty($user) && !$user->unsubscribe) {

			if (!$user->unsubscribe) {

				$mtext = 'User want\'s to be unsubscribed, but needs an approval
Link in CMS: https://dentavox.dentacoin.com/cms/users/users/edit/'.$user->id;

	            Mail::raw($mtext, function ($message) use ($user) {

	                $sender = config('mail.from.address');
	                $sender_name = config('mail.from.name');

	                $message->from($sender, $sender_name);
	                $message->to( 'petya.ivanova@dentacoin.com' );
	                $message->to( 'donika.kraeva@dentacoin.com' );
	                $message->replyTo($user->email, $user->getNames());
	                $message->subject('New unsubscribe request');
	            });
			}

			$seos = PageSeo::find(31);

	        return $this->ShowVoxView('unsubscribe-dentist', [
	        	'noIndex' => true,
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	            'incomplete_alert' => false,
	        ]);
		}

		return redirect( getLangUrl('/') );
	}

	/**
     * old unsubscribe link
     */
	public function new_unsubscribe($locale=null, $user_id, $hash) {

		$user = User::find($user_id);

		if (!empty($user) && $hash == $user->get_token() ) {

			if (!$user->unsubscribe) {
				$user->unsubscribe = true;
				$user->save();

				$mtext = 'This user was automatically unsubscribed - https://dentavox.dentacoin.com/cms/users/users/edit/'.$user->id;

	            Mail::raw($mtext, function ($message) use ($user) {

	                $sender = config('mail.from.address');
	                $sender_name = config('mail.from.name');

	                $message->from($sender, $sender_name);
	                $message->to( 'petya.ivanova@dentacoin.com' );
	                $message->to( 'donika.kraeva@dentacoin.com' );
	                $message->replyTo($user->email, $user->getNames());
	                $message->subject('New user unsubscribe');
	            });
			}

			$on_invites = UserInvite::where('invited_id', $user->id)->get();

			if (!empty($on_invites)) {
				foreach ($on_invites as $inv) {
					$inv->unsubscribed = true;
					$inv->save();
				}
			}

			$unclaimed_dentist = UnclaimedDentist::find($user->id);

			if(!empty($unclaimed_dentist)) {
				$unclaimed_dentist->unsubscribed = true;
				$unclaimed_dentist->save();
			}

			$seos = PageSeo::find(31);

	        return $this->ShowVoxView('unsubscribe-dentist', [
	        	'noIndex' => true,
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	            'incomplete_alert' => false,
	        ]);
		}

		return redirect( getLangUrl('/') );
	}

	/**
     * old unsubscribe link for incomplete registration
     */
	public function unsubscribe_incomplete($locale=null, $id, $hash) {

		$ir = IncompleteRegistration::find($id);

		if (!empty($ir) && $hash == md5($id.env('SALT_INVITE')) ) {

			if (!$ir->unsubscribed) {
				$ir->unsubscribed = true;
				$ir->save();
			}

			$seos = PageSeo::find(31);

	        return $this->ShowVoxView('unsubscribe-dentist', [
	        	'noIndex' => true,
				'social_image' => $seos->getImageUrl(),
	            'seo_title' => $seos->seo_title,
	            'seo_description' => $seos->seo_description,
	            'social_title' => $seos->social_title,
	            'social_description' => $seos->social_description,
	            'incomplete_alert' => true,
	        ]);
		}

		return redirect( getLangUrl('/') );
	}
}