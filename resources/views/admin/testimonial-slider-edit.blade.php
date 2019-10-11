@extends('admin')

@section('content')

<h1 class="page-header"> 
    Edit Testimonial
</h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Edit Testimonial</h4>
            </div>
            <div class="panel-body">
                {!! Form::open(array('url' => url('cms/testimonial-slider/edit/'.$item->id), 'method' => 'post', 'class' => 'form-horizontal','files' => true)) !!}
                    {!! csrf_field() !!}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Name</label>
                                <div class="col-md-10">
                                    {{ Form::text( 'name', $item->name, array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Job</label>
                                <div class="col-md-10">
                                    {{ Form::text( 'job', $item->job, array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Description</label>
                                <div class="col-md-10">
                                    {{ Form::textarea( 'description', $item->description, array('class' => 'form-control')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col-md-6">
                        	<label class="col-md-6 control-label">Image</label>
                            <div class="col-md-6">
                                <label for="add-avatar" class="image-label" style="background-image: url('{{ $item->getImageUrl()}}');">
                                    <div class="loader">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </div>
                                    <input type="file" name="image" id="add-avatar" upload-url="{{ url('cms/testimonial-slider/edit/'.$item->id.'/addavatar') }}">
                                </label>
                            </div>
                        </div>
                        
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>

@endsection