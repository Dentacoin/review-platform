@extends('admin')

@section('content')
    
    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
        <div>
            <a href="{{ $is_warning_message_shown ? url('cms/transactions/remove-message') : url('cms/transactions/add-message') }}" class="btn btn-info pull-right" style="margin-left: 10px;">{{ $is_warning_message_shown ? 'Hide warning message on DV homepage' : 'Show warning message on DV homepage' }}</a>
            <a href="{{ $are_transactions_stopped ? url('cms/transactions/start') : url('cms/transactions/stop') }}" class="btn btn-{{ $are_transactions_stopped ? 'success' : 'danger' }} pull-right" style="margin-left: 10px;">{{ $are_transactions_stopped ? 'Allow users to withdraw' : 'Disallow users to withdraw' }}</a>

            @if(App\Models\DcnTransaction::where('status', 'dont_retry')->count())
                <a href="{{ url('cms/transactions/bump-dont-retry') }}" class="btn btn-warning pull-right">Bump all transactions with status 'DONT RETRY'</a>
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
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-id" value="{{ $search_id }}" placeholder="Transaction ID" autocomplete="off">
                            </div>  
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User email" autocomplete="off">
                            </div>
                            <div class="col-md-1">
                                <select class="form-control" name="search-status">
                                    <option value="">Status</option>
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
                            <div class="col-md-1">
                                <input type="text" class="form-control datepicker" name="search-from" value="{{ $search_from }}" placeholder="Search from" autocomplete="off">
                            </div>
                            <div class="col-md-1">
                                <input type="text" class="form-control datepicker" name="search-to" value="{{ $search_to }}" placeholder="Search to" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <label for="paid-by-user" style="display: flex;align-items: center;margin-top: 7px;font-weight: normal;">
                                    <input id="paid-by-user" type="checkbox" name="paid-by-user" value="1" {!! !empty($paid_by_user) ? 'checked="checked"' : '' !!} style="margin-top: 0px;margin-right: 4px;" />
                                    Paid by user
                                </label>
                            </div>
                        </div>
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <input type="submit" class="btn btn-block btn-primary btn-block" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
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
            						'table_fields' => [
                                        'checkboxes' => array('format' => 'checkboxes'),
                                        'id'                => array('template' => 'admin.parts.table-transactions-id'),
                                        'created_at'        => array('format' => 'datetime','order' => true, 'orderKey' => 'created','label' => 'Date'),
                                        'user'              => array('template' => 'admin.parts.table-transactions-user'),
                                        'email'             => array('template' => 'admin.parts.table-transactions-email'),
                                        'user_status'       => array('template' => 'admin.parts.table-users-status'),
                                        'amount'            => array(),
                                        'address'           => array(),
                                        'tx_hash'           => array('template' => 'admin.parts.table-transactions-hash'),
                                        'status'            => array('template' => 'admin.parts.table-transactions-status'),
                                        'type'              => array(),
                                        'nonce'              => array(),
                                        'message'           => array(),
                                        'retries'           => array(),
                                        'sended_at'        => array('format' => 'datetime', 'order' => true, 'orderKey' => 'attempt','label' => 'Sended at'),
                                        'bump'              =>array('template' => 'admin.parts.table-transactions-bump'),
            						],
                                    'table_data' => $transactions,
            						'table_pagination' => false,
                                    'pagination_link' => array()
            					])
                            </div>
                            <div style="display: flex">
                                <button type="submit" name="mass-bump" id="mass-bump" class="btn btn-primary" style="flex: 1">Bump transactions</button>
                                <button type="submit" name="mass-stop" id="mass-stop" class="btn btn-danger" style="flex: 1">Stop transactions</button>
                                <button type="submit" name="mass-pending" id="mass-pending" class="btn btn-warning" style="flex: 1">"Pending" transactions</button>
                            </div>
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
                    <form class="form-horizontal" method="post" action="{{ url('cms/transactions/conditions') }}">
                        {!! csrf_field() !!}
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
                            <label class="col-md-2">Server Pending Transactions Count</label>
                            <div class="col-md-4">
                                <input class="form-control" type="number" name="count_pending_transactions" value="{{ $withdrawal_conditions->count_pending_transactions }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-sm btn-success btn-block">Submit</button>
                            </div>
                        </div>
                    </form>
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

    </style>

@endsection