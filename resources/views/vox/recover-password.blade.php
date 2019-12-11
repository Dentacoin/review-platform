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
					Think of strong password containing at least 6 symbols and type it below.
				</p>

				<form action="{{ getLangUrl('recover/'.$id.'/'.$hash) }}" method="post" class="form-horizontal">
					{!! csrf_field() !!}

					@include('front.errors')

					<div class="modern-field alert-after">
						<input type="password" name="password" id="password-forgot" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
						<label for="password-forgot">
							<span>{{ trans('vox.page.'.$current_page.'.password') }}</span>
						</label>
					</div>

					<div class="modern-field alert-after">
						<input type="password" name="password-repeat" id="password-forgot-repeat" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
						<label for="password-forgot-repeat">
							<span>{{ trans('vox.page.'.$current_page.'.password-repeat') }}</span>
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