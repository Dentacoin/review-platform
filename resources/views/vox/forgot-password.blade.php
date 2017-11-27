@extends('vox')

@section('content')

	<div class="container">

		<div class="col-md-6 col-md-offset-3">
			<br/><br/>
			<div class="panel panel-default">
				<div class="panel-body">
					
		  			<h1>
		  				{{ trans('vox.page.'.$current_page.'.title') }}
		  			</h1>
		  			<br/>

	      			
	      			<form action="{{ getLangUrl('forgot-password') }}" method="post" class="form-horizontal">
	      				{!! csrf_field() !!}

	      				<p>
	      					{{ trans('vox.page.'.$current_page.'.hint') }}
	      				</p>
	      				<br/>
	      				
	        			<div class="form-group">
						  	<label class="control-label col-md-3">
						  		{{ trans('vox.page.'.$current_page.'.email') }}
						  	</label>
						  	<div class="col-md-9">
						  		<input type="email" name="email" class="form-control" required>
						    </div>
						</div>
	        			<div class="form-group">
						  	<div class="col-md-12">
			        			<button class="btn btn-primary btn-block db" type="submit">
			        				{{ trans('vox.page.'.$current_page.'.submit') }}
			        			</button>
	      						@include('front.errors')
						  	</div>
	        			</div>
	      			</form>
	      		</div>
	      	</div>

		</div>

	</div>
	
@endsection