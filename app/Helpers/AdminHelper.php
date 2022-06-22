<?php

namespace App\Helpers;

use App\Models\DcnTransactionHistory;
use App\Models\UserHistory;

class AdminHelper {

    public static function paginationsFunction($total_pages, $adjacents, $page) {

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
            $start = 1;
            $end   = $total_pages;
        } else {
            if(($page - $adjacents) > 1) { 
                if(($page + $adjacents) < $total_pages) { 
                    $start = ($page - $adjacents);            
                    $end   = ($page + $adjacents);         
                } else {             
                    $start = ($total_pages - (1+($adjacents*2)));  
                    $end   = $total_pages;               
                }
            } else {               
                $start = 1;                                
                $end   = (1+($adjacents * 2));             
            }
        }

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    public static function bumpTransaction($transaction, $admin_id=null) {

        if($transaction->status == 'first' && !empty($transaction->user) && !$transaction->user->is_dentist) {
            $user_history = new UserHistory;
            if($admin_id) {
                $user_history->admin_id = $admin_id;
            }
            $user_history->user_id = $transaction->user->id;
            $user_history->patient_status = $transaction->user->patient_status;
            $user_history->save();

            $transaction->user->patient_status = 'new_verified';
            $transaction->user->save();
        }

        $dcn_history = new DcnTransactionHistory;
        $dcn_history->transaction_id = $transaction->id;
        if($admin_id) {
            $dcn_history->admin_id = $admin_id;
        }
        $dcn_history->status = 'new';
        $dcn_history->old_status = $transaction->status;
        $dcn_history->history_message = $admin_id ? 'Bumped by admin' : 'Bumped automatically';
        $dcn_history->save();

        $transaction->status = 'new';
        $transaction->processing = 0;
        $transaction->retries = 0;
        $transaction->save();
    }
}