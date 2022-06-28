@extends('admin')

@section('content')

	<h1 class="page-header">
	    Add new Seo Page
	</h1>
	<!-- end page-header -->

	<div class="panel panel-inverse">
	    <div class="panel-body">
			<div class="row">
			    <!-- begin col-6 -->
			    <div class="col-md-12">
			        {{ Form::open([
						'id' => 'pageseo-add', 
						'class' => 'form-horizontal', 
						'method' => 'post', 
						'class' => 'form-horizontal'
					]) }}
			            {!! csrf_field() !!}
			                        
		            	<div class="form-group">
		                   	<label class="col-md-2 control-label">Name</label>
			                <div class="col-md-5">
			                    {{ Form::text('name', null, array('class' => 'form-control')) }}
			                </div>
			            </div>
		                    
		            	<div class="form-group">
		                   	<label class="col-md-2 control-label">Url</label>
			                <div class="col-md-5">
			                    {{ Form::text('url', null, array('class' => 'form-control')) }}
			                </div>
			            </div>

		                <div class="form-group clearfix">
		                    <label class="col-md-2 control-label">Platform</label>
		                    <div class="col-md-5">
		                        {{ Form::select('platform', $platforms, null, array('class' => 'form-control')) }}
		                    </div>
		                </div>

		                <div class="form-group" style="margin-top: 60px;">
		                    <div class="col-md-5 col-md-offset-2">
		                        <button type="submit" class="btn btn-block btn-success">Save</button>
		                    </div>
		                </div>

			        {{ Form::close() }}
			    </div>
			</div>
		</div>
	</div>

@endsection