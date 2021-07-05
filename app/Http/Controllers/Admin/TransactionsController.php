<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\TransactionScammersByBalance;
use App\Models\TransactionScammersByDay;
use App\Models\DcnTransactionHistory;
use App\Models\WithdrawalsCondition;
use App\Models\StopTransaction;
use App\Models\DcnTransaction;
use App\Models\UserHistory;
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

class TransactionsController extends AdminController {

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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
        if(!empty($this->request->input('search-user-status'))) {
            $status = $this->request->input('search-user-status');
            $transactions = $transactions->whereHas('user', function ($query) use ($status) {

                if( $status=='approved' ) {
                    $query->where(function ($subquery) use ($status) {
                        $subquery->where('is_dentist', 1)
                        ->where('status', $status);
                    });
                } else {
                    $query->where('status', $status)
                    ->orWhere('patient_status', $status);
                }
            });
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

        $table_fields = [
            'checkboxes' => array('format' => 'checkboxes'),
            'id'                => array('template' => 'admin.parts.table-transactions-id'),
            'created_at'        => array('format' => 'datetime','order' => true, 'orderKey' => 'created','label' => 'Date'),
            'user'              => array('template' => 'admin.parts.table-transactions-user'),
            'email'             => array('template' => 'admin.parts.table-transactions-email'),
        ];

        if(request('search-status') == 'first') {
            $table_fields['user_image'] = array('template' => 'admin.parts.table-users-image', 'label' => 'Image');
            $table_fields['user_website'] = array('template' => 'admin.parts.table-users-website', 'label' => 'Website');
        }

        $table_fields['user_status'] = array('template' => 'admin.parts.table-users-status');
        $table_fields['amount'] = array();
        $table_fields['address'] = array();
        $table_fields['tx_hash'] = array('template' => 'admin.parts.table-transactions-hash');
        $table_fields['status'] = array('template' => 'admin.parts.table-transactions-status');
        $table_fields['type'] = array();
        $table_fields['nonce'] = array();
        $table_fields['message'] = array();
        $table_fields['retries'] = array();
        $table_fields['sended_at'] = array('format' => 'datetime', 'order' => true, 'orderKey' => 'attempt','label' => 'Sended at');
        $table_fields['bump'] = array('template' => 'admin.parts.table-transactions-bump', 'label' => "Actions");

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
            'search_user_status' => $this->request->input('search-user-status'),
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'total_dcn_price' => $total_dcn_price,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
            'withdrawal_conditions' => WithdrawalsCondition::find(1),
            'table_fields' => $table_fields
        ));
    }

    public function bump( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = DcnTransaction::find($id);

        if($item->status == 'first' && !empty($item->user) && !$item->user->is_dentist) {

            $user_history = new UserHistory;
            $user_history->user_id = $item->user->id;
            $user_history->patient_status = $item->user->patient_status;
            $user_history->save();

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = DcnTransaction::find($id);

        if(!empty($item)) {

            if(!in_array($item->status, ['completed', 'unconfirmed', 'pending'])) {

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if( Request::input('ids') ) {
            $bumptrans = DcnTransaction::whereIn('id', Request::input('ids'))->get();
            foreach ($bumptrans as $bt) {

                if($bt->status == 'first' && !empty($bt->user) && !$bt->user->is_dentist) {

                    $user_history = new UserHistory;
                    $user_history->user_id = $bt->user->id;
                    $user_history->patient_status = $bt->user->patient_status;
                    $user_history->save();

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = StopTransaction::find(1);
        $allow->stopped = false;
        $allow->save();

        $this->request->session()->flash('success-message', 'Withdrawals are allowed!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function disallowWithdraw() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = StopTransaction::find(1);
        $allow->stopped = true;
        $allow->save();

        $this->request->session()->flash('success-message', 'Withdrawals are not allowed!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function removeMessage() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = StopTransaction::find(1);
        $allow->show_warning_text = false;
        $allow->save();

        $this->request->session()->flash('success-message', 'Message is removed!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function addMessage() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = StopTransaction::find(1);
        $allow->show_warning_text = true;
        $allow->save();

        $this->request->session()->flash('success-message', 'Messagе is added!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function withdrawalConditions() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

        if(!empty(request('server_pending_trans_check'))) {
            $withdrawal_conditions->server_pending_trans_check = true;
        } else {
            $withdrawal_conditions->server_pending_trans_check = false;
        }

        if(!empty(request('count_pending_transactions'))) {
            $withdrawal_conditions->count_pending_transactions = request('count_pending_transactions');
        }

        if(!empty(request('connected_nodes_check'))) {
            $withdrawal_conditions->connected_nodes_check = true;
        } else {
            $withdrawal_conditions->connected_nodes_check = false;
        }

        if(!empty(request('daily_max_amount')) || request('daily_max_amount') === 0) {
            $withdrawal_conditions->daily_max_amount = request('daily_max_amount');
        } else {
            $withdrawal_conditions->daily_max_amount = null;
        }

        $withdrawal_conditions->save();

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function scammers() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        return $this->showView('transactions-scammers', array(
            'scammers' => TransactionScammersByDay::get(),
        ));
    }

    public function scammersChecked($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $scam = TransactionScammersByDay::find($id);

        if(!empty($scam)) {
            $scam->checked = true;
            $scam->save();
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function scammersBalance() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        return $this->showView('transactions-scammers-balance', array(
            'scammers' => TransactionScammersByBalance::get(),
        ));
    }

    public function scammersBalanceChecked($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $scam = TransactionScammersByBalance::find($id);

        if(!empty($scam)) {
            $scam->checked = true;
            $scam->save();
        }

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function disableRetry() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = GasPrice::find(1);
        $allow->cron_new_trans = Carbon::now()->addYears(5);
        $allow->save();

        $this->request->session()->flash('success-message', 'Disabled!' );

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function enableRetry() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = GasPrice::find(1);
        $allow->cron_new_trans = Carbon::now()->addDays(-1);
        $allow->save();

        $this->request->session()->flash('success-message', 'Enabled!' );

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function disablePaidByUserRetry() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = GasPrice::find(1);
        $allow->cron_paid_by_user_trans = Carbon::now()->addYears(5);
        $allow->save();

        $this->request->session()->flash('success-message', 'Disabled!' );

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function enablePaidByUserRetry() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $allow = GasPrice::find(1);
        $allow->cron_paid_by_user_trans = Carbon::now()->addDays(-1);
        $allow->save();

        $this->request->session()->flash('success-message', 'Enabled!' );

        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function editMode() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        session([
            'edit-mode' => true
        ]); 

        $this->request->session()->flash('success-message', 'Edit mode enabled!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function normalMode() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        session([
            'edit-mode' => false
        ]); 

        $this->request->session()->flash('success-message', 'Normal mode enabled!' );
        return redirect(!empty(Request::server('HTTP_REFERER')) ? Request::server('HTTP_REFERER') : 'cms/transactions');
    }

    public function edit($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

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

    public function checkPendingTransactions() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_URL => 'https://payment-server-info.dentacoin.com/get-pending-transactions',
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
         
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        $pending_transactions = !empty($resp) ? $resp->success : 'Error';

        return Response::json( ['data' => $pending_transactions] );
    }

    public function checkConnectedNodes() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_URL => 'https://payment-server-info.dentacoin.com/get-connected-nodes',
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
         
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        $connected_nodes = !empty($response) ? $response->success : 'Error';

        return Response::json( ['data' => $connected_nodes] );
    }

}
