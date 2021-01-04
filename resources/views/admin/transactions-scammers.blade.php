@extends('admin')

@section('content')
    
    <div class="flex" style="justify-content: space-between;">
        <h1 class="page-header">Users with transactions below 7 days after 18-08-2020</h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Users with transactions below 7 days after 18-08-2020</h4>
                </div>
                <div class="panel-body">
            		<div class="panel-body">
                        <div class="table-responsive">
        					@include('admin.parts.table', [
        						'table_id' => 'transactions',
        						'table_fields' => [
                                    'user'              => array('template' => 'admin.parts.table-transactions-user'),
                                    'user_status'       => array('template' => 'admin.parts.table-users-status'),
        						],
                                'table_data' => $users,
        						'table_pagination' => false,
                                'pagination_link' => array()
        					])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection