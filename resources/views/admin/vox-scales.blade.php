@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.scales.title') }}
    <a href="{{ url('cms/'.$current_page.'/'.$current_subpage.'/add') }}" class="btn btn-success pull-right">
        {{ trans('admin.page.'.$current_page.'.scales.add') }}
    </a>
</h1>
<!-- end page-header -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.scales.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'voxs',
						'table_fields' => [
                            //'id'                => array(),
                            'title'              => array(),
                            'count'              => array('template' => 'admin.parts.table-vox-scales'),
							'update'			=> array('format' => 'update'),
							'delete'			=> array('format' => 'delete'),
						],
                        'table_subpage' => 'scales',
                        'table_data' => $scales,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection