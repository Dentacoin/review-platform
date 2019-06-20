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

	<div class="section-login">


		<div class="new-auth pre-login">
			<div class="wrapper">
				<div class="inner">
					@include('front.errors')
					<h2>
						{!! trans('vox.page.auth.before-login.title') !!}
					</h2>
					<div class="flex break-mobile">
						<p>
							{!! nl2br(trans('vox.page.auth.before-login.hint')) !!}
						</p>
						<form action="{{ getLangUrl('login') }}" method="post" class="form-horizontal">
							{!! csrf_field() !!}

							<div class="modern-field alert-after">
								<input type="email" name="email" id="email" class="modern-input" autocomplete="off" value="{{ old('email') }}" readonly onfocus="this.removeAttribute('readonly');">
								<label for="email">
									<span>{{ trans('vox.page.login.email') }}</span>
								</label>
							</div>

							<div class="modern-field alert-after">
								<input type="password" name="password" id="password" class="modern-input" autocomplete="off">
								<label for="password">
									<span>{{ trans('vox.page.login.password') }}</span>
								</label>
							</div>

							<div class="form-group">
								<button class="btn btn-primary btn-block" type="submit">
									{{ trans('vox.page.login.submit') }}
								</button>
							</div>
							
							<div class="form-group tac">
								<div class="checkbox tac">
									<label for="remember-popup" class="active">
										<i class="far fa-square"></i>
								    	<input id="remember-popup" type="checkbox" name="remember" class="input-checkbox" checked>
										{{ trans('vox.page.login.remember') }}
									</label>
								</div>
							</div>

							<div class="form-group tac">
				            	<a class="recover-text" href="{{ getLangUrl('recover-password') }}">
				            		{{ trans('vox.page.login.recover') }}
				            	</a>
				            </div>
						</form>
					</div>
				</div>
				<a class="closer x">
					<i class="fas fa-times"></i>
				</a>
			</div>
		</div>

		<form action="{{ getLangUrl('new-login/facebook', null, 'https://dentavox.dentacoin.com/') }}" method="post" id="new-login-form" style="display: none;">
			{!! csrf_field() !!}
			<input type="text" name="access-token" value="">
			<button type="submit"></button>			
		</form>

		<form action="{{ getLangUrl('login/civic', null, 'https://dentavox.dentacoin.com/') }}" method="post" id="new-civic-login-form" style="display: none;">
			{!! csrf_field() !!}
			<input type="text" name="jwtToken" value="">
			<button type="submit"></button>			
		</form>

		<div class="container">
			<div class="col-md-3">
				<img class="image-left" src="{{ url('new-vox-img/register-dentist.png') }}">
			</div>

			<div class="col-md-9">
				<h3 class="tac">
					{{ trans('vox.page.login.title')  }}
				</h3>
				<p class="reg-desc">
					{{ trans('vox.page.login.subtitle')  }}
				</p>

				<form action="{{ getLangUrl('login') }}" method="post" class="form-horizontal">
					{!! csrf_field() !!}

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
								<a href="javascript:;" class="fb-login-button-new"></a>
								<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
							</div>

							<!-- <div class="fb-button-inside">
								<a href="https://dev.dentavox.dentacoin.com/en/login/facebook/{{ $workaround ? $workaround : '' }}" class="">
								</a>
								<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
							</div> -->
					
							<div class="civic-button" id="register-civic-button">
								<i class="fas fa-circle-notch fa-spin"></i>
								Continue with Civic
							</div>

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
							<input type="hidden" id="jwtAddress" value="{{ getLangUrl('login/civic') }}" />


					
							<div class="grace-button" id="register-grace-button">
								{!! trans('vox.page.login.email-pass') !!}
							</div>
						</div>

						<div class="reg-dentists col-md-6">
							<h4 class="tac">
								{{ trans('vox.common.type-dentist')  }}
							</h4>

							<div id="register-error" class="alert alert-warning" style="display: none;">						
								{{ trans('front.page.login.register-error')  }}<br/>
								<span></span>
							</div>

							<div class="modern-field alert-after">
								<input type="email" name="email" id="email-login" class="modern-input" autocomplete="off" value="{{ old('email') }}" readonly onfocus="this.removeAttribute('readonly');">
								<label for="email-login">
									<span>{{ trans('vox.page.login.email') }}</span>
								</label>
							</div>

							<div class="modern-field alert-after">
								<input type="password" name="password" id="password-login" class="modern-input" autocomplete="off">
								<label for="password-login">
									<span>{{ trans('vox.page.login.password') }}</span>
								</label>
							</div>							

							<div class="form-group">
								<button class="btn btn-primary btn-block" type="submit">
									{{ trans('vox.page.login.submit') }}
								</button>
							</div>
							
							<div class="form-group tac">
								<div class="checkbox tac">
									<label for="remember" class="active">
										<i class="far fa-square"></i>
								    	<input id="remember" type="checkbox" name="remember" class="input-checkbox" checked>
										{{ trans('vox.page.login.remember') }}
									</label>
								</div>
							</div>

							<div class="form-group tac">
				            	<a class="recover-text" href="{{ getLangUrl('recover-password') }}">
				            		{{ trans('vox.page.login.recover') }}
				            	</a>
				            </div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>


	
@endsection