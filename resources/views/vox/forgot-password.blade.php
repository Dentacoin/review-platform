@extends('vox')

@section('content')

	<div class="section-recover">

		<div class="container wrapper-flex">
			<div class="col">
				<img class="image-left" src="{{ url('new-vox-img/register-dentist.png') }}">
			</div>

			<div class="col">
				<h3 class="tac">
					Recover Your Password
				</h3>
				<p class="reg-desc">
					Enter your email address below and follow the recovery link <br/> we will send you to reset your password.
				</p>

				<form action="{{ getLangUrl('recover-password') }}" method="post" class="form-horizontal">
					{!! csrf_field() !!}

					@include('front.errors')
					<div class="modern-field alert-after">
						<input type="email" name="email" id="email-forgot" class="modern-input" autocomplete="off" value="{{ old('email') }}" readonly onfocus="this.removeAttribute('readonly');">
						<label for="email-forgot">
							<span>{{ trans('vox.page.login.email') }}</span>
						</label>
					</div>

        			<button class="button" type="submit">
        				{{ trans('front.page.'.$current_page.'.submit') }}
        			</button>
				</form>
			</div>
		</div>
	</div>
	
@endsection