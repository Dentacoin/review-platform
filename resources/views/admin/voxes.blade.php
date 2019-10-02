@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}
    
    <a class="btn btn-primary pull-right" id="table-sort" href="javascript:;" alternate="Done">Sort Surveys</a>
</h1>
<!-- end page-header -->

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
						'table_id' => 'voxs',
						'table_fields' => [
                            'sort_order'        => array('label' => 'Sort'),
                            'id'                => array(),
                            'title'             => array(),
                            'category'          => array('template' => 'admin.parts.table-voxes-category'),
                            'count'             => array('template' => 'admin.parts.table-voxes-count'),
                            'reward'            => array('template' => 'admin.parts.table-voxs-reward'),
                            'duration'          => array('template' => 'admin.parts.table-voxs-duration'),
                            'respondents'       => array('template' => 'admin.parts.table-voxs-respondents'),
                            'featured'          => array('template' => 'admin.parts.table-voxes-featured'),
                            'type'              => array('template' => 'admin.parts.table-voxes-type'),
                            'stats'             => array('template' => 'admin.parts.table-voxes-stats'),
                            'stats_featured'    => array('template' => 'admin.parts.table-voxes-stats-featured'),
                            'date'              => array('template' => 'admin.parts.table-voxes-date'),
                            'launched_date'     => array('template' => 'admin.parts.table-voxes-launched-date'),
                            'updated_date'      => array('template' => 'admin.parts.table-voxes-updated-date'),
							'update'			=> array('format' => 'update'),
							'delete'			=> array('format' => 'delete'),
						],
                        'table_data' => $voxes,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection