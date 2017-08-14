@extends('front')

@section('content')

	<div class="container">

			<div class="col-md-4 col-md-offset-4">
				<div class="panel panel-default">
				<div class="panel-body">
					
		  			<h1>
		  				{{ trans('front.page.'.$current_page.'.title') }}
		  			</h1>

	      			@include('front.errors')
	      			
	      			<form action="{{ getLangUrl('forgot-password') }}" method="post" class="form-horizontal">
	      				{!! csrf_field() !!}

	      				<p>
	      					{{ trans('front.page.'.$current_page.'.hint') }}
	      				</p>
	      				
	        			<div class="form-group">
						  	<label class="control-label col-md-3">
						  		{{ trans('front.page.'.$current_page.'.email') }}
						  	</label>
						  	<div class="col-md-9">
						  		<input type="email" name="email" class="form-control" required>
						    </div>
						</div>
	        			<div class="form-group">
						  	<div class="control-label col-md-12">
			        			<button class="btn btn-primary btn-block db" type="submit">
			        				{{ trans('front.page.'.$current_page.'.submit') }}
			        			</button>
						  	</div>
	        			</div>
	      			</form>
	      		</div>
	      	</div>

		</div>

	</div>
	
@endsection