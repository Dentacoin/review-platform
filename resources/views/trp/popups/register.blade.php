<div class="popup" id="popup-register">
	<div class="popup-inner">


		<div class="mobile-buttons flex-mobile">
			<a href="javascript:;" class="button col switch-forms-mobile" data-form="user-form">I'm a user</a>
			<a href="javascript:;" class="button col switch-forms-mobile" data-form="dentist-form">I'm a dentist</a>
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>
		<div class="forms flex">
			<div class="form-wrapper chosen" id="user-form">						
				<a class="switch-forms user-choice" href="javascript:;">
					<img src="img-trp/play-black.png"/>
				</a>
				<a href="javascript:;" class="form-button">I'm a user</a>
				<form id="signin-form-popup-left" class="signin-form blue-form">
					{!! csrf_field() !!}
					<div class="form-inner">

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							You need to log in or register to do this action.
						</div>

						<h2>Sign up</h2>

						<a href="{{ getLangUrl('register/facebook') }}" class="fb-login log-button">
							<span>
								<img src="img-trp/fb.png">
							</span>
							with Facebook
						</a>
						<a href="javascript:;" class="civic-login log-button register-civic-button">
							<span>
								<img src="img-trp/civic.png">
							</span>
							with Civic
						</a>
						<!-- <a href="javascript:;" class="uport-login log-button">
							<span>
								<img src="img-trp/uport.png">
							</span>
							with uPort
						</a> -->
						@include('front.errors')


						<div class="alert alert-info civic-cancelled" style="display: none;">
							{!! nl2br(trans('front.common.civic.cancelled')) !!}
						</div>
						<div class="alert alert-warning civic-error" style="display: none;">
							{!! nl2br(trans('front.common.civic.error')) !!}
							<span></span>
						</div>
						<div class="alert alert-warning civic-weak" style="display: none;">
							{!! nl2br(trans('front.common.civic.weak')) !!}
						</div>
						<div class="alert alert-info civic-wait" style="display: none;">
							{!! nl2br(trans('front.common.civic.wait')) !!}
						</div>
						<input type="hidden" class="jwtAddress" value="{{ getLangUrl('register/civic') }}" />

						<label class="checkbox-label agree-label" for="register-agree">
							<input type="checkbox" class="special-checkbox" id="register-agree" name="agree" value="agree">
							<i class="far fa-square"></i>
							By continuing you agree to our <a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">Privacy Policy</a>
						</label>
					</div>

					<p class="have-account">
						Already have an account? <a class="log-in-button" href="javascript:;">Log in</a>
					</p>
	    		</form>
	    		<form id="login-form-popup-left" class="signin-form blue-form" style="display: none;">
	    			{!! csrf_field() !!}
					<div class="form-inner">

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							You need to log in or register to do this action.
						</div>

						<h2>Log in</h2>

						<a href="{{ getLangUrl('login/facebook') }}" class="fb-login log-button">
							<span>
								<img src="img-trp/fb.png">
							</span>
							with Facebook
						</a>
						<a href="javascript:;" class="civic-login log-button register-civic-button">
							<span>
								<img src="img-trp/civic.png">
							</span>
							with Civic
						</a>
						<!-- <a href="javascript:;" class="uport-login log-button">
							<span>
								<img src="img-trp/uport.png">
							</span>
							with uPort
						</a> -->
						@include('front.errors')
						


						<div class="alert alert-info civic-cancelled" style="display: none;">
							{!! nl2br(trans('front.common.civic.cancelled')) !!}
						</div>
						<div class="alert alert-warning civic-error" style="display: none;">
							{!! nl2br(trans('front.common.civic.error')) !!}
							<span></span>
						</div>
						<div class="alert alert-warning civic-weak" style="display: none;">
							{!! nl2br(trans('front.common.civic.weak')) !!}
						</div>
						<div class="alert alert-info civic-wait" style="display: none;">
							{!! nl2br(trans('front.common.civic.wait')) !!}
						</div>
						<input type="hidden" class="jwtAddress" value="{{ getLangUrl('login/civic') }}" />
					</div>

					<p class="have-account">
						Don't have an account? <a class="sign-in-button" href="javascript:;">Sign up</a>
					</p>
	    		</form>
	    	</div>
			<div class="form-wrapper" id="dentist-form">								
				<a class="switch-forms dentist-choice" href="javascript:;">
					<img src="img-trp/play.png"/>
				</a>
				<a href="javascript:;" class="form-button white-form-button">I'm a dentist</a>
				<form id="signin-form-popup" class="signin-form" action="{{ getLangUrl('register') }}" method="post">
					{!! csrf_field() !!}

					<div class="form-inner">
						
						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							You need to log in or register to do this action.
						</div>

						<h2>Sign up</h2>
						@include('front.errors')
						<div id="register-error" class="alert alert-warning" style="display: none;">
							{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
							<span>

							</span>
						</div>
						<div class="sign-in-step active" id="step-1">
							<input type="text" name="name" id="dentist-name" placeholder="Full name" class="input" value="{{ old('name') }}">
							<input type="email" name="email" id="dentist-email" placeholder="Email address" class="input" value="{{ old('email') }}">
							<input type="password" name="password" id="dentist-password" placeholder="Password" class="input" value="{{ old('password') }}">
							<input type="password" name="password-repeat" id="dentist-password-repeat" placeholder="Repeat password" class="input" value="{{ old('password-repeat') }}">
							<div class="form-info clearfix">
								<a href="javascript:;" class="button next go-to-next" data-validator="{{ getLangUrl('register/step1') }}">Next</a>
							</div>
						</div>
						<div class="sign-in-step" id="step-2">

							<div class="mobile-radios">
								<div class="radio-label">
								  	<label for="mode-dentist">
										<i class="far fa-circle"></i>
								    	<input class="type-radio" type="radio" name="mode" id="mode-dentist" value="dentist">
								    	I work as an independent dental practitioner
								  	</label>
								</div>
								<div class="radio-label">
								  	<label for="mode-clinic">
										<i class="far fa-circle"></i>
								    	<input class="type-radio" type="radio" name="mode" id="mode-clinic" value="clinic">
								    	I represent a dental practice/clinic with more than one dentist.
								  	</label>
								</div>
								<div class="radio-label">
								  	<label for="mode-in-clinic">
										<i class="far fa-circle"></i>
								    	<input class="type-radio" type="radio" name="mode" id="mode-in-clinic" value="in-clinic">
								    	I work as an associate dentist at a dental clinic.
								  	</label>
								</div>
							</div>

					  		<select name="country_id" id="dentist-country" class="input country-select">
					  			@if(!$country_id)
					  				<option>-</option>
					  			@endif
					  			@foreach( $countries as $country )
					  				<option value="{{ $country->id }}" data-code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
					  			@endforeach
					  		</select>
                            {{ Form::select( 'city_id' , $country_id ? ['' => '-'] + \App\Models\City::where('country_id', $country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('front.common.select-country')] , $city_id , array('id' => 'dentist-city', 'class' => 'input city-select') ) }}

							<input type="text" name="address" id="dentist-address" placeholder="Address" class="input">
							<div class="flex input-flex">
								<div>
				    				<span class="phone-code-holder">{{ $country_id ? '+'.$countries->where('id', $country_id)->first()->phone_code : '' }}</span>
								</div>
								<div style="flex: 1;">
									<input type="phone" name="phone" id="dentist-tel" placeholder="Phone number" class="input">
								</div>
							</div>
							<input type="text" name="website" placeholder="Website" id="dentist-website" class="input">
							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< Back</a>
								<a href="javascript:;" class="button next go-to-next" data-validator="{{ getLangUrl('register/step2') }}">Next</a>
							</div>
						</div>
						<div class="sign-in-step" id="step-3">
							<div class="flex flex-mobile">
								<div class="col">
									<label for="add-avatar" class="image-label">
										<div class="centered-hack">
											<i class="fas fa-plus"></i>
											<p>Add profile photo</p>
										</div>
							    		<div class="loader">
							    			<i class="fas fa-circle-notch fa-spin"></i>
							    		</div>
										<input type="file" name="image" id="add-avatar" upload-url="{{ getLangUrl('register/upload') }}">
									</label>
									<input type="hidden" id="photo-name" name="photo" >
								</div>
								<div class="col">
									<div class="specilializations">
										<p class="checkbox-question">Specialization</p>
								    	@foreach($categories as $k => $v)
											<label class="checkbox-label" for="checkbox-{{ $k }}">
												<input type="checkbox" class="special-checkbox" id="checkbox-{{ $k }}" name="specialization[]" value="{{ $loop->index }}">
												<i class="far fa-square"></i>
												{{ $v }}
											</label>
	                                    @endforeach
	                                </div>
								</div>
							</div>

							<div class="search-input" id="clinic-widget">
								<label>
									If the clinic you work in has a profile, you can find and join it.
									<div class="input-wrapper cilnic-suggester-wrapper suggester-wrapper">
										<i class="fas fa-search"></i>
										<input type="text" class="input cilnic-suggester suggester-input" placeholder="Search for clinic...">
										<div class="suggest-results">
										</div>
										<input type="hidden" class="suggester-hidden" name="clinic_id" value="">
									</div>
								</label>
							</div>

							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< Back</a>
								<a href="javascript:;" class="button next go-to-next" data-validator="{{ getLangUrl('register/step3') }}">Next</a>
							</div>
						</div>
						<div class="sign-in-step tac" id="step-4">
					    	<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-size="compact" data-sitekey="6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0"></div>
							<label class="checkbox-label agree-label" for="agree-privacyy">
								<input type="checkbox" class="special-checkbox" id="agree-privacyy" name="agree" value="1">
								<i class="far fa-square"></i>
								I’ve read and agree to the <a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">Privacy Policy</a>
							</label>

							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< Back</a>
								<input type="submit" value="Create profile" class="button next"/>
							</div>
						</div>
					</div>

					<p class="have-account">
						Already have an account? <a class="log-in-button" href="javascript:;">Log in</a>
					</p>

	    		</form>
	    		<form id="login-form-popup" class="signin-form" action="{{ getLangUrl('login') }}" method="post">
	    			{!! csrf_field() !!}

					<div class="form-inner">

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							You need to log in or register to do this action.
						</div>
						
						<h2>Log in</h2>
						@include('front.errors')
						<input type="email" name="email" placeholder="Email address" class="input">
						<input type="password" name="password" placeholder="Password" class="input">

						<div class="form-info tac">
							<input class="button login-button" type="submit" value="Log in"/>
							<div class="alert alert-warning login-error" style="display: none;">
								
							</div>
						</div>
					</div>

					<p class="have-account">
						<a href="{{ getLangUrl('forgot-password') }}">Forgotten password?</a>
						 | 
						Don't have an account? <a class="sign-in-button" href="javascript:;">Sign up</a>
					</p>

	    		</form>
	    	</div>
    	</div>
	</div>
</div>
