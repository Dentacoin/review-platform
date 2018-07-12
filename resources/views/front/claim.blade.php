@extends('front')

@section('content')

	<div class="container">

			<div class="col-md-6 col-md-offset-3">
				<div class="panel panel-default">
				<div class="panel-body">
					
					<h1>
		  				{{ trans('front.page.'.$current_page.'.title') }}
		  			</h1>

						@include('front.errors')
	      			<form action="{{ getLangUrl('claim/'.$id.'/'.$hash) }}" method="post" class="form-horizontal">
	      				{!! csrf_field() !!}

	      				<p>
	      					{!! nl2br(trans('front.page.'.$current_page.'.hint')) !!}
	      				</p>
  				
					  	<div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
						  	<label class="control-label col-md-4">
						  		{{ trans('front.page.'.$current_page.'.password')  }}
						  	</label>
						  	<div class="col-md-8">
						    	<input type="password" name="password" class="form-control">
						    </div>
						</div>
					  	<div class="form-group {{ $errors->has('password-repeat') ? 'has-error' : '' }}">
						  	<label class="control-label col-md-4">
						  		{{ trans('front.page.'.$current_page.'.password-repeat') }} 
						  	</label>
						  	<div class="col-md-8">
						    	<input type="password" name="password-repeat" class="form-control">
						    </div>
						</div>

						<div class="form-group">
							<div class="col-md-12">
								<button class="btn btn-primary btn-block" type="submit">
									{{ trans('front.page.'.$current_page.'.submit')  }}
								</button>
							</div>
						</div>
	      			</form>
	      		</div>
	      	</div>
		</div>

	</div>
	
@endsection