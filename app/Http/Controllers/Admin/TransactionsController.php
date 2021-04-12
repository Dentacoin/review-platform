<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\TransactionScammersByBalance;
use App\Models\TransactionScammersByDay;
use App\Models\DcnTransactionHistory;
use App\Models\WithdrawalsCondition;
use App\Models\StopTransaction;
use App\Models\DcnTransaction;
use App\Models\UserAction;
use App\Models\DcnCashout;
use App\Models\DcnReward;
use App\Models\GasPrice;
use App\Models\User;

use Carbon\Carbon;

use Response;
use Request;
use Route;
use Auth;

class TransactionsController extends AdminController
{
    public function list() {
        $transactions = DcnTransaction::orderBy('id', 'DESC');

        if(!empty($this->request->input('search-address'))) {
            $transactions = $transactions->where('address', 'LIKE', '%'.trim($this->request->input('search-address')).'%');
        }
        if(!empty($this->request->input('search-tx'))) {
            $transactions = $transactions->where('tx_hash', 'LIKE', '%'.trim($this->request->input('search-tx')).'%');
        }
        if(!empty($this->request->input('search-user-id'))) {
            $transactions = $transactions->where('user_id', $this->request->input('search-user-id'));
        }
        if(!empty($this->request->input('search-id'))) {
            $transactions = $transactions->where('id', $this->request->input('search-id'));
        }
        if(!empty($this->request->input('search-status'))) {
            $transactions = $transactions->where('status', $this->request->input('search-status'));
        }        
        if(!empty($this->request->input('search-from'))) {
            $firstday = new Carbon($this->request->input('search-from'));
            $transactions = $transactions->where('created_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-to'))) {
            $firstday = new Carbon($this->request->input('search-to'));
            $transactions = $transactions->where('created_at', '<=', $firstday);
        }
        if(!empty($this->request->input('search-email'))) {
            $mail = $this->request->input('search-email');
            $transactions = $transactions->whereHas('user', function ($query) use ($mail) {
                $query->where('email', 'like', '%'.trim($mail).'%');
            });
        } 

        if(!empty(request()->input( 'paid-by-user'))) {
            $transactions = $transactions->whereNotNull('is_paid_by_the_user');
        }

        if(!empty($this->request->input('created'))) {
            $order = request()->input( 'created' );
            $transactions->getQuery()->orders = null;
            $transactions = $transactions
            ->orderBy('created_at', $order);
        }

        if (!empty(request()->input( 'attempt' ))) {
            $order = request()->input( 'attempt' );
            $transactions->getQuery()->orders = null;
            $transactions = $transactions
            ->orderBy('updated_at', $order);
        }

        $total_count = $transactions->count();
        $total_dcn_price = $transactions->sum('amount');

        $page = max(1,intval(request('page')));
        
        $ppp = 25;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

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

        $transactions = $transactions->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $current_url = url('cms/transactions');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'search' && $key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        $are_transactions_stopped = StopTransaction::find(1)->stopped;
        $is_warning_message_shown = StopTransaction::find(1)->show_warning_text;

        $is_retry_stopped = GasPrice::find(1)->cron_new_trans > Carbon::now();
        $is_retry_paid_by_the_user_stopped = GasPrice::find(1)->cron_paid_by_user_trans > Carbon::now();

        return $this->showView('transactions', array(
            'are_transactions_stopped' => $are_transactions_stopped,
            'is_warning_message_shown' => $is_warning_message_shown,
            'is_retry_stopped' => $is_retry_stopped,
            'is_retry_paid_by_the_user_stopped' => $is_retry_paid_by_the_user_stopped,
            'transactions' => $transactions,
            'total_count' => $total_count,
            'search_address' => $this->request->input('search-address'),
            'search_status' => $this->request->input('search-status'),
            'search_tx' => $this->request->input('search-tx'),
            'search_user_id' => $this->request->input('search-user-id'),
            'search_id' => $this->request->input('search-id'),
            'search_to' => $this->request->input('search-to'),
            'search_from' => $this->request->input('search-from'),
            'search_email' =>  $this->request->input('search-email'),
            'paid_by_user' => $this->request->input('paid-by-user'),
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'total_dcn_price' => $total_dcn_price,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
            'withdrawal_conditions' => WithdrawalsCondition::find(1),
        ));
    }

    public function bump( $id ) {
        $item = DcnTransaction::find($id);

        if($item->status == 'first' && !empty($item->user) && !$item->user->is_dentist) {
            $item->user->patient_status = 'new_verified';
            $item->user->save();
        }

        $item->status = 'new';
        $item->processing = 0;
        $item->retries = 0;

        $item->save();

        $dcn_history = new DcnTransactionHistory;
        $dcn_history->transaction_id = $item->id;
        $dcn_history->admin_id = $this->user->id;
        $dcn_history->status = 'new';
        $dcn_history->history_message = 'Bumped by admin';
        $dcn_history->save();

        $this->request->session()->flash('success-message', 'Transaction bumped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function pending( $id ) {
        $item = DcnTransaction::find($id);

        $item->status = 'pending';
        $item->save();

        $dcn_history = new DcnTransactionHistory;
        $dcn_history->transaction_id = $item->id;
        $dcn_history->admin_id = $this->user->id;
        $dcn_history->status = 'pending';
        $dcn_history->history_message = 'Pending by admin';
        $dcn_history->save();

        $this->request->session()->flash('success-message', 'Transaction pending' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function stop( $id ) {
        $item = DcnTransaction::find($id);

        if($item->status == 'first') {
            $action = new UserAction;
            $action->user_id = $item->user->id;
            $action->action = 'deleted';
            $action->reason = 'Automatically - rejected first transaction';
            $action->actioned_at = Carbon::now();
            $action->save();

            $item->user->deleteActions();
            User::destroy( $item->user->id );
        }

        $item->status = 'stopped';
        $item->save();

        $dcn_history = new DcnTransactionHistory;
        $dcn_history->transaction_id = $item->id;
        $dcn_history->admin_id = $this->user->id;
        $dcn_history->status = 'stopped';
        $dcn_history->history_message = 'Stopped by admin';
        $dcn_history->save();

        $this->request->session()->flash('success-message', 'Transaction stopped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function delete( $id ) {
        $item = DcnTransaction::find($id);

        if(!empty($item)) {

            if(($this->user->id == 14 || $this->user->id == 15 || $this->user->id == 1) && ($item->status != 'completed' && $item->status != 'unconfirmed' && $item->status != 'pending')) {

                $dcn_history = new DcnTransactionHistory;
                $dcn_history->transaction_id = $item->id;
                $dcn_history->admin_id = $this->user->id;
                $dcn_history->status = 'failed';
                $dcn_history->history_message = 'Deleted by admin';
                $dcn_history->save();

                foreach($item->reference_id as $cashout_id) {
                    $cashout = DcnCashout::find($cashout_id);

                    if(!empty($cashout)) {
                        $cashout->delete();
                    }
                }

                $item->status = 'failed';
                $item->save();

                $last_transaction = DcnTransaction::where('user_id', $item->user->id)->where('id', '!=', $item->id)->orderBy('id', 'desc')->first();
                if(!empty($last_transaction)) {
                    $item->user->withdraw_at = $last_transaction->created_at;
                } else {
                    $item->user->withdraw_at = $item->user->created_at;
                }
                $item->user->save();

                $item->user->sendGridTemplate(125);

                $this->request->session()->flash('success-message', 'Transaction deleted' );
            } else {
                $this->request->session()->flash('error-message', 'You can\'t delete this transaction' );
            }
        } else {
            $this->request->session()->flash('error-message', 'This transaction is already deleted' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function massbump(  ) {
        if( Request::input('ids') ) {
            $bumptrans = DcnTransaction::whereIn('id', Request::input('ids'))->get();
            foreach ($bumptrans as $bt) {

                if($bt->status == 'first' && !empty($bt->user) && !$bt->user->is_dentist) {
                    $bt->user->patient_status = 'new_verified';
                    $bt->user->save();
                }
                
                $bt->status = 'new';
                $bt->retries = 0;
                $bt->save();

                $dcn_history = new DcnTransactionHistory;
                $dcn_history->transaction_id = $bt->id;
                $dcn_history->admin_id = $this->user->id;
                $dcn_history->status = 'new';
                $dcn_history->history_message = 'Bumped by admin';
                $dcn_history->save();
            }
        }

        $this->request->session()->flash('success-message', 'All selected transactions are bumped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function massstop(  ) {
        if( Request::input('ids') ) {
            $stoptrans = DcnTransaction::whereIn('id', Request::input('ids'))->get();
            foreach ($stoptrans as $st) {

                if($st->status == 'first') {
                    $action = new UserAction;
                    $action->user_id = $st->user->id;
                    $action->action = 'deleted';
                    $action->reason = 'Automatically - rejected first transaction';
                    $action->actioned_at = Carbon::now();
                    $action->save();

                    $st->user->deleteActions();
                    User::destroy( $st->user->id );
                }

                $st->status = 'stopped';
                $st->save();

                $dcn_history = new DcnTransactionHistory;
                $dcn_history->transaction_id = $st->id;
                $dcn_history->admin_id = $this->user->id;
                $dcn_history->status = 'stopped';
                $dcn_history->history_message = 'Stopped by admin';
                $dcn_history->save();
            }
        }

        $this->request->session()->flash('success-message', 'All selected transactions are stopped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function massPending(  ) {
        if( Request::input('ids') ) {
            $pending_trans = DcnTransaction::whereIn('id', Request::input('ids'))->get();
            foreach ($pending_trans as $pt) {

                $pt->status = 'pending';
                $pt->save();

                $dcn_history = new DcnTransactionHistory;
                $dcn_history->transaction_id = $pt->id;
                $dcn_history->admin_id = $this->user->id;
                $dcn_history->status = 'pending';
                $dcn_history->history_message = 'Pending by admin';
                $dcn_history->save();
            }
        }

        $this->request->session()->flash('success-message', 'All selected transactions are pending' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function bumpDontRetry() {

        $transactions = DcnTransaction::where('status', 'dont_retry')->get();
        foreach ($transactions as $transaction) {

            $transaction->status = 'new';
            $transaction->save();

            $dcn_history = new DcnTransactionHistory;
            $dcn_history->transaction_id = $item->id;
            $dcn_history->admin_id = $this->user->id;
            $dcn_history->status = 'new';
            $dcn_history->history_message = 'Bumped by admin from Dont Retry button';
            $dcn_history->save();
        }

        $this->request->session()->flash('success-message', '"Dont retry" transactions are bumped' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function allowWithdraw() {

        $allow = StopTransaction::find(1);
        $allow->stopped = false;
        $allow->save();

        $this->request->session()->flash('success-message', 'Withdrawals are allowed!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function disallowWithdraw() {

        $allow = StopTransaction::find(1);
        $allow->stopped = true;
        $allow->save();

        $this->request->session()->flash('success-message', 'Withdrawals are not allowed!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function removeMessage() {

        $allow = StopTransaction::find(1);
        $allow->show_warning_text = false;
        $allow->save();

        $this->request->session()->flash('success-message', 'Message is removed!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function addMessage() {

        $allow = StopTransaction::find(1);
        $allow->show_warning_text = true;
        $allow->save();

        $this->request->session()->flash('success-message', 'Messagе is added!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function withdrawalConditions() {

        $withdrawal_conditions = WithdrawalsCondition::find(1);
    
        if(!empty(request('min-amount'))) {
            $withdrawal_conditions->min_amount = request('min-amount');
        }

        if(!empty(request('min-vox-amount'))) {
            $withdrawal_conditions->min_vox_amount = request('min-vox-amount');
        }

        if(!empty(request('timerange'))) {
            $withdrawal_conditions->timerange = request('timerange');
        }

        if(!empty(request('count_pending_transactions'))) {
            $withdrawal_conditions->count_pending_transactions = request('count_pending_transactions');
        }

        $withdrawal_conditions->save();

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function scammers() {

        return $this->showView('transactions-scammers', array(
            'scammers' => TransactionScammersByDay::get(),
        ));
    }

    public function scammersChecked($id) {

        $scam = TransactionScammersByDay::find($id);

        if(!empty($scam)) {
            $scam->checked = true;
            $scam->save();
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function scammersBalance() {

        return $this->showView('transactions-scammers-balance', array(
            'scammers' => TransactionScammersByBalance::get(),
        ));
    }

    public function scammersBalanceChecked($id) {

        $scam = TransactionScammersByBalance::find($id);

        if(!empty($scam)) {
            $scam->checked = true;
            $scam->save();
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function disableRetry() {

        if($this->user->id == 14 || $this->user->id == 15 || $this->user->id == 1) {

            $allow = GasPrice::find(1);
            $allow->cron_new_trans = Carbon::now()->addYears(5);
            $allow->save();

            $this->request->session()->flash('success-message', 'Disabled!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function enableRetry() {

        if($this->user->id == 14 || $this->user->id == 15 || $this->user->id == 1) {
            $allow = GasPrice::find(1);
            $allow->cron_new_trans = Carbon::now()->addDays(-1);
            $allow->save();

            $this->request->session()->flash('success-message', 'Enabled!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function disablePaidByUserRetry() {

        if($this->user->id == 14 || $this->user->id == 15 || $this->user->id == 1) {
            $allow = GasPrice::find(1);
            $allow->cron_paid_by_user_trans = Carbon::now()->addYears(5);
            $allow->save();

            $this->request->session()->flash('success-message', 'Disabled!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function enablePaidByUserRetry() {

        if($this->user->id == 14 || $this->user->id == 15 || $this->user->id == 1) {
            $allow = GasPrice::find(1);
            $allow->cron_paid_by_user_trans = Carbon::now()->addDays(-1);
            $allow->save();

            $this->request->session()->flash('success-message', 'Enabled!' );
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function editMode() {
        session([
            'edit-mode' => true
        ]); 

        $this->request->session()->flash('success-message', 'Edit mode enabled!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function normalMode() {
        session([
            'edit-mode' => false
        ]); 

        $this->request->session()->flash('success-message', 'Normal mode enabled!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function edit($id) {
        if($this->user->id == 14 || $this->user->id == 15 || $this->user->id == 1) {
            $item = DcnTransaction::find($id);

            if(Request::isMethod('post')) {

                if((!empty(request('tx_hash')) && request('tx_hash') != $item->tx_hash) || (!empty(request('allowance_hash')) && request('allowance_hash') != $item->allowance_hash) || (!empty(request('status')) && request('status') != $item->status) || (!empty(request('message')) && request('message') != $item->message)) {

                    $dcn_history = new DcnTransactionHistory;
                    $dcn_history->transaction_id = $item->id;
                    $dcn_history->admin_id = $this->user->id;

                    if(!empty(request('status')) && request('status') != $item->status) {
                        $dcn_history->status = request('status');
                    }

                    if(!empty(request('tx_hash')) && request('tx_hash') != $item->tx_hash) {
                        $dcn_history->tx_hash = request('tx_hash');
                    }

                    if(!empty(request('allowance_hash')) && request('allowance_hash') != $item->allowance_hash) {
                        $dcn_history->allowance_hash = request('allowance_hash');
                    }

                    if(!empty(request('message')) && request('message') != $item->message) {
                        $dcn_history->message = request('message');
                    }

                    $dcn_history->history_message = 'Edited by admin';
                    $dcn_history->save();
                }

                $item->tx_hash = request('tx_hash');
                $item->message = request('message');
                if($item->is_paid_by_the_user) {
                    $item->allowance_hash = request('allowance_hash');
                }
                $item->status = request('status');

                if(request('status') == 'new') {
                    $item->processing = 0;
                }
                $item->save();


                $this->request->session()->flash('success-message', 'Saved!' );
                return redirect('cms/transactions');
            }

            return $this->showView('transactions-edit', array(
                'item' => $item,
            ));
        }
    }

}
