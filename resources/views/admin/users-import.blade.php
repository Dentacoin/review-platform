@extends('admin')

@section('content')

    <h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Search dentists</h4>
                </div>
                <div class="panel-body">
                    {!! Form::open(array(
                        'url' => url('cms/users/users/import'), 
                        'method' => 'post', 
                        'class' => 'form-horizontal',
                        'files' => true, 
                        'accept'=>'.xls'
                    )) !!}
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label class="col-md-1 control-label">Upload File</label>
                            <div class="col-md-5">                            
                                <input type="file" name="file" class="form-control" accept=".xls">
                            </div>
                        </div>                    
                        <div class="form-group">
                            <div class="col-md-4"></div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-success btn-block">Submit</button>
                            </div>
                        </div>
                        <a href="{{ url('dentist_import.xls') }}">Download sample</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection