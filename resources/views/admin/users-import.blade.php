@extends('admin')

@section('content')

<h1 class="page-header">{{ trans('admin.page.'.$current_page.'.title') }}</h1>
<!-- end page-header -->


<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Search dentists</h4>
            </div>
            <div class="panel-body">
                {!! Form::open(array('url' => url('cms/'.$current_page.'/import'), 'method' => 'post', 'class' => 'form-horizontal','files' => true)) !!}
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
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>


@endsection