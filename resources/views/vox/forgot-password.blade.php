@extends('vox')

@section('content')

	<div class="section-recover">

		<div class="container">
			<div class="col-md-3">
				<img class="image-left" src="{{ url('new-vox-img/register-dentist.png') }}">
			</div>

			<div class="col-md-9">
				<h3 class="tac">
					Recover Your Password
				</h3>
				<p class="reg-desc">
					Enter your email address below and follow the recovery link <br/> we will send you to reset your password.
				</p>

				<form action="{{ getLangUrl('recover-password') }}" method="post" class="form-horizontal">
					{!! csrf_field() !!}

					@include('front.errors')

					<div class="form-group">
					  	<input type="email" name="email" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.email') }}" required>
					</div>
        			<div class="form-group">
					  	<div class="control-label col-md-12">
		        			<button class="btn btn-primary" type="submit">
		        				{{ trans('front.page.'.$current_page.'.submit') }}
		        			</button>
					  	</div>
        			</div>
				</form>
			</div>
		</div>
	</div>
	
@endsection