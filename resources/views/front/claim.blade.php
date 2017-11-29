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
  				
					  	<div class="col-md-12 text-center">
							<a class="btn register-social btn-default" title="{{ trans('front.page.'.$current_page.'.facebook') }}" href="{{ getLangUrl('register/facebook/1') }}">
								<i class="fa fa-facebook"></i> Register with Facebook
							</a>
						</div>
	      			</form>
	      		</div>
	      	</div>
		</div>

	</div>
	
@endsection