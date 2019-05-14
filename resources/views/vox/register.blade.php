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
								<a href="https://dev.dentavox.dentacoin.com/en/register/facebook" class="fb-register">
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

							<div class="sign-in-step active" id="step-1">

								<div class="modern-field alert-after {{ $errors->has('email') ? 'has-error' : '' }}">
									<input type="email" name="email" id="email" class="modern-input" autocomplete="off">
									<label for="email">
										<span>{!! trans('vox.page.register.email') !!}</span>
									</label>
								</div>

								<div class="modern-field alert-after {{ $errors->has('password') ? 'has-error' : '' }}">
									<input type="password" name="password" id="password" class="modern-input" autocomplete="off">
									<label for="password">
										<span>{!! trans('vox.page.register.password') !!}</span>
									</label>
								</div>

								<div class="modern-field alert-after {{ $errors->has('password-repeat') ? 'has-error' : '' }}">
									<input type="password" name="password-repeat" id="password-repeat" class="modern-input" autocomplete="off">
									<label for="password-repeat">
										<span>{!! trans('vox.page.register.password-repeat') !!}</span>
									</label>
								</div>								

								<div class="form-info clearfix">
									<button class="btn go-to-next" id="go-to-2" data-validator="{{ getLangUrl('registration/step1') }}">
										{!! trans('vox.page.register.next') !!}
									</button>
								</div>
							</div>
							<div class="sign-in-step address-suggester-wrapper" id="step-2">

								<div class="modern-radios alert-after">
									<div class="radio-label">
									  	<label for="mode-dentist">
											<span class="modern-radio">
												<span></span>
											</span>
									    	<input class="type-radio" type="radio" name="mode" id="mode-dentist" value="dentist">
									    	{!! nl2br(trans('vox.page.register.mode.dentist')) !!}
									  	</label>
									  	<span>{!! nl2br(trans('vox.page.register.mode.dentist.description')) !!}</span>
									</div>
									<div class="radio-label">
									  	<label for="mode-clinic">
											<span class="modern-radio">
												<span></span>
											</span>
									    	<input class="type-radio" type="radio" name="mode" id="mode-clinic" value="clinic">
									    	{!! nl2br(trans('vox.page.register.mode.clinic')) !!}								    	
									  	</label>
									  	<span>{!! nl2br(trans('vox.page.register.mode.clinic.description')) !!}</span>
									</div>
								</div>

								<div class="modern-field alert-after title-wrap">
									{{ Form::select( 'title' , config('titles') , null , array('class' => 'modern-input', 'id'=>'title') ) }}
									<label for="title">
										<span>{!! trans('vox.page.register.title-placeholder') !!}</span>
									</label>
								</div>

								<div class="modern-field alert-after {{ $errors->has('name') ? 'has-error' : '' }}">
									<input type="text" name="name" id="name" class="modern-input" autocomplete="off">
									<label for="name">
										<span>{!! trans('vox.page.register.name') !!}</span>
									</label>
									<p>{!! trans('vox.page.register.name-hint') !!}</p>
								</div>

								<div class="modern-field alert-after {{ $errors->has('name_alternative') ? 'has-error' : '' }}">
									<input type="text" name="name_alternative" id="name_alternative" class="modern-input" autocomplete="off">
									<label for="name_alternative">
										<span>{!! trans('vox.page.register.name_alternative') !!}</span>
									</label>
									<p>{!! trans('vox.page.register.name_alternative-hint') !!}</p>
								</div>

								<div class="form-info clearfix">
									<a class="back" href="javascript:;">&lt; {!! trans('vox.page.register.back') !!}</a>
									<button class="btn go-to-next" id="go-to-3" type="submit" data-validator="{{ getLangUrl('registration/step2') }}">
										{!! trans('vox.page.register.next') !!}
									</button>
								</div>								
							</div>
							<div class="sign-in-step address-suggester-wrapper" id="step-3">

								<div class="modern-field  {{ $errors->has('country_id') ? 'has-error' : '' }}">
						  			<select name="country_id" id="dentist-country" class="modern-input country-select">
						  				@if(!$country_id)
							  				<option>-</option>
							  			@endif
						  				@foreach( $countries as $country )
						  					<option value="{{ $country->id }}" code="{{ $country->code }}" {!! ( $test_country_id ?? $country_id )==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
						  				@endforeach
						  			</select>
								</div>

								<div class="modern-field alert-after {{ $errors->has('address') ? 'has-error' : '' }}">
									<input type="text" name="address"  id="dentist-address" class="modern-input address-suggester" autocomplete="off">
									<label for="dentist-address">
										<span>{!! trans('vox.page.register.address') !!}</span>
									</label>
									<p>{!! nl2br(trans('vox.page.register.address.description')) !!}</p>
								</div>

			                	<div>
							    	<div class="suggester-map-div" style="height: 200px; display: none; margin: 10px 0px; background: transparent;">
			                        </div>
			                        <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px 20px;">
			                        	{!! nl2br(trans('vox.common.check-address')) !!}
			                        </div>
			                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: -10px 0px 10px;">
			                        	{!! nl2br(trans('vox.common.invalid-address')) !!}
			                        </div>		                        
			                    </div>

			                    <div class="modern-field alert-after {{ $errors->has('website') ? 'has-error' : '' }}">
									<input type="text" name="website" id="dentist-website" class="modern-input" autocomplete="off" value="{{ $regData['website'] ?? old('website') }}">
									<label for="dentist-website">
										<span>{!! trans('vox.page.register.website') !!} </span>
									</label>
									<p>{!! trans('vox.page.register.website-hint') !!}</p>
								</div>

								<div class="flex input-flex alert-after {{ $errors->has('phone') ? 'has-error' : '' }}">
									<div>
					    				<span class="phone-code-holder">{{ $country_id ? '+'.$countries->where('id', $country_id)->first()->phone_code : '' }}</span>
									</div>
									<div style="flex: 1;" class="modern-field">
										<input type="text" name="phone" id="dentist-phone" class="modern-input" autocomplete="off">
										<label for="dentist-phone">
											<span>{!! trans('vox.page.register.phone') !!}</span>
										</label>
									</div>
								</div>

								<div class="form-info clearfix">
									<a class="back" href="javascript:;">&lt; {!! trans('vox.page.register.back') !!}</a>
									<button class="btn go-to-next" id="go-to-4" type="submit" data-validator="{{ getLangUrl('registration/step3') }}">
										{!! trans('vox.page.register.next') !!}
									</button>
								</div>
							</div>
							<div class="sign-in-step" id="step-4">

								<div class="flex alert-after">
									<div class="col image-w">
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
							    		<!-- <i class="hint">
							    			{!! trans('vox.page.register.photo-hint') !!}
							    			
							    		</i> -->
										<input type="hidden" id="photo-name" name="photo" >
										<div id="photo-upload-error" style="display: none;" class="alert alert-warning">
											{!! trans('vox.page.register.photo-error') !!}
											
										</div>
									</div>
									<div class="col">
										<div class="specializations">
											<p class="checkbox-question">
												{!! trans('vox.page.register.specialization') !!}
											</p>
									    	@foreach($categories as $k => $v)
												<label class="checkbox-label" for="checkbox-{{ $k }}">
													<input 
														type="checkbox" 
														class="input-checkbox" 
														id="checkbox-{{ $k }}" 
														name="specialization[]" 
														value="{{ $loop->index }}"
													>
													<i class="far fa-square"></i>
													{{ $v }}
												</label>
		                                    @endforeach
		                                </div>

									</div>
								</div>								

							  	<div class="form-group {{ $errors->has('captcha') ? 'has-error' : '' }}" style="text-align: center;">
								    <div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-size="compact" data-sitekey="6LfmCmEUAAAAAH20CTYH0Dg6LGOH7Ko7Wv1DZlO0"></div>
								</div>

								<div class="form-info clearfix">
									<a class="back" href="javascript:;">&lt; {!! trans('vox.page.register.back') !!}</a>
									<button class="btn submit-register" type="submit">
										{!! trans('vox.page.register.sign-up') !!}
									</button>
								</div>
							</div>
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