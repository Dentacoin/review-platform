@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.'.$current_subpage.'.title') }}</h1>
<!-- end page-header -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.'.$current_subpage.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'voxideas',
						'table_fields' => [
                            'created_at'              => array('format' => 'datetime'),
                            'user'              => array('format' => 'user'),
                            'idea'              => array(),
							'delete'			=> array('format' => 'delete'),
						],
                        'table_data' => $ideas,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection