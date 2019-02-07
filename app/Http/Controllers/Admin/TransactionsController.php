<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\DcnTransaction;

use Carbon\Carbon;

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
        
        $transactions = $transactions->take(500)->get();

        return $this->showView('transactions', array(
            'transactions' => $transactions,
            'search_address' => $this->request->input('search-address'),
            'search_tx' => $this->request->input('search-tx'),
            'search_user_id' => $this->request->input('search-user-id'),
        ));
    }

    public function bump( $id ) {
        $item = DcnTransaction::find($id);

        $item->status = 'new';
        $item->retries = 0;

        $item->save();

        $this->request->session()->flash('success-message', 'Transaction bumped' );
        return redirect('cms/transactions');

    }


}
