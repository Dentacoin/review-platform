@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
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
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-user-id') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search-address" value="{{ $search_address }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-address') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search-tx" value="{{ $search_tx }}" placeholder="{{ trans('admin.page.'.$current_page.'.title-filter-tx') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="{{ trans('admin.page.'.$current_page.'.title-filter-submit') }}">
                        </div>
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
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'transactions',
						'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-transactions-user'),
                            'amount'              => array(),
                            'address'              => array(),
                            'tx_hash'              => array('template' => 'admin.parts.table-transactions-hash'),
                            'status'              => array(),
                            'type'              => array(),
                            'message'              => array(),
                            'retries'              => array(),
                            'updated_at'        => array('format' => 'datetime'),
						],
                        'table_data' => $transactions,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection