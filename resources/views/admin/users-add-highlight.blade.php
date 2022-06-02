@extends('admin')

@section('content')

    <h1 class="page-header"> 
        Add Highlight to {{ $item->getNames() }}
    </h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Add Highlight to {{ $item->getNames() }}</h4>
                </div>
                <div class="panel-body">
                    {!! Form::open(array('url' => url('cms/users/users/edit/'.$item->id.'/add-highlight/'), 'method' => 'post', 'class' => 'form-horizontal','files' => true)) !!}
                        {!! csrf_field() !!}

                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">Title</label>
                            <div class="col-md-10">
                                {{ Form::text('title', null, array('maxlength' => 128, 'class' => 'form-control')) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">Link</label>
                            <div class="col-md-10">
                                {{ Form::text('link', null, array('maxlength' => 128, 'class' => 'form-control')) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" style="max-width: 200px;">Image</label>
                            <div class="col-md-10">
                                <input type="file" name="image" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                            </div>
                        </div>

                    {!! Form::close() !!}
                </div>
            </div>
            <!-- end panel -->
        </div>
    </div>

@endsection