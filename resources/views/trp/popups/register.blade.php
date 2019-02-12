<div class="popup" id="popup-register">
	<div class="popup-inner">


		<div class="mobile-buttons flex-mobile">
			<a href="javascript:;" class="button col switch-forms-mobile" data-form="user-form">
				{!! nl2br(trans('trp.popup.popup-register.user')) !!}
				
			</a>
			<a href="javascript:;" class="button col switch-forms-mobile" data-form="dentist-form">
				{!! nl2br(trans('trp.popup.popup-register.dentist')) !!}
				
			</a>
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>
		<div class="forms flex">
			<div class="form-wrapper {{ $current_page == 'welcome-dentist' ? '' : 'chosen' }}" id="user-form">						
				<a class="switch-forms user-choice" href="javascript:;">
					<img src="img-trp/play-black.png"/>
				</a>
				<a href="javascript:;" class="form-button">
					{!! nl2br(trans('trp.popup.popup-register.user')) !!}
					
				</a>
				<form id="signin-form-popup-left" class="signin-form blue-form">
					{!! csrf_field() !!}
					<div class="form-inner">

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							{!! nl2br(trans('trp.popup.popup-register.cta')) !!}
							
						</div>

						<h2>
							{!! nl2br(trans('trp.popup.popup-register.signup')) !!}
							
						</h2>

						<a href="{{ getLangUrl('register/facebook') }}" class="fb-login log-button">
							<span>
								<img src="img-trp/fb.png">
							</span>
							{!! nl2br(trans('trp.popup.popup-register.facebook')) !!}
							
						</a>
						<a href="javascript:;" class="civic-login log-button register-civic-button">
							<span>
								<img src="img-trp/civic.png">
							</span>
							{!! nl2br(trans('trp.popup.popup-register.civic')) !!}
							
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
							{!! nl2br(trans('trp.popup.popup-register.privacy', [
								'link' => '<a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">',
								'endlink' => '</a>',
							])) !!}
							
						</label>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.popup.popup-register.have-account')) !!}
						
						<a class="log-in-button button-login-patient" href="javascript:;">
							{!! nl2br(trans('trp.popup.popup-register.login')) !!}
							
						</a>
					</p>
	    		</form>
	    		<form id="login-form-popup-left" class="signin-form blue-form" style="display: none;">
	    			{!! csrf_field() !!}
					<div class="form-inner">

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							{!! nl2br(trans('trp.popup.popup-register.cta')) !!}
						</div>

						<h2>
							{!! nl2br(trans('trp.popup.popup-register.login')) !!}
						</h2>

						<a href="{{ getLangUrl('login/facebook') }}" class="fb-login log-button">
							<span>
								<img src="img-trp/fb.png">
							</span>
							{!! nl2br(trans('trp.popup.popup-register.facebook')) !!}
						</a>
						<a href="javascript:;" class="civic-login log-button register-civic-button">
							<span>
								<img src="img-trp/civic.png">
							</span>
							{!! nl2br(trans('trp.popup.popup-register.civic')) !!}
							
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
						{!! nl2br(trans('trp.popup.popup-register.no-account')) !!}
						
						<a class="sign-in-button button-sign-up-patient" href="javascript:;">
							{!! nl2br(trans('trp.popup.popup-register.signup')) !!}
							
						</a>
					</p>
	    		</form>
	    	</div>

			<div class="form-wrapper {{ $current_page == 'welcome-dentist' ? 'chosen' : '' }}" id="dentist-form">								
				<a class="switch-forms dentist-choice" href="javascript:;">
					<img src="img-trp/play.png"/>
				</a>
				<a href="javascript:;" class="form-button white-form-button">
					{!! nl2br(trans('trp.popup.popup-register.dentist')) !!}
				</a>
				<form id="signin-form-popup" class="signin-form" action="{{ getLangUrl('register') }}" method="post">
					{!! csrf_field() !!}

					<div class="form-inner">
						
						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							{!! nl2br(trans('trp.popup.popup-register.cta')) !!}
						</div>

						<h2>
							{!! nl2br(trans('trp.popup.popup-register.signup')) !!}
						</h2>
						<div id="register-error" class="alert alert-warning" style="display: none;">
							{{ trans('trp.popup.popup-register.error')  }}<br/>
							<span>

							</span>
						</div>
						<div class="sign-in-step active" id="step-1">
							@include('front.errors')
							<input type="text" name="name" id="dentist-name" placeholder="{!! nl2br(trans('trp.popup.popup-register.name')) !!}" class="input" value="{{ old('name') }}">
							<input type="text" name="name_alternative" id="dentist-name_alternative" placeholder="{!! nl2br(trans('trp.popup.popup-register.name_alterantive')) !!}" class="input" value="{{ old('name_alternative') }}">
							<input type="email" name="email" id="dentist-email" placeholder="{!! nl2br(trans('trp.popup.popup-register.email')) !!}" class="input" value="{{ old('email') }}">
							<input type="password" name="password" id="dentist-password" placeholder="{!! nl2br(trans('trp.popup.popup-register.password')) !!}" class="input" value="{{ old('password') }}">
							<input type="password" name="password-repeat" id="dentist-password-repeat" placeholder="{!! nl2br(trans('trp.popup.popup-register.repeat-password')) !!}" class="input" value="{{ old('password-repeat') }}">
							<div class="form-info clearfix">
								<a href="javascript:;" class="button next go-to-next button-next-step" step-number="1" data-validator="{{ getLangUrl('register/step1') }}">
									{!! nl2br(trans('trp.common.next')) !!}
									
								</a>
							</div>
						</div>
						<div class="sign-in-step address-suggester-wrapper" id="step-2">

							<div class="mobile-radios" {!! session('join_clinic') && session('invited_by') ? 'style="display: none;"' : '' !!}>
								<div class="radio-label">
								  	<label for="mode-dentist">
										<i class="far fa-circle"></i>
								    	<input class="type-radio" type="radio" name="mode" id="mode-dentist" value="dentist">
								    	{!! nl2br(trans('trp.popup.popup-register.type.dentist')) !!}
								    	
								  	</label>
								</div>
								<div class="radio-label">
								  	<label for="mode-clinic">
										<i class="far fa-circle"></i>
								    	<input class="type-radio" type="radio" name="mode" id="mode-clinic" value="clinic">
								    	{!! nl2br(trans('trp.popup.popup-register.type.clinic')) !!}
								    	
								  	</label>
								</div>
								<div class="radio-label">
								  	<label for="mode-in-clinic">
										<i class="far fa-circle"></i>
								    	<input class="type-radio" type="radio" name="mode" id="mode-in-clinic" value="in-clinic" {!! session('join_clinic') && session('invited_by') ? 'checked="checked"' : '' !!}>
								    	{!! nl2br(trans('trp.popup.popup-register.type.associate')) !!}
								    	
								  	</label>
								</div>
							</div>

					  		<select name="country_id" id="dentist-country" class="input country-select">
					  			@if(!$country_id)
					  				<option>-</option>
					  			@endif
					  			@foreach( $countries as $country )
					  				<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
					  			@endforeach
					  		</select>

		                	<div>
						    	<input type="text" name="address" class="input address-suggester" autocomplete="off" placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}">
		                        <div class="suggester-map-div" style="height: 200px; display: none; margin: 10px 0px; background: transparent;">
		                        </div>
		                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
		                        	{!! nl2br(trans('trp.common.invalid-address')) !!}
		                        </div>
		                    </div>

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
								<a class="back" href="javascript:;">< {!! nl2br(trans('trp.common.back')) !!}</a>
								<a href="javascript:;" class="button next go-to-next button-next-step" step-number="2" data-validator="{{ getLangUrl('register/step2') }}">{!! nl2br(trans('trp.common.next')) !!}</a>
							</div>
						</div>
						<div class="sign-in-step" id="step-3">
							<div class="flex flex-mobile">
								<div class="col">
									<label for="add-avatar" class="image-label">
										<div class="centered-hack">
											<i class="fas fa-plus"></i>
											<p>
												{!! nl2br(trans('trp.popup.popup-register.add-photo')) !!}
												
											</p>
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
										<p class="checkbox-question">
											{!! nl2br(trans('trp.popup.popup-register.specialization')) !!}
											
										</p>
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

							<div class="search-input" id="clinic-widget" {!! session('join_clinic') && session('invited_by') ? 'style="display: none;"' : '' !!}>
								<label>
									{!! nl2br(trans('trp.popup.popup-register.join-clinic')) !!}
									
									<div class="input-wrapper cilnic-suggester-wrapper suggester-wrapper">
										<i class="fas fa-search"></i>
										<input type="text" class="input cilnic-suggester suggester-input" placeholder="{!! nl2br(trans('trp.popup.popup-register.search-clinic')) !!}">
										<div class="suggest-results">
										</div>
										<input type="hidden" class="suggester-hidden" name="clinic_id" value="{{ session('join_clinic') && session('invited_by') ? session('invited_by') : '' }}">
									</div>
								</label>
							</div>

							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< {!! nl2br(trans('trp.common.back')) !!}</a>
								<a href="javascript:;" class="button next go-to-next button-next-step" step-number="3" data-validator="{{ getLangUrl('register/step3') }}">
									{!! nl2br(trans('trp.common.next')) !!}
								</a>
							</div>
						</div>
						<div class="sign-in-step tac" id="step-4">
					    	<div id="captcha-div"></div>
							<label class="checkbox-label agree-label" for="agree-privacyy">
								<input type="checkbox" class="special-checkbox" id="agree-privacyy" name="agree" value="1">
								<i class="far fa-square"></i>
								I’ve read and agree to the <a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">Privacy Policy</a>
							</label>

							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< {!! nl2br(trans('trp.common.back')) !!}</a>
								<input type="submit" value="Create profile" class="button next"/>
							</div>
						</div>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.popup.popup-register.have-account')) !!}
						<a class="log-in-button" href="javascript:;">
							{!! nl2br(trans('trp.popup.popup-register.login')) !!}
						</a>
					</p>

	    		</form>
	    		<form id="login-form-popup" class="signin-form" action="{{ getLangUrl('login') }}" method="post">
	    			{!! csrf_field() !!}

					<div class="form-inner">

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							{!! nl2br(trans('trp.popup.popup-register.cta')) !!}
						</div>
						
						<h2>{!! nl2br(trans('trp.popup.popup-register.login')) !!}</h2>
						@include('front.errors')
						<input type="email" name="email" placeholder="{!! nl2br(trans('trp.popup.popup-register.email')) !!}" class="input">
						<input type="password" name="password" placeholder="{!! nl2br(trans('trp.popup.popup-register.password')) !!}" class="input">

						<div class="form-info tac">
							<input class="button login-button" type="submit" value="{!! nl2br(trans('trp.popup.popup-register.login')) !!}"/>
							<div class="alert alert-warning login-error" style="display: none;">
								
							</div>
						</div>
					</div>

					<p class="have-account">
						<a href="{{ getLangUrl('forgot-password') }}">
							{!! nl2br(trans('trp.popup.popup-register.forgot')) !!}
							
						</a>
						 | 
						{!! nl2br(trans('trp.popup.popup-register.no-account')) !!}
						<a class="sign-in-button button-sign-up-dentist" href="javascript:;">
							{!! nl2br(trans('trp.popup.popup-register.signup')) !!}
						</a>
					</p>

	    		</form>
	    	</div>
    	</div>
	</div>
</div>
