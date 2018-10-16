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
						Create Your Profile
					</h3>
					<p class="reg-desc">
						Thank you for taking our Welcome Questionnaire! To complete your registration and get your reward, just sign up using some of the options below.
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
							I'm a patient
						</a>
						<a href="javascript:;" type="reg-dentists">
							I'm a dentist
						</a>
					</div>

					<div class="reg-wrapper row clearfix">

						<div class="errors-wrapper">
							@include('front.errors')
						</div>

						<div class="reg-patients col-md-6 tac">
							<h4>Users (Patients)</h4>

							<div class="fb-button-inside">
								<a href="{{ getLangUrl('register/facebook') }}" class="fb-register">
								</a>
								<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
							</div>
						</div>

						<div class="reg-dentists col-md-6">
							<h4 class="tac">Dentists</h4>

							<div id="register-error" class="alert alert-warning" style="display: none;">						
								{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
								<span></span>
							</div>

							<div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
								<input type="text" class="form-control" name="name" id="name" placeholder="{{ trans('vox.popup.register.placeholder-name') }}">
								<span class="error-message" id="name-error"></span>
							</div>
							<div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
								<input type="text" class="form-control" name="email" id="email" placeholder="{{ trans('vox.popup.register.placeholder-email') }}">
								<span class="error-message" id="email-error"></span>
							</div>
							<div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
								<input type="password" class="form-control" name="password" id="password" placeholder="{{ trans('vox.popup.register.placeholder-password') }}
								">
								<span class="error-message" id="password-error"></span>
							</div>
							<div class="form-group {{ $errors->has('password-repeat') ? 'has-error' : '' }}">
								<input type="password" class="form-control" name="password-repeat" id="password-repeat" placeholder="{{ trans('vox.popup.register.placeholder-password-repeat') }}">
								<span class="error-message" id="password-repeat-error"></span>
							</div>

							<div class="form-group tac">
								<button class="btn" id="go-to-2" data-validator="{{ getLangUrl('registration/step1') }}">
									Sign up
								</button>
							</div>
							
							<!--
								<div class="tac">
									<a class="login-text" href="{{ getLangUrl('login') }}">
										{{ trans('front.page.'.$current_page.'.login') }}
									</a>
								</div>
					        -->
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
						Registration for Dentists
					</h3>
					<p class="reg-desc">
						By creating a profile on DentaVox, you also get full access to all <br/> Dentacoin tools and features. Manage all your activities from a single place!
					</p>

					<div class="clearfix reg-step-2-wrapper">

						<div class="col-md-6 register-dentist-left">

							<div id="register-error-two" class="alert alert-warning" style="display: none;">						
								{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
								<span></span>
							</div>

						  	<div class="form-group {{ $errors->has('country_id') ? 'has-error' : '' }}">
						  		<select name="country_id" id="dentist-country" class="form-control country-select" placeholder="{{ trans('front.page.'.$current_page.'.country') }}">
						  			@if(!$country_id)
						  				<option>-</option>
						  			@endif
						  			@foreach( $countries as $country )
						  				<option value="{{ $country->id }}" data-code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
						  			@endforeach
						  		</select>
						  		<span class="error-message" id="country-error"></span>
							</div>
						  	<div class="form-group {{ $errors->has('city_id') ? 'has-error' : '' }}">
				                {{ Form::select( 'city_id' , $country_id ? ['' => '-'] + \App\Models\City::where('country_id', $country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('front.common.select-country')] , $city_id , array('id' => 'dentist-city', 'class' => 'form-control city-select') ) }}
								<span class="error-message" id="city-error"></span>
							</div>

						  	<div class="form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
							    <input type="text" name="zip" id="dentist-zip" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.zip') }} ">
							    <span class="error-message" id="zip-error"></span>
							</div>

						  	<div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
							    <input type="text" name="address" id="dentist-address" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.address') }} ">
							    <span class="error-message" id="address-error"></span>
							</div>                                    

						  	<div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
							    <input type="text" name="phone" id="dentist-phone" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.phone') }}">
					    		<i>+ [country code] [area / provider code] [phone number]</i>
							    <span class="error-message" id="phone-error"></span>
							</div>

						  	<div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">
							    <input type="text" name="website" id="dentist-website" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.website') }} ">
							    <span class="error-message" id="website-error"></span>
							</div>

							<div class="form-group {{ $errors->has('photo') ? 'has-error' : '' }}">
						    	<label class="add-photo" for="add-avatar">
						    		<div class="photo-cta">
						    			<i class="fa fa-plus"></i>
						    			{{ trans('front.page.'.$current_page.'.photo-add') }} 
						    		</div> 
						    		<div class="loader">
						    			<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>
						    		</div>
						    		<input id="add-avatar" name="image" type="file">
						    	</label>
					    		<i>* Supported formats: PNG, JPG, GIF, up to 4MB</i>
								<input type="hidden" id="photo-name" name="photo" >
								<span class="error-message" id="photo-error"></span>
								<div id="photo-upload-error" style="display: none;" class="alert alert-warning">
									Uh, an error occured during upload. Please try again or use a different photo.
								</div>
							</div>

						  	<div class="form-group {{ $errors->has('captcha') ? 'has-error' : '' }}">
							    <div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-size="compact" data-sitekey="6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0"></div>
							    <span class="error-message" id="captcha-error"></span>
							</div>

							<button class="btn btn-block" id="go-to-3" type="submit">
								{{ trans('front.page.'.$current_page.'.submit')  }}
							</button>
						</div>

						<div class="col-md-6 register-dentist-right">
							<p>Earn DCN by referring patients</p>
							<p>Check real-time survey stats</p>
							<p>Improve your dental practice</p>
							<p>Order custom surveys</p>
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