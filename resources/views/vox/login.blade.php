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

		<div class="container">
			<div class="col-md-3">
				<img class="image-left" src="{{ url('new-vox-img/register-dentist.png') }}">
			</div>

			<div class="col-md-9">
				<h3 class="tac">
					Login
				</h3>
				<p class="reg-desc">
					Welcome back! To access your profile and get full access to the platform, just log in using one of the options below.
				</p>

				<form action="{{ getLangUrl('login') }}" method="post" class="form-horizontal">
					{!! csrf_field() !!}

					<div class="user-type-mobile">
						<a href="javascript:;" type="reg-patients">
							I'm a patient
						</a>
						<a href="javascript:;" type="reg-dentists">
							I'm a dentist
						</a>
					</div>
					
					<div class="errors-wrapper">
						@include('front.errors')
					</div>

					<div class="reg-wrapper row clearfix">


						<div class="reg-patients col-md-6 tac">
							<h4>Users (Patients)</h4>

							<div class="fb-button-inside">
								<a href="{{ getLangUrl('login/facebook') }}" class="">
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
							<input type="hidden" id="jwtAddress" value="{{ getLangUrl('login/civic') }}" />
						</div>

						<div class="reg-dentists col-md-6">
							<h4 class="tac">Dentists</h4>

							<div id="register-error" class="alert alert-warning" style="display: none;">						
								{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
								<span></span>
							</div>

							<div class="form-group">
								<input type="text" name="email" value="{{ old('email') }}" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.email') }}">
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control" placeholder="{{ trans('front.page.'.$current_page.'.password') }}">
							</div>
							

							<div class="form-group">
								<button class="btn btn-primary btn-block" type="submit">
									{{ trans('front.page.'.$current_page.'.submit') }}
								</button>
							</div>
							
							<div class="form-group tac">
								<div class="checkbox tac">
									<label for="remember" class="active">
										<i class="far fa-square"></i>
								    	<input id="remember" type="checkbox" name="remember" class="input-checkbox" checked>
										{{ trans('front.page.'.$current_page.'.remember') }}
									</label>
								</div>
							</div>

							<div class="form-group tac">
				            	<a class="recover-text" href="{{ getLangUrl('recover-password') }}">
				            		{{ trans('front.page.'.$current_page.'.recover') }}
				            	</a>
				            </div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	
@endsection