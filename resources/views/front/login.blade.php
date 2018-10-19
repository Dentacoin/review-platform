@extends('front')

@section('content')

	<div class="container">

			<div class="col-md-6 col-md-offset-3">

				<div class="panel panel-default">
				<div class="panel-body">
					
					<h1>
						{{ trans('front.page.'.$current_page.'.title') }}
					</h1>

					<form action="{{ getLangUrl('login') }}" method="post" class="form-horizontal">

						<div id="register-form">

							<div class="form-group">
							  	<div class="col-md-12 text-justify">
									{{ trans('front.page.'.$current_page.'.hint') }}
								</div>
							</div>
							<div class="form-group">
							  	<div class="col-md-12 text-center">
									<div class="fb-button-inside">
										<a href="{{ getLangUrl('login/facebook') }}" class="">
										</a>
										<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
									</div>
								</div>

							  	<div class="col-md-12 text-center">
									<div class="civic-button" id="register-civic-button">
										<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>
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

							  	<div class="col-md-12 text-center">
									<a class="btn btn-default" href="{{ getLangUrl('login/twitter') }}" title="{{ trans('front.page.'.$current_page.'.twitter') }}"><i class="fa fa-twitter"></i></a>
									<a class="btn btn-default" href="{{ getLangUrl('login/gplus') }}" title="{{ trans('front.page.'.$current_page.'.gplus') }}"><i class="fa fa-google-plus"></i></a>
								</div>

							</div>

							{!! csrf_field() !!}

							<div class="form-group">
							  	<label class="control-label col-md-3">
									{{ trans('front.page.'.$current_page.'.email') }}
							  	</label>
							  	<div class="col-md-9">
							    	<input type="text" name="email" value="{{ old('email') }}" class="form-control">
							    </div>
							</div>
						  	<div class="form-group">
							  	<label class="control-label col-md-3">
									{{ trans('front.page.'.$current_page.'.password') }}
								</label>
							  	<div class="col-md-9">
							    	<input type="password" name="password" class="form-control">
							    </div>
							</div>
							<div class="form-group">
							  	<label class="control-label col-md-3"></label>
							  	<label for="remember" class="col-md-9">
							    	<input id="remember" type="checkbox" name="remember" checked>
									{{ trans('front.page.'.$current_page.'.remember') }}
							  	</label>
							</div>
							<div class="form-group">
								<div class="col-md-12">
									<button class="btn btn-primary btn-block" type="submit">
										{{ trans('front.page.'.$current_page.'.submit') }}
									</button>
								</div>
							</div>

							@include('front.errors')

							<div class="form-group">
								<div class="col-md-12">
									<p class="divider">
										{{ trans('front.page.'.$current_page.'.alternative') }}
									</p>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-6">
									<a class="btn btn-default btn-block" href="{{ getLangUrl('register') }}">
										{{ trans('front.page.'.$current_page.'.register') }}											
									</a>
								</div>
								<div class="col-md-6">
					            	<a class="btn btn-default btn-block" href="{{ getLangUrl('forgot-password') }}">
					            		{{ trans('front.page.'.$current_page.'.recover') }}
					            	</a>								
								</div>
							</div>


						</div>

					</form>

				</div>
			</div>

					
		</div>
	</div>
	
@endsection