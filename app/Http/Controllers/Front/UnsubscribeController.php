<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\UserInvite;
use CArbon\Carbon;

use App;
use Mail;
use Response;
use Request;
use Validator;

class UnsubscribeController extends FrontController
{

	public function unsubscribe($locale=null, $user_id, $hash) {

		$user = User::find($user_id);

		if (!empty($user) && $hash == $user->get_token() ) {

			if (!$user->unsubscribe) {
				$user->unsubscribe = true;
				$user->save();

				$mtext = 'New user unsubscribe 
Link to user\'s profile in CMS: https://reviews.dentacoin.com/cms/users/edit/'.$user->id;

	            Mail::raw($mtext, function ($message) use ($user) {

	                $sender = config('mail.from.address');
	                $sender_name = config('mail.from.name');

	                $message->from($sender, $sender_name);
	                $message->to( 'petya.ivanova@dentacoin.com' );
	                $message->to( 'donika.kraeva@dentacoin.com' );
	                $message->replyTo($user->email, $user->getName());
	                $message->subject('New dentist unsubscribe');
	            });
			}

			$on_invites = UserInvite::where('invite_id', $user->id)->whereNull('unsubscribe')->get();

			if (!empty($on_invites)) {
				foreach ($on_invites as $inv) {
					$inv->unsubscribe = true;
					$inv->save();
				}
			}

	        return $this->ShowView('unsubscribe-dentist', [
	        	'noIndex' => true,
	        ]);
		}

		return redirect( getLangUrl('/') );
	}
}