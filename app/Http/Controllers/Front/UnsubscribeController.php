<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\FrontController;

use App\Models\IncompleteRegistration;
use App\Models\AnonymousUser;
use App\Models\User;

use App\Helpers\GeneralHelper;

class UnsubscribeController extends FrontController {

	/**
     * oldest unsubscribe link
     */
	public function unsubscribe($locale=null, $user_id, $hash) {
		$user = User::find($user_id);
		
		if (!empty($user) && $hash == $user->get_token()) {
			return redirect( 'https://api.dentacoin.com/api/update-single-email-preference/'.'?'. http_build_query([
				'fields' => urlencode(GeneralHelper::encrypt(json_encode(array(
					'email' => $user->email,
					'email_category' => 'website_notifications',
					'platform' => $user->platform 
				))))
			]));
		}

		return redirect( getLangUrl('/') );
	}

	/**
     * old unsubscribe link
     */
	public function new_unsubscribe($locale=null, $user_id, $hash) {
		$user = User::find($user_id);
		
		if (!empty($user) && $hash == $user->get_token()) {
			return redirect( 'https://api.dentacoin.com/api/update-single-email-preference/'.'?'. http_build_query([
				'fields' => urlencode(GeneralHelper::encrypt(json_encode(array(
					'email' => $user->email,
					'email_category' => 'website_notifications',
					'platform' => $user->platform 
				))))
			]));
		}

		return redirect( getLangUrl('/') );
	}

	/**
     * old unsubscribe link for incomplete registration
     */
	public function unsubscribe_incomplete($locale=null, $id, $hash) {

		$ir = IncompleteRegistration::find($id);

		if (!empty($ir) && $hash == md5($id.env('SALT_INVITE')) ) {

			$existing_anonymous = AnonymousUser::where('email', 'LIKE', $ir->email)->first();
			
			if(!empty($existing_anonymous)) {
				$unsubscribe_cats = $existing_anonymous->unsubscribed_website_notifications;

				if(!isset($unsubscribe_cats['trp'])) {
					$unsubscribe_cats[] = 'trp';
					$existing_anonymous->unsubscribed_website_notifications = $unsubscribe_cats;
					$existing_anonymous->save();
				}
			} else {
				$new_anonymous_user = new AnonymousUser;
				$new_anonymous_user->email = $ir->email;
				$new_anonymous_user->unsubscribed_website_notifications = ['trp'];
				$new_anonymous_user->save();
			}

			return redirect( 'https://api.dentacoin.com/api/update-single-email-preference/'.'?'. http_build_query([
				'fields' => urlencode(GeneralHelper::encrypt(json_encode(array(
					'email' => $ir->email,
					'email_category' => 'website_notifications',
					'platform' => 'trp' 
				))))
			]));
		}

		return redirect( getLangUrl('/') );
	}
}