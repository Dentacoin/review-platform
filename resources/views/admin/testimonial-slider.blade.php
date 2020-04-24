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
						'table_id' => 'testimonials',
						'table_fields' => [
							'image'				=> array('label' => 'Image', 'template' => 'admin.parts.table-testimonials-image'),
							'name'				=> array('label' => 'Name'),
							'job'				=> array('label' => 'Job'),
							'description'		=> array('label' => 'Description'),
							'edit'				=> array('label' => 'Edit', 'format' => 'update'),
							'delete'			=> array('label' => 'Delete', 'format' => 'delete'),
						],
                        'table_data' => $testimonials,
						'table_pagination' => false,
                        'pagination_link' => array()
					])
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Add testimonial</h4>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" id="admin-add" method="post" enctype= multipart/form-data action="{{ url('cms/testimonial-slider/add') }}">
                	{!! csrf_field() !!}
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Name</label>
                        <div class="col-md-4">
                            {{ Form::text('name', '', array('class' => 'form-control')) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Image</label>
                        <div class="col-md-4">
                            <input type="file" name="image" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Job</label>
                        <div class="col-md-4">
                            {{ Form::text('job', '', array('class' => 'form-control')) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Description</label>
                        <div class="col-md-4">
                            {{ Form::textarea('description', '', array('class' => 'form-control')) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-2"></div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-block btn-success">Add</button>
                        </div>                        
                    </div>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>



@endsection