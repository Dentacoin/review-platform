<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\AnonymousUser;
use App\Models\DentistClaim;
use App\Models\UserHistory;
use App\Models\User;

use Auth;
use Mail;
use Auth;

class DentistClaimsController extends AdminController {

    public function approve($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = DentistClaim::find($id);

        $item->status = 'approved';
        $item->save();

        $user = User::find($item->dentist_id);

        //if phone is empty is old added by patient dentist
        if(empty($item->phone) && !empty($user->old_unclaimed_profile)) {
            $user_history = new UserHistory;
            $user_history->user_id = $user->id;
            $user_history->status = $user->status;
            $user_history->save();

            $user->password = bcrypt($item->password);
            $user->status = 'approved';
            $user->save();

            $user->old_unclaimed_profile->completed = true;
            $user->old_unclaimed_profile->save();

            $user->sendGridTemplate(104, [], 'trp');

            $mtext = 'Old Added by Patient Dentist claim request was approved<br/>
Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id;

            Mail::send([], [], function ($message) use ($mtext, $user) {
                $sender = config('mail.from.address');
                $sender_name = config('mail.from.name');

                $message->from($sender, $sender_name);
                $message->to( 'betina.bogdanova@dentacoin.com' );
                $message->to( 'petya.ivanova@dentacoin.com' );
                $message->replyTo($user->email, $user->getNames());
                $message->subject('Old Added by Patient Dentist claim request was approved');
                $message->setBody($mtext, 'text/html'); // for HTML rich messages
            });

        } else {

            $dentist_claims = DentistClaim::where('dentist_id', $item->dentist_id)->where('id', '!=', $item->id)->get();

            if (!empty($dentist_claims)) {
                foreach ($dentist_claims as $dk) {
                    $dk->status = 'rejected';
                    $dk->save();

                    $unsubscribed = User::isUnsubscribedAnonymous(66, 'trp', $dk->email);
                    $u = User::find(113928);
                    $mail = User::unregisteredSendGridTemplate($u, $dk->email, $dk->name, 66, null, 'trp', $unsubscribed, $dk->email);
                    $mail->delete();
                }
            }

            $existing_anonymous = AnonymousUser::where('email', 'LIKE', $item->email)->first();
            if(!empty($existing_anonymous)) {
                AnonymousUser::destroy($existing_anonymous->id);
            }

            $user_history = new UserHistory;
            $user_history->user_id = $user->id;
            $user_history->status = $user->status;
            $user_history->save();

            $user->email_public = $user->email;
            $user->email = $item->email;
            $user->password = bcrypt($item->password);
            $user->status = 'approved';
            $user->save();

            $substitutions = [
                'trp_profile' => getLangUrl('dentist/'.$user->slug, null, 'https://reviews.dentacoin.com/'),
            ];

            $user->sendGridTemplate(26, $substitutions, 'trp');
        }

        return redirect( 'cms/users/users/edit/'.$item->dentist_id );

    }

    public function reject($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = DentistClaim::find($id);

        $item->status = 'rejected';
        $item->save();

        $user = User::find($item->dentist_id);

        $unsubscribed = User::isUnsubscribedAnonymous(66, 'trp', $item->email);

        $u = User::find(113928);
        $mail = User::unregisteredSendGridTemplate($u, $item->email, $item->name, 66, null, 'trp', $unsubscribed, $item->email);
        $mail->delete();


        $mtext = 'Dentist claim request was rejected<br/>
Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id;

        Mail::send([], [], function ($message) use ($mtext, $user) {
            $sender = config('mail.from.address');
            $sender_name = config('mail.from.name');

            $message->from($sender, $sender_name);
            
            $message->to( 'betina.bogdanova@dentacoin.com' );
            $message->to( 'petya.ivanova@dentacoin.com' );
            //$message->to( 'dokinator@gmail.com' );
            $message->replyTo($user->email, $user->getNames());
            $message->subject('Dentist claim request was rejected');
            $message->setBody($mtext, 'text/html'); // for HTML rich messages
        });

        return redirect( 'cms/users/users/edit/'.$item->dentist_id );

    }


    public function suspicious($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $item = DentistClaim::find($id);

        $item->status = 'suspicious';
        $item->save();

        $user = User::find($item->dentist_id);

        $unsubscribed = User::isUnsubscribedAnonymous(67, 'trp', $item->email);

        $u = User::find(113928);
        $mail = User::unregisteredSendGridTemplate($u, $item->email, $item->name, 67, null, 'trp', $unsubscribed, $item->email);
        $mail->delete();


        $mtext = 'Dentist claim request was suspicious<br/>
Link to dentist\'s profile in CMS: https://reviews.dentacoin.com/cms/users/users/edit/'.$user->id;

        Mail::send([], [], function ($message) use ($mtext, $user) {
            $sender = config('mail.from.address');
            $sender_name = config('mail.from.name');

            $message->from($sender, $sender_name);
            $message->to( 'betina.bogdanova@dentacoin.com' );
            $message->to( 'petya.ivanova@dentacoin.com' );
            $message->replyTo($user->email, $user->getNames());
            $message->subject('Dentist claim request was suspicious');
            $message->setBody($mtext, 'text/html'); // for HTML rich messages
        });

        return redirect( 'cms/users/users/edit/'.$item->dentist_id );

    }

}
