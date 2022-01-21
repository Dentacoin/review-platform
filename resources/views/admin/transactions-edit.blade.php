@extends('admin')

@section('content')

<h1 class="page-header">Transaction Edit</h1>
<!-- end page-header -->

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Transaction Edit</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{{ url('cms/transactions/edit/'.$item->id) }}">

                    <div class="form-group">
                        <label class="col-md-2 control-label">ID</label>
                        <div class="col-md-10">
                            {{ Form::text('id', $item->id, array('class' => 'form-control', 'disabled'=>'disabled')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">User</label>
                        <div class="col-md-10" style="margin-top: 7px;">
                            <a href="{{ url('/cms/users/users/edit/'.$item->user_id) }}">
                                {{ !empty($item->user) ? $item->user->name : ''  }}
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Amount</label>
                        <div class="col-md-10">
                            {{ Form::text('amount', $item->amount, array('class' => 'form-control', 'disabled'=>'disabled')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Address</label>
                        <div class="col-md-10">
                            {{ Form::text('address', $item->address, array('class' => 'form-control')) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Tx hash</label>
                        <div class="col-md-10">
                            {{ Form::textarea('tx_hash', $item->tx_hash, array('class' => 'form-control', 'style' => 'max-height: 60px !important;')) }}
                        </div>
                    </div>

                    @if($item->is_paid_by_the_user)
                    	<div class="form-group">
                            <label class="col-md-2 control-label">Allowance hash</label>
                            <div class="col-md-10">
                                {{ Form::textarea('allowance_hash', $item->allowance_hash, array('class' => 'form-control', 'style' => 'max-height: 60px !important;')) }}
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="col-md-2 control-label">Status</label>
                        <div class="col-md-10">
                            {{ Form::select('status', config('transaction-statuses'), $item->status , array('class' => 'form-control')) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Message</label>
                        <div class="col-md-10">
                            {{ Form::textarea('message', null, array('class' => 'form-control', 'style' => 'max-height: 60px !important;', 'placeholder' => 'Write here any comment about the changes if you want..')) }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label"></label>
                        <div class="col-md-10">
                            *Hint: If you want to send the transaction to the payment server, change transaction's status to 'New'
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-11 control-label"></label>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-block btn-sm btn-success form-control">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>


@endsection