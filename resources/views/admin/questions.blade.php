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
                <h4 class="panel-title">{{ trans('admin.page.'.$current_page.'.title') }}</h4>
            </div>
            <div class="panel-body">
        		<div class="panel-body">
					@include('admin.parts.table', [
						'table_id' => 'admins',
						'table_fields' => [
							//'id'				=> array(),
							'order'			    => array(),
                            'question'          => array(),
							'update'			=> array('format' => 'update'),
							'delete'			=> array('format' => 'delete'),
						],
                        'table_data' => $questions,
						'table_pagination' => false,
                        'pagination_link' => array()
					])

                    <div class="form-group">
                        <label class="col-md-10 control-label"></label>
                        <div class="col-md-1">
                            <a href="{{ url('cms/'.$current_page.'/add') }}" class="btn btn-sm btn-success">{{ trans('admin.page.'.$current_page.'.new') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection