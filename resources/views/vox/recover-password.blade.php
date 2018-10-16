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
					Think of strong password containing at least 6 symbols and type it below.
				</p>

				<form action="{{ getLangUrl('recover/'.$id.'/'.$hash) }}" method="post" class="form-horizontal">
					{!! csrf_field() !!}

					@include('front.errors')

					<div class="form-group">
					  	<input type="password" name="password" placeholder="{{ trans('vox.page.'.$current_page.'.password') }}" class="form-control" required>
					</div>
					<div class="form-group">
					    <input type="password" name="password-repeat" class="form-control" placeholder="{{ trans('vox.page.'.$current_page.'.password-repeat') }}" required>
					</div>
        			<div class="form-group">
	        			<button class="btn btn-primary" type="submit">
	        				{{ trans('vox.page.'.$current_page.'.submit') }}
	        			</button>
        			</div>
				</form>
			</div>
		</div>
	</div>
	
@endsection