@extends('admin')

@section('content')

<h1 class="page-header">
    {{ trans('admin.page.'.$current_page.'.title') }}
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
					<div class="clearfix">
						<div class="pull-left">
							<h4>Errors</h4>
						</div>

						<a href="{{ url('cms/logs') }}?clear=1" class="btn btn-primary pull-right" onclick="return confirm('Are you sure?')">
							clear
						</a>
					</div>

					<pre>
						{!! @file_get_contents( base_path().'/storage/logs/laravel.log' ) !!}
					</pre>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection