<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use DB;

use App\Models\Dcn;
use App\Models\DcnTransaction;
use App\Models\User;
use App\Models\VoxCashout;
use App\Models\TrpReward;
use App\Models\Email;

class YouTubeController extends FrontController
{
    public function test() {



        //Logins
        $users = DB::select( DB::raw('
            SELECT `id` FROM `users` WHERE `gdpr_privacy` IS NULL AND `deleted_at` IS NULL AND `id` NOT IN ( SELECT `user_id` FROM `emails` WHERE `template_id` IN (31,32) ) LIMIT 100
        ') );

        foreach ($users as $user) {
            $u = User::find($user->id);
            if($u->email) {
                $hasemail = Email::where('user_id', $u->id)->whereIn('template_id', [31,32])->first();
                if( $hasemail ) {
                    echo $u->id.' -> IMA<br/>';
                } else {
                    $u->sendTemplate($u->platform=='trp' ? 31 : 32);
                    echo $u->id.'<br/>';                    
                }
            }
        }
        dd('done');


        exit;
        $address = '0x6Ae191bf2c748308a8F29334F207D6d45EFd3504';
        $amount = 50;

        $post = array(
            "address" => $address,
            "amount" => $amount,
            "token" => md5( $address . 'dcn'.$amount.'dentacoin' )
        );
        $ch = curl_init('https://dentacoin.net/dcn');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        curl_close($ch);
        dd($response);
        exit;
    	set_time_limit (600);

    	// $user = User::find(5352);
    	// $user->sendTemplate(28);
    	// exit;

    	$users = [];

    	$total = 0;
    	$trans = DcnTransaction::where('type', '!=', 'vox-cashout')->where('type', '!=', 'trp-cashout')->where('status', 'failed')->get();
    	foreach ($trans as $t) {
    		echo 'User ID: '.$t->user_id.' Amount: '.$t->amount.' / Ref: '.$t->reference_id;
            if($t->type=='register-reward') {
                echo '-> Register reward (SKIP)<br/>';
            } else {
                echo '-> '.$t->type.'<br/>';                
                //,'review','review-dentist','invite-reward',
                //'review','registration','invitation','dentist-review'
        		
                $users[$t->user_id] = $t->user_id;
                $total += $t->amount;

                $reward = new TrpReward();
                $reward->user_id = $t->user_id;
                $reward->reward = $t->amount;
                $reward->type = $t->type=='review-dentist' ? 'dentist-review' : ($t->type=='invite-reward' ? 'invitation' : 'review');
                $reward->reference_id = $t->reference_id;
                $reward->save();                            
            }

            $t->delete();

            if(count($users)>100) {
                break;
            }

    	}
    	echo '========<br/>DCNs affected: '.$total.'<br/>========<br/>';

        $em=0;
    	foreach ($users as $uid) {
    		$u = User::find($uid);
    		if(!empty($u)) {
	    		echo 'Sending email to: '.$u->email;
	    		// $template = Email::where('user_id', $uid)->where('template_id', 28)->first();
	    		// if(!empty($template)) {
	    		// 	echo 'ALREADY SENT';
	    		// } else {
	    			echo 'SENDING NOW!';
	    			$u->sendTemplate(28);
	    		// }
                $em++;
	    		echo '<br/>';
    		} else {
    			echo 'Skipping: '.$uid.'<br/>';
    		}
    	}

    	echo 'Mails sent: '.$em;


    	echo 'DONE';
    }
}