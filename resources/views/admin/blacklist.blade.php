@extends('admin')

@section('content')

<h1 class="page-header">Blacklisted names & emails</h1>
<!-- end page-header -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Blacklist</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'blacklist',
						'table_fields' => [
                            'pattern'               => array(),
                            'field'                 => array(),
                            'comments'              => array(),
                            'blocked'               => array('template' => 'admin.parts.table-blacklist-people'),
							'delete'			    => array('format' => 'delete'),
						],
                        'table_data' => $items,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
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
                <h4 class="panel-title">Add new item</h4>
            </div>
            <div class="panel-body">
                <div class="panel-body">
                    @include('admin.blacklist-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection