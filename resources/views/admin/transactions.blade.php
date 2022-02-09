@extends('admin')

@section('content')
    
    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
        <div>
            <a href="{{ $is_warning_message_shown ? url('cms/transactions/remove-message') : url('cms/transactions/add-message') }}" class="btn btn-info pull-right" style="margin-left: 10px;">{{ $is_warning_message_shown ? 'Hide warning message on DV homepage' : 'Show warning message on DV homepage' }}</a>
            <a href="{{ $are_transactions_stopped ? url('cms/transactions/start') : url('cms/transactions/stop') }}" class="btn btn-{{ $are_transactions_stopped ? 'success' : 'danger' }} pull-right" style="margin-left: 10px;">{{ $are_transactions_stopped ? 'Allow users to withdraw' : 'Disallow users to withdraw' }}</a>
            <a href="{{ $are_transactions_hash_check_stopped ? url('cms/transactions/start-hash-check') : url('cms/transactions/stop-hash-check') }}" class="btn btn-{{ $are_transactions_hash_check_stopped ? 'success' : 'info' }} pull-right" style="margin-left: 10px;">{{ $are_transactions_hash_check_stopped ? 'Enable hash check' : 'Disable hash check' }}</a>

            @if($admin->role!='support')
                @if(App\Models\DcnTransaction::where('status', 'dont_retry')->count())
                    <a href="{{ url('cms/transactions/bump-dont-retry') }}" class="btn btn-warning pull-right" style="margin-left: 10px;">Bump trans with status 'DONT RETRY'</a>
                @endif
            @endif

            @if(in_array($admin->role, ['super_admin', 'support']))
                <a href="{{ $is_retry_stopped ? url('cms/transactions/enable-retry') : url('cms/transactions/disable-retry') }}" class="btn btn-primary pull-right" style="margin-left: 10px;">{{ $is_retry_stopped ? 'Enable' : 'Disable' }} sending trans to PS</a>
            @endif
        </div>
    </div>
    <div style="margin-bottom: 10px;"><a href="https://docs.google.com/spreadsheets/d/1O3hId4TS3m_ZA-1-c77Rl6_grqyb2TIrpKkqTtmmgx8/edit#gid=0" target="_blank">Statuses info</a></div>

    @if($are_transactions_stopped)
        <div>
            <label class="alert alert-danger">Users can't withdraw. Allow them by click on 'Allow users to withdraw' button.</label>
        </div>
    @endif
    @if($is_warning_message_shown)
        <div>
            <label class="alert alert-warning">Warning message on DV homepage is shown. Hide it from the button "Hide warning message on DV homepage".</label>
        </div>
    @endif
    @if($is_retry_stopped)
        <div>
            <label class="alert alert-warning">Sending transactions to the PS is disabled.</label>
        </div>
    @endif
    @if($manually_check_transactions && $admin->role=='super_admin')
        <div>
            <label class="alert alert-warning">Тhere are transactions that need to be checked manually.</label>
        </div>
    @endif

    @if(App\Models\DcnTransaction::where('status', 'dont_retry')->count())
        <!-- <div>
            <label class="alert alert-warning">After refill, please click on "Bump all transactions with status 'DONT RETRY'" button</label>
        </div> -->
    @endif

    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-filter') }} </h4>
                </div>
                <div class="panel-body">
                    <form method="get" action="{{ url('cms/'.$current_page) }}" >
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-user-id') }}">
                            </div>  
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User email" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-user-status">
                                    <option value="">User Status</option>
                                    @foreach(config('patient-statuses') as $key => $user_status)
                                        <option value="{{ $key }}" {!! $key==$search_user_status ? 'selected="selected"' : '' !!}>Patient: {{ $user_status }}</option>
                                    @endforeach
                                    <option value="dentists_clinics" {!! 'dentists_clinics'==$search_user_status ? 'selected="selected"' : '' !!}>Dentists and Clinics All</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-id" value="{{ $search_id }}" placeholder="Transaction ID" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-status">
                                    <option value="">Transaction Status</option>
                                    @foreach(config('transaction-statuses') as $key => $status)
                                        <option value="{{ $key }}" {!! $key==$search_status ? 'selected="selected"' : '' !!}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-address" value="{{ $search_address }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-address') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-tx" value="{{ $search_tx }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-tx') }}">
                            </div>
                        </div>
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <div class="col-md-1">
                                <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="Search from" autocomplete="off">
                            </div>
                            <div class="col-md-1">
                                <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="Search to" autocomplete="off">
                            </div>
                            <div class="col-md-1">
                                <label for="paid-by-user" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                    <input id="paid-by-user" type="checkbox" name="paid-by-user" value="1" {!! !empty($paid_by_user) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                    Paid by user
                                </label>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-layer">
                                    <option value="">Transaction Layer Type</option>
                                    <option value="l1" {!! 'l1'==$search_layer_type ? 'selected="selected"' : '' !!}>Ethereum</option>
                                    <option value="l2" {!! 'l2'==$search_layer_type ? 'selected="selected"' : '' !!}>Optimistic Ethereum</option>
                                </select>
                            </div>
                            @if($admin->role=='super_admin')
                                <div class="col-md-2">
                                    <label for="manual_check_admin" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                        <input id="manual_check_admin" type="checkbox" name="manual_check_admin" value="1" {!! !empty($manual_check_admin) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                        For manual check
                                    </label>
                                </div>
                            @endif
                        </div>
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <input type="submit" class="btn btn-block btn-primary btn-block" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
                </div>
                <div class="panel-body">
                    <div style="display: flex;justify-content: space-between;">
                        <div>
                            Transactions count: {{ $total_count }} <br/>
                            Sum: {{ $total_dcn_price }} DCN
                        </div>
                        <div>
                            <a href="{{ url('cms/transactions/scammers') }}" class="btn btn-{{ App\Models\TransactionScammersByDay::where('checked', '!=', 1)->count() ? 'danger' : 'info' }} pull-right" style="margin-left: 10px;">Scammers by days ({{ App\Models\TransactionScammersByDay::where('checked', '!=', 1)->count() }})</a>
                            <a href="{{ url('cms/transactions/scammers-balance') }}" class="btn btn-{{ App\Models\TransactionScammersByBalance::where('checked', '!=', 1)->count() ? 'danger' : 'info' }} pull-right" style="margin-left: 10px;">Scammers by balance ({{ App\Models\TransactionScammersByBalance::where('checked', '!=', 1)->count() }})</a>
                        </div>
                    </div>
            		<div class="panel-body">
                        <form method="post" action="{{ url('cms/transactions') }}" original-action="{{ url('cms/transactions') }}">
                            {!! csrf_field() !!}
                            <div class="table-responsive">
            					@include('admin.parts.table', [
            						'table_id' => 'transactions',
            						'table_fields' => $table_fields,
                                    'table_data' => $transactions,
            						'table_pagination' => false,
                                    'pagination_link' => array()
            					])
                            </div>
                            @if($admin->role!='support')
                                <div style="display: flex">
                                    <button type="submit" name="mass-bump" id="mass-bump" class="btn btn-primary" style="flex: 1">Bump/Approve transactions</button>
                                    <button type="submit" name="mass-stop" id="mass-stop" class="btn btn-danger" style="flex: 1">Stop/Reject transactions</button>
                                    <button type="submit" name="mass-pending" id="mass-pending" class="btn btn-warning" style="flex: 1">"Pending" transactions</button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/transactions/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/transactions/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/transactions/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/transactions/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/transactions/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif

    <br/>
    <br/>
    <br/>
    <br/>
    
    Current gas price: <b>{{ App\Models\GasPrice::find(1)->gas_price }}</b> <br/>
    Max gas price: <b>{{ App\Models\GasPrice::find(1)->max_gas_price }}</b> <br/>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Withdrawals conditions</h4>
                </div>
                <div class="panel-body">
                    @if($admin->role=='super_admin')
                        <form class="form-horizontal" method="post" action="{{ url('cms/transactions/conditions') }}">
                        {!! csrf_field() !!}
                    @else
                        <div class="form-horizontal">
                    @endif
                        <div class="form-group">
                            <label class="col-md-2">Min amount</label>
                            <div class="col-md-4">
                                <input class="form-control" type="number" name="min-amount" value="{{ $withdrawal_conditions->min_amount }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2">Min VOX amount</label>
                            <div class="col-md-4">
                                <input class="form-control" type="number" name="min-vox-amount" value="{{ $withdrawal_conditions->min_vox_amount }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2">Timerange (in days)</label>
                            <div class="col-md-4">
                                <input class="form-control" type="number" name="timerange" value="{{ $withdrawal_conditions->timerange }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="server_pending_trans_check" class="col-md-2 control-label" style="text-align: left;">Check for server pending transactions</label>
                            <div class="col-md-10">
                                <input type="checkbox" name="server_pending_trans_check" value="1" id="server_pending_trans_check" {!! !empty($withdrawal_conditions->server_pending_trans_check) ? 'checked="checked"' : '' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2">Server Pending Transactions Count</label>
                            <div class="col-md-4">
                                <input class="form-control" type="number" id="count_pending_transactions" name="count_pending_transactions" value="{{ $withdrawal_conditions->count_pending_transactions }}" />
                            </div>
                            <a href="javascript:;" class="col-md-2 btn btn-primary" id="check-cur-pending-tx">check pending tx count</a>
                            <div class="col-md-4" id="cur-pending-tx">
                                
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="connected_nodes_check" class="col-md-2 control-label" style="text-align: left;">Check for connected nodes</label>
                            <div class="col-md-4">
                                <input type="checkbox" name="connected_nodes_check" value="1" id="connected_nodes_check" {!! !empty($withdrawal_conditions->connected_nodes_check) ? 'checked="checked"' : '' !!}>
                            </div>
                            <a href="javascript:;" class="col-md-2 btn btn-primary" id="check-cur-nodes">check connected nodes</a>
                            <div class="col-md-4" id="cur-nodes">
                                
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2">Transactions daily max amount ( in $ )</label>
                            <div class="col-md-4">
                                <input class="form-control" type="number" name="daily_max_amount" value="{{ $withdrawal_conditions->daily_max_amount }}" />
                            </div>
                        </div>
                        <p>Todays transactions amount {{ round(App\Models\DcnTransaction::where('sended_at', '>=', date('Y-m-d').' 00:00:00')->whereIn('status', ['unconfirmed', 'completed'])->where(function($query) {
                            $query->whereNull('is_paid_by_the_user')
                            ->orWhere('is_paid_by_the_user', 0);
                        })->get()->sum('amount') * @file_get_contents('/tmp/dcn_original_price')) }}$</p>
                        
                        @if($admin->role=='super_admin')
                            <div class="form-group">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-sm btn-success btn-block">Submit</button>
                                </div>
                            </div>
                        @endif
                    
                    @if($admin->role=='super_admin')
                        </form>
                    @else
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="suspiciousUserModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Make user suspicious</h4>
                </div>
                <div class="modal-body">
                    <form class="suspicious-form" action="{{ url('cms/transactions/user-suspicious/') }}" original-action="{{ url('cms/transactions/user-suspicious/') }}" method="post">    
                        <textarea class="form-control" name="suspicious-reason" placeholder="Reason why this user is suspicious"></textarea>
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Submit</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <style type="text/css">
        .trans-history-wrapper .trans-history {
            display: none;
            position: absolute;
            border: 1px solid black;
            padding: 10px;
            border-radius: 5px;
            background: white;
        }

        .trans-history-wrapper:hover .trans-history {
            display: block;
        }

        .trans-history-wrapper .trans-history div {
            border-bottom: 1px solid black;
            margin-bottom: 3px;
            padding-bottom: 4px;
        }

        .trans-history-wrapper .trans-history div:last-child {
            border-bottom: none;
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

        .user-info-wrapper .user-info-tooltip {
            display: none;
            position: absolute;
            border: 1px solid black;
            padding: 10px;
            border-radius: 5px;
            background: white;
            z-index: 1000;
        }

        .user-info-wrapper .img-wrap {
            padding: 5px;
            padding-left: 0px;
        }

        .user-info-wrapper:hover .user-info-tooltip {
            display: block;
        }

    </style>

@endsection