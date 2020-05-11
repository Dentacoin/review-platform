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
        <form class="form-horizontal" id="admin-add" method="post" enctype= multipart/form-data action="{{ url('cms/testimonial-slider/add') }}">
            {!! csrf_field() !!}
            <!-- begin panel -->

             <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-1">
                <div class="panel-heading p-0">
                    <div class="panel-heading-btn m-r-10 m-t-10">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-expand" data-original-title="" title=""><i class="fa fa-expand"></i></a>
                    </div>
                    <!-- begin nav-tabs -->
                    <div class="tab-overflow overflow-right">
                        <ul class="nav nav-tabs nav-tabs-inverse">
                            <li class="prev-button"><a href="javascript:;" data-click="prev-tab" class="text-success"><i class="fa fa-arrow-left"></i></a></li>
                            @foreach($langs as $code => $lang_info)
                                <li class="{{ $loop->first ? 'active' : '' }}"><a href="#nav-tab-{{ $code }}" data-toggle="tab" aria-expanded="false">{{ $lang_info['name'] }}</a></li>
                            @endforeach

                            <li class="next-button"><a href="javascript:;" data-click="next-tab" class="text-success"><i class="fa fa-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    @foreach($langs as $code => $lang_info)
                        <div class="tab-pane fade{{ $loop->first ? ' active in' : '' }}" id="nav-tab-{{ $code }}">
                            <div class="form-group">
                                <label class="col-md-2 control-label" style="max-width: 200px;">Name</label>
                                <div class="col-md-10">
                                    {{ Form::text('name-'.$code, null, array('maxlength' => 128, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label" style="max-width: 200px;">Job</label>
                                <div class="col-md-10">
                                    {{ Form::text('job-'.$code, null, array('maxlength' => 128, 'class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label" style="max-width: 200px;">Description</label>
                                <div class="col-md-10">
                                    {{ Form::textarea('description-'.$code, null, array('maxlength' => 2048, 'class' => 'form-control')) }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group">
                        <label class="col-md-2 control-label">Image</label>
                        <div class="col-md-4">
                            <input type="file" name="image" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-2"></div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-block btn-success">Add</button>
                        </div>                        
                    </div>
                </div>
            </div>
        <!-- end panel -->
        </form>
    </div>
</div>



@endsection