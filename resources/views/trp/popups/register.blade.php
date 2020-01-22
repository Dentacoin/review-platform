<div class="popup" id="popup-register">
	<div class="popup-inner">


		<div class="mobile-buttons flex-mobile">
			<a href="javascript:;" class="button col switch-forms-mobile" data-form="user-form">
				{!! nl2br(trans('trp.popup.popup-register.user')) !!}
				
			</a>
			<a href="javascript:;" class="button col switch-forms-mobile" data-form="dentist-form">
				{!! nl2br(trans('trp.popup.popup-register.dentist')) !!}
				
			</a>
		</div>
		<div class="tablet-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>
		<div class="forms flex">
			<div class="form-wrapper {{ $current_page == 'welcome-dentist' ? '' : 'chosen' }}" id="user-form">						
				<!-- <a class="switch-forms user-choice" href="javascript:;">
					<img src="img-trp/play-black.png"/>
				</a> -->
				<a href="javascript:;" class="form-button">
					{!! nl2br(trans('trp.popup.popup-register.user')) !!}
					
				</a>
				<form id="signin-form-popup-left" class="signin-form blue-form">
					{!! csrf_field() !!}
					<div class="form-inner">

						<h2>
							{!! nl2br(trans('trp.popup.popup-register.signup-title')) !!}
						</h2>

						<a href="{{ getLangUrl('register/facebook') }}" class="fb-login log-button has-cookies-button">
							<span>
								<i class="fab fa-facebook"></i>
							</span>
							Continue with Facebook							
						</a>
						<a href="javascript:;" class="civic-login log-button register-civic-button has-cookies-button">
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
						<div class="alert alert-warning agree-cookies" style="display: none;">
							You must accept at least the strictly necessary cookies in order to proceed. 
						</div>

						<div class="cta">
							<i class="fas fa-sign-in-alt"></i>
							{!! nl2br(trans('trp.popup.popup-register.cta')) !!}
							
						</div>
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

						<a href="{{ getLangUrl('login/facebook') }}" class="fb-login log-button has-cookies-button">
							<span>
								<i class="fab fa-facebook"></i>
							</span>
							Continue with Facebook
						</a>
						<a href="javascript:;" class="civic-login log-button register-civic-button has-cookies-button">
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
						<div class="alert alert-warning agree-cookies" style="display: none;">
							You must accept at least the strictly necessary cookies in order to proceed. 
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
				<!-- <a class="switch-forms dentist-choice" href="javascript:;">
					<img src="img-trp/play.png"/>
				</a> -->
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
							{!! nl2br(trans('trp.popup.popup-register.signup-title')) !!}
						</h2>
						<div id="register-error" class="alert alert-warning" style="display: none;">
							<!-- {{ trans('trp.popup.popup-register.error')  }}<br/> -->
							<span>

							</span>
						</div>
						<div class="sign-in-step {!! empty($regData) ? 'active' : '' !!}" id="step-1">
							@include('front.errors')
							<div class="modern-field alert-after">
								<input type="email" name="email" id="dentist-email" class="modern-input" value="{{ !empty($regData) && $regData['email'] ?? old('email') }}" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
								<label for="dentist-email">
									<span>{!! nl2br(trans('trp.popup.popup-register.email')) !!}</span>
								</label>
							</div>
							
							<div class="modern-field alert-after">
								<input type="password" name="password" id="dentist-password" class="modern-input" value="{{ !empty($regData) && $regData['password'] ?? old('password') }}" autocomplete="off">
								<label for="dentist-password">
									<span>{!! nl2br(trans('trp.popup.popup-register.password')) !!}</span>
								</label>
							</div>
							
							<div class="modern-field alert-after">
								<input type="password" name="password-repeat" id="dentist-password-repeat" class="modern-input" value="{{ !empty($regData) && $regData['password'] ?? old('password-repeat') }}" autocomplete="off">
								<label for="dentist-password-repeat">
									<span>{!! nl2br(trans('trp.popup.popup-register.repeat-password')) !!}</span>
								</label>
							</div>
							<div class="form-info clearfix">
								<a href="javascript:;" class="button next go-to-next button-next-step" step-number="1" data-validator="{{ getLangUrl('register/step1') }}">
									{!! nl2br(trans('trp.common.next')) !!}
									
								</a>
							</div>
						</div>
						<div class="sign-in-step {!! !empty($regData) && empty($regData['name']) ? 'active' : '' !!}" id="step-2">

							<div class="mobile-radios modern-radios alert-after" {!! session('join_clinic') && session('invited_by') ? 'style="display: none;"' : '' !!}>
								<div class="radio-label col">
								  	<label for="mode-dentist" {!! !empty($regData) && $regData['mode']=='dentist' ? 'class="active"' : '' !!}>
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio" type="radio" name="mode" id="mode-dentist" value="dentist" {!! !empty($regData) && $regData['mode']=='dentist' ? 'checked="checked"' : '' !!}>
								    	{!! nl2br(trans('trp.popup.popup-register.mode.dentist')) !!}
								  	</label>
								  	<span>{!! nl2br(trans('trp.popup.popup-register.mode.dentist.description')) !!}</span>
								</div>
								<div class="radio-label col">
								  	<label for="mode-clinic" {!! !empty($regData) && $regData['mode']=='clinic' ? 'class="active"' : '' !!}>
										<span class="modern-radio">
											<span></span>
										</span>
								    	<input class="type-radio" type="radio" name="mode" id="mode-clinic" value="clinic" {!! !empty($regData) && $regData['mode']=='clinic' ? 'checked="checked"' : '' !!}>
								    	{!! nl2br(trans('trp.popup.popup-register.mode.clinic')) !!}								    	
								  	</label>
								  	<span>{!! nl2br(trans('trp.popup.popup-register.mode.clinic.description')) !!}</span>
								</div>
							</div>

					  		<div class="modern-field title-wrap alert-after tooltip-text fixed-tooltip" text="Please choose the right title." {!! !empty($regData) && $regData['mode']=='dentist' ? '' : 'style="display: none;"' !!}>
					  			<select name="title" id="dentist-title" class="modern-input" value="{{ $regData['title'] ?? old('title') }}">
					  				@foreach(config('titles') as $k => $v)
					  					<option value="{{ $k }}" {!! !empty($regData) && !empty($regData['title'] && ($regData['title'] == $k)) ? 'selected="selected"' : '' !!}>{{ $v }}</option>
					  				@endforeach
					  			</select>
								<label for="dentist-title">
									<span>{!! nl2br(trans('trp.popup.popup-register.title')) !!}</span>
								</label>
							</div>

					  		<div class="modern-field alert-after tooltip-text fixed-tooltip" text="Write your names (or your official practice name) in full! This ensures that patients who search for you will find you easily.">
								<input type="text" name="name" id="dentist-name" class="modern-input dentist-name-register" value="{{ !empty($regData) && $regData['name'] ?? old('name') }}" autocomplete="off">
								<label for="dentist-name">
									<span>{!! nl2br(trans('trp.popup.popup-register.name')) !!}</span>
								</label>
								<p>{!! nl2br(trans('trp.popup.popup-register.name.description')) !!}</p>
							</div>

							<div class="alert alert-warning" id="alert-name-dentist" style="display: none;">Latin letters only. Please add the alternative spelling below.</div>

					  		<div class="modern-field tooltip-text fixed-tooltip" text="Patients who search for your name in your language will still find your profile.">
								<input type="text" name="name_alternative" id="dentist-name_alternative" class="modern-input" value="{{ $regData['name_alternative'] ?? old('name_alternative') }}" autocomplete="off">
								<label for="dentist-name_alternative">
									<span>{!! nl2br(trans('trp.popup.popup-register.name_alterantive')) !!}</span>
								</label>
								<p>{!! nl2br(trans('trp.popup.popup-register.name_alterantive.description')) !!}</p>
							</div>

							<label class="checkbox-label agree-label alert-after {!! (!empty($regData) && empty($regData['country_id']) && !empty($regData['name'])) || (empty($regData['specialization']) && !empty($regData['country_id'])) ? 'active' : '' !!}" for="agree-privacyyy" >
								<input type="checkbox" class="special-checkbox" id="agree-privacyyy" name="agree" value="1" {!! (!empty($regData) && empty($regData['country_id']) && !empty($regData['name'])) || (empty($regData['specialization']) && !empty($regData['country_id'])) ? 'checked="checked"' : '' !!} />
								<i class="far fa-square"></i>
								{!! nl2br(trans('trp.popup.popup-register.terms', [
									'link' => '<a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">',
									'endlink' => '</a>',
								])) !!}
							</label>
							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< {!! nl2br(trans('trp.common.back')) !!}</a>
								<a href="javascript:;" class="button next go-to-next button-next-step" step-number="2" data-validator="{{ getLangUrl('register/step2') }}">{!! nl2br(trans('trp.common.next')) !!}</a>
							</div>
						</div>
						<div class="sign-in-step address-suggester-wrapper {!! !empty($regData) && empty($regData['country_id']) && !empty($regData['name']) ? 'active' : '' !!}" id="step-3">
							<div class="alert alert-warning ip-country mobile" style="display: none;">
	                        	Hmm... Your IP thinks differently. <br/>
								Sure you've entered the right country?
	                        </div>	
							<div class="modern-field">
					  			<select name="country_id" id="dentist-country" class="modern-input country-select country-dropdown" real-country="{{ !empty($country_id) ? $country_id : '' }}">
					  				@if(!$country_id)
						  				<option>-</option>
						  			@endif
					  				@foreach( $countries as $country )
					  					<option value="{{ $country->id }}" code="{{ $country->code }}" {!! (!empty($regData) && !empty($regData['country_id']) && $regData['country_id']==$country->id) || (empty($regData['country_id']) && $country_id==$country->id) ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
					  				@endforeach
					  			</select>
							</div>

							<div class="modern-field alert-after tooltip-text fixed-tooltip" text="Enter your full address, in the same way it is displayed on your website / Facebook page / Google Business profile.">
								<input type="text" name="address" id="dentist-address" class="modern-input address-suggester" autocomplete="off" value="{{ $regData['address'] ?? old('address') }}">
								<label for="dentist-address">
									<span>{!! nl2br(trans('trp.popup.popup-register.address')) !!}</span>
								</label>
								<p>{!! nl2br(trans('trp.popup.popup-register.address.description')) !!}</p>
							</div>

		                	<div>
						    	<div class="suggester-map-div" style="height: 200px; display: none; margin: 10px 0px; background: transparent;">
		                        </div>
		                        <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px 20px;">
		                        	{!! nl2br(trans('trp.common.check-address')) !!}
		                        </div>
		                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: -10px 0px 10px;">
		                        	{!! nl2br(trans('trp.common.invalid-address')) !!}
		                        </div>
						        <div class="alert alert-warning different-country-hint mobile" style="display: none; margin: -10px 0px 10px;">
						        	Unable to proceed. Please, choose address from your country.
						        </div>
		                    </div>

							<div class="modern-field alert-after tooltip-text fixed-tooltip" text="Website URL or Facebook page">
								<input type="text" name="website" id="dentist-website" class="modern-input" autocomplete="off" value="{{ !empty($regData) && $regData['website'] ?? old('website') }}">
								<label for="dentist-website">
									<span>{!! nl2br(trans('trp.popup.popup-register.website')) !!}</span>
								</label>
								<p>{!! nl2br(trans('trp.popup.popup-register.website.description')) !!}</p>
							</div>

							<div class="flex input-flex alert-after">
								<div>
				    				<span class="phone-code-holder">{{ $country_id ? '+'.$countries->where('id', $country_id)->first()->phone_code : '' }}</span>
								</div>
								<div style="flex: 1;" class="modern-field tooltip-text fixed-tooltip" text="Enter your official practice phone number exactly as it is on your website / Facebook page.">
									<input type="text" name="phone" id="dentist-tel" class="modern-input" autocomplete="off" value="{{ !empty($regData) && $regData['phone'] ?? old('phone') }}">
									<label for="dentist-tel">
										<span>{!! nl2br(trans('trp.popup.popup-register.phone')) !!}</span>
									</label>
								</div>
							</div>

							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< {!! nl2br(trans('trp.common.back')) !!}</a>
								<a href="javascript:;" class="button next go-to-next button-next-step" step-number="3" data-validator="{{ getLangUrl('register/step3') }}">
									{!! nl2br(trans('trp.common.next')) !!}
								</a>
							</div>
						</div>
						<div class="sign-in-step {!! !empty($regData) && !empty($regData['country_id']) ? 'active' : '' !!} tac" id="step-4">
							<div class="flex flex-mobile alert-after">
								<div class="col" style="max-width: 154px;">
									<label for="add-avatar" class="image-label tooltip-text fixed-tooltip" text="Photos build trust. Add a clear image of you/your team or upload your practice logo." {!! !empty($regData) && !empty($regData['photoThumb']) ? 'style="background-image:url('.$regData['photoThumb'].');"' : '' !!} >
										@if(empty( $regData['photo'] ))
											<div class="centered-hack">
												<i class="fas fa-plus"></i>
												<p>
													{!! nl2br(trans('trp.popup.popup-register.add-photo')) !!}													
												</p>
											</div>
										@endif
							    		<div class="loader">
							    			<i class="fas fa-circle-notch fa-spin"></i>
							    		</div>
										<input type="file" name="image" id="add-avatar" upload-url="{{ getLangUrl('register/upload') }}">
									</label>
									<input type="hidden" id="photo-name" name="photo" value="{{ !empty($regData) && $regData['photo'] ?? '' }}" >
									<input type="hidden" id="photo-thumb" name="photo-thumb" value="{{ $regData['photoThumb'] ?? '' }}" >
								</div>
								<div class="col">
									<div class="specilializations">
										<p class="checkbox-question">
											{!! nl2br(trans('trp.popup.popup-register.specialization')) !!}
											
										</p>
								    	@foreach($categories as $k => $v)
											<label class="checkbox-label{!! !empty($regData) && !empty($regData['specialization']) && in_array($loop->index, $regData['specialization']) ? ' active' : '' !!}" for="checkbox-{{ $k }}">
												<input 
													type="checkbox" 
													class="special-checkbox" 
													id="checkbox-{{ $k }}" 
													name="specialization[]" 
													value="{{ $loop->index }}"
													{!! !empty($regData['specialization']) && in_array($loop->index, $regData['specialization']) ? 'checked="checked"' : '' !!}
												>
												<i class="far fa-square"></i>
												{{ $v }}
											</label>
	                                    @endforeach
	                                </div>

								</div>
							</div>
					    	<div id="captcha-div"></div>

							<div class="form-info clearfix">
								<a class="back" href="javascript:;">< {!! nl2br(trans('trp.common.back')) !!}</a>
								<input type="submit" value="{!! nl2br(trans('trp.popup.popup-register.create-profile')) !!}" class="button next"/>
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

						<div class="modern-field">
							<input type="email" name="email" id="dentist-login-email" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-login-email">
								<span>{!! nl2br(trans('trp.popup.popup-register.email')) !!}</span>
							</label>
						</div>

						<div class="modern-field">
							<input type="password" name="password" id="dentist-login-password" class="modern-input" autocomplete="off">
							<label for="dentist-login-password">
								<span>{!! nl2br(trans('trp.popup.popup-register.password')) !!}</span>
							</label>
						</div>

						<div class="form-info tac">
							<input class="button login-button has-cookies-button" type="submit" value="{!! nl2br(trans('trp.popup.popup-register.login')) !!}"/>
							<div class="alert alert-warning login-error" style="display: none;">
								
							</div>
							<div class="alert alert-warning agree-cookies" style="display: none;">
								You must accept at least the strictly necessary cookies in order to proceed. 
							</div>
						</div>
						<div class="login-without-account">
							{!! nl2br(trans('trp.popup.popup-register.no-account')) !!}
							<a class="sign-in-button button-sign-up-dentist" href="javascript:;">
								{!! nl2br(trans('trp.popup.popup-register.signup')) !!}
							</a>
						</div>
					</div>

					<p class="have-account forgot-password-wrap">
						<a href="{{ getLangUrl('forgot-password') }}">
							{!! nl2br(trans('trp.popup.popup-register.forgot')) !!}							
						</a>
					</p>

	    		</form>
	    	</div>
    	</div>
	</div>
</div>
