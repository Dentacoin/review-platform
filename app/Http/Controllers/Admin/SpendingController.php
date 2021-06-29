<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\AdminController;

use App\Models\DcnTransaction;

use Carbon\Carbon;

use Auth;
use DB;

class SpendingController extends AdminController {

    public function list( ) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $groups = [
            'day', 
            'week', 
            'month'
        ];
        $sql_groups = [
            'day' => 'DATE_FORMAT(`created_at`, "%d.%m.%Y") AS `period`', 
            'week' => 'DATE_FORMAT(`created_at`, "%v.%Y") AS `period`', 
            'month' => 'DATE_FORMAT(`created_at`, "%m.%Y") AS `period`'
        ];
        $search_from = $this->request->input('search_from');
        if(!empty($search_from)) {
            $search_from = new Carbon($this->request->input('search_from'));
        } else {
            $search_from = Carbon::now()->addDays(-31);
        }
        $search_to = $this->request->input('search_to');
        if(!empty($search_to)) {
            $search_to = new Carbon($this->request->input('search_to'));
        } else {
            $search_to = Carbon::now();
        }
        $search_group = $this->request->input('search_group', 'day');


        $firstday = $search_from->format('d.m.Y');
        $lastday = $search_to->format('d.m.Y');

        $weeks = DB::select("
            SELECT 
                ".$sql_groups[$search_group].",
                `type`,
                SUM(`amount`) AS `total`
            FROM  `dcn_transactions` 
            WHERE
                `created_at` > '".$search_from->format('Y.m.d')."' AND
                `created_at` < '".$search_to->addDays(1)->format('Y.m.d')."' AND
                `status` = 'completed'
            GROUP BY `period`, `type` 
            ORDER BY `id` DESC
        ");

        $totals = [];
        $types = [];
        $transactions = [];
        foreach ($weeks as $week) {
            if(!isset($transactions[$week->period])) {
                $transactions[$week->period] = [];
            }
            if(!isset($totals[$week->type])) {
                $totals[$week->type] = 0;
            }
            $types[$week->type] = $week->type;
            $transactions[$week->period][$week->type] = $week->total;
            $totals[$week->type] += $week->total;
        }

        //dd($transactions);
        return $this->showView('spending', array(
            'totals' => $totals,
            'types' => $types,
            'transactions' => $transactions,
            'search_from' => $firstday,
            'search_to' => $lastday,
            'search_group' => $search_group,
            'groups' => $groups,
        ));

    }

}