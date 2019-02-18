@extends('vox')

@section('content')

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=1906201509652855&autoLogAppEvents=1';
	fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

	<div class="section-register">

		<form action="{{ getLangUrl('registration') }}" id="register-form" method="post" role="form" data-toggle="validator" class="form-horizontal">
			{!! csrf_field() !!}


			<div class="container" id="step-1">
				<div class="col-md-3">
					<img class="image-left" src="{{ url('new-vox-img/register.png') }}">
				</div>

				<div class="col-md-9">
					<h3 class="tac">
						{!! trans('vox.page.register.title') !!}
						
					</h3>
					<p class="reg-desc">
						{!! nl2br(trans('vox.page.register.subtitle')) !!}
					</p>

					<div class="form-group {{ $errors->has('privacy') ? 'has-error' : '' }}">
						<div class="checkbox tac">
							<label for="read-privacy" class="reg-privacy gradient-line">
								<i class="far fa-square"></i>
								<input id="read-privacy" type="checkbox" name="privacy" class="input-checkbox">
								{!! nl2br(trans('front.page.'.$current_page.'.agree-privacy', [
									'privacylink' => '<a href="'.getLangUrl('privacy').'">', 
									'endprivacylink' => '</a>'
								])) !!}
								<span class="error-message" id="privacy-error"></span>
							</label>
						</div>
					</div>

					<div class="user-type-mobile">
						<a href="javascript:;" type="reg-patients">
							{{ trans('vox.common.type-patient-mobile')  }}
						</a>
						<a href="javascript:;" type="reg-dentists">
							{{ trans('vox.common.type-dentist-mobile')  }}
						</a>
					</div>

					<div class="errors-wrapper">
						@include('front.errors')
					</div>

					<div class="reg-wrapper row clearfix">

						<div class="reg-patients col-md-6 tac">
							<h4>
								{{ trans('vox.common.type-patient')  }}
							</h4>

							<div class="fb-button-inside">
								<a href="{{ getLangUrl('register/facebook') }}" class="fb-register">
								</a>
								<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
							</div>

							<div class="civic-button" id="register-civic-button">
								<i class="fas fa-circle-notch fa-spin"></i>
								Continue with Civic
							</div>
							<br/>
							<br/>

							<div id="civic-cancelled" class="alert alert-info" style="display: none;">
								{!! nl2br(trans('front.common.civic.cancelled')) !!}
							</div>
							<div id="civic-error" class="alert alert-warning" style="display: none;">
								{!! nl2br(trans('front.common.civic.error')) !!}
								<span></span>
							</div>
							<div id="civic-weak" class="alert alert-warning" style="display: none;">
								{!! nl2br(trans('front.common.civic.weak')) !!}
							</div>
							<div id="civic-wait" class="alert alert-info" style="display: none;">
								{!! nl2br(trans('front.common.civic.wait')) !!}
							</div>
							<input type="hidden" id="jwtAddress" value="{{ getLangUrl('register/civic') }}" />
						</div>

						<div class="reg-dentists col-md-6">
							<h4 class="tac">
								{{ trans('vox.common.type-dentist')  }}
							</h4>

							<div id="register-error" class="alert alert-warning" style="display: none;">						
								{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
								<span></span>
							</div>

							<div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
								<input type="text" class="form-control" name="name" id="name" placeholder="{!! trans('vox.page.register.name') !!}">
					    		<i class="hint">
									{!! trans('vox.page.register.name-hint') !!}
								</i>
								<span class="error-message" id="name-error"></span>
							</div>
							<div class="form-group {{ $errors->has('name_alternative') ? 'has-error' : '' }}">
								<input type="text" class="form-control" name="name_alternative" id="name_alternative" placeholder="{!! trans('vox.page.register.name_alternative') !!}">
								<i class="hint">
									{!! trans('vox.page.register.name_alternative-hint') !!}
								</i>
								<span class="error-message" id="name_alternative-error"></span>
							</div>
							<div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
								<input type="text" class="form-control" name="email" id="email" placeholder="{!! trans('vox.page.register.email') !!}">
								<span class="error-message" id="email-error"></span>
							</div>
							<div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
								<input type="password" class="form-control" name="password" id="password" placeholder="{!! trans('vox.page.register.password') !!}
								">
								<span class="error-message" id="password-error"></span>
							</div>
							<div class="form-group {{ $errors->has('password-repeat') ? 'has-error' : '' }}">
								<input type="password" class="form-control" name="password-repeat" id="password-repeat" placeholder="{!! trans('vox.page.register.password-repeat') !!}">
								<span class="error-message" id="password-repeat-error"></span>
							</div>

							<div class="form-group tac">
								<button class="btn" id="go-to-2" data-validator="{{ getLangUrl('registration/step1') }}">
									{!! trans('vox.page.register.sign-up') !!}
								</button>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="container" id="step-2" style="display: none;">

				<div class="col-md-3">
					<img class="image-left" src="{{ url('new-vox-img/register-dentist.png') }}">
				</div>

				<div class="col-md-9 clearfix">

					<h3 class="tac">
						{!! trans('vox.page.register.title-dentist') !!}
					</h3>
					<p class="reg-desc">
						{!! nl2br(trans('vox.page.register.subtitle-dentist')) !!}
					</p>

					<div class="clearfix reg-step-2-wrapper">

						<div class="col-md-6 register-dentist-left address-suggester-wrapper">

							<div id="register-error-two" class="alert alert-warning" style="display: none;">						
								{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
								<span></span>
							</div>

						  	<div class="form-group {{ $errors->has('country_id') ? 'has-error' : '' }}">
						  		<select name="country_id" id="dentist-country" class="form-control country-select" placeholder="{!! trans('vox.page.register.country') !!}">
						  			@if(!$country_id)
						  				<option>-</option>
						  			@endif
						  			@foreach( $countries as $country )
						  				<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
						  			@endforeach
						  		</select>
						  		<span class="error-message" id="country-error"></span>
							</div>
						  	

						  	<div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
						    	<input type="text" name="address" id="dentist-address" class="form-control address-suggester" autocomplete="off" placeholder="{!! trans('vox.page.register.address') !!}">
							    <span class="error-message" id="address-error"></span>
		                        <div class="suggester-map-div" style="height: 200px; display: none; margin: 10px 0px; background: transparent;">
		                        </div>
		                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
		                        	{!! nl2br(trans('vox.common.invalid-address')) !!}
		                        </div>
		                    </div>                          

						  	<div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
						  		<div class="flex flex-center">
						  			<div class="phone-code-holder">{{ $country_id ? '+'.$countries->where('id', $country_id)->first()->phone_code : '' }}</div>
								    <input type="text" name="phone" id="dentist-phone" class="form-control" placeholder="{!! trans('vox.page.register.phone') !!}">
								</div>
								<!--
						    		<i>
						    			{!! trans('vox.page.register.phone-hint') !!}
						    		</i>
						    	-->
							    <span class="error-message" id="phone-error"></span>
							</div>

						  	<div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">
							    <input type="text" name="website" id="dentist-website" class="form-control" placeholder="{!! trans('vox.page.register.website') !!} ">
								<i class="hint">
									{!! trans('vox.page.register.website-hint') !!}
								</i>
							    <span class="error-message" id="website-error"></span>
							</div>

							<div class="form-group {{ $errors->has('photo') ? 'has-error' : '' }}">
						    	<label class="add-photo" for="add-avatar">
						    		<div class="photo-cta">
						    			<i class="fa fa-plus"></i>
						    			{!! trans('vox.page.register.photo-add') !!}
						    		</div> 
						    		<div class="loader">
						    			<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>
						    		</div>
						    		<input id="add-avatar" name="image" type="file">
						    	</label>
					    		<i class="hint">
					    			{!! trans('vox.page.register.photo-hint') !!}
					    			
					    		</i>
								<input type="hidden" id="photo-name" name="photo" >
								<span class="error-message" id="photo-error"></span>
								<div id="photo-upload-error" style="display: none;" class="alert alert-warning">
									{!! trans('vox.page.register.photo-error') !!}
									
								</div>
							</div>

						  	<div class="form-group {{ $errors->has('captcha') ? 'has-error' : '' }}">
							    <div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-size="compact" data-sitekey="6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0"></div>
							    <span class="error-message" id="captcha-error"></span>
							</div>

							<button class="btn btn-block" id="go-to-3" type="submit">
								{!! trans('vox.page.register.sign-up') !!}
							</button>
						</div>

						<div class="col-md-6 register-dentist-right">
							<p>
								{!! trans('vox.page.register.dentist-perk-1') !!}
							</p>
							<p>
								{!! trans('vox.page.register.dentist-perk-2') !!}
							</p>
							<p>
								{!! trans('vox.page.register.dentist-perk-3') !!}
							</p>
							<p>
								{!! trans('vox.page.register.dentist-perk-4') !!}
							</p>
						</div>
					</div>
				</div>
			</div>

		</form>
	</div>

	<script type="text/javascript">
		var upload_url = '{{ getLangUrl('registration/upload') }}';
	</script>

@endsection