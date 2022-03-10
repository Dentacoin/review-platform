@extends('trp')

@section('content')

	<div class="welcome-dentist-title-section">
		<h1 class="mont">
			{{ trans('trp.page.index-dentist.title') }}
		</h1>
	
		<h3>
			{{-- {!! nl2br(trans('trp.page.index-dentist.subtitle')) !!} --}}
			Learn and earn from genuine reviews written by verified patients. Improve your dental services and get rewarded in Dentacoin (DCN).
		</h3>
	</div>

	<div class="welcome-dentist-section">

	    <div class="signin-form-wrapper">
	    	<div class="container">
	    		<form class="signin-form tablet-fixes">

					<div class="form-inner">
						<div class="modern-field">
							<input type="email" name="email" id="dentist-mail" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-mail">
								<span>{{ trans('trp.page.index-dentist.email') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password" id="dentist-pass" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-pass">
								<span>{{ trans('trp.page.index-dentist.password') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password-repeat" id="dentist-pass-repeat" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-pass-repeat">
								<span>{{ trans('trp.page.index-dentist.repeat-password') }}</span>
							</label>
						</div>

						<div class="tac">
							<input type="submit" value="List your practice" class="blue-button button-sign-up-dentist">
							{{-- <input type="submit" value="{{ trans('trp.page.index-dentist.signup') }}" class="blue-button button-sign-up-dentist"> --}}
						</div>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.page.index-dentist.have-account', [
							'link' => '<a href="javascript:;" class="open-dentacoin-gateway dentist-login">',
							'endlink' => '</a>',
						])) !!}					
					</p>

	    		</form>
	    	</div>
	    </div>
	</div>

	<div class="section-dentist-info-wrap">
	    <div class="container section-dentist-info">
	    	<h2 class="mont tac">
	    		{{-- {!! nl2br(trans('trp.page.index-dentist.usp-title')) !!} --}}
				Why Join Dentacoin Trusted Reviews
	    	</h2>
		</div>
	</div>

	<div id="to-append"></div>
	
@endsection