@extends('front')

@section('content')

	<div class="container">
			<div class="col-md-6 col-md-offset-3">

				<div class="panel panel-default">
				<div class="panel-body">
					<h1>
						{{ trans('front.page.'.$current_page.'.title') }}
					</h1>

					<form action="{{ getLangUrl('register') }}" id="register-form" method="post" role="form" data-toggle="validator" class="form-horizontal">

						<p>
							{{ trans('front.page.'.$current_page.'.hint') }}								
						</p>
						
						@include('front.errors')

						<div id="register-error" class="alert alert-warning" style="display: none;">
							{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
							<span>

							</span>
						</div>

						@if($invitation_email)
							<input type="hidden" name="is_dentist" value="0"> 
						@else
							<div class="form-group">

								<div class="col-md-12">
								  	<div class="btn-group btn-group-justified" role="group" aria-label="...">
										<label for="radio-dentist" class="btn {!! count($errors) && old('is_dentist') ? 'btn-primary' : 'btn-default' !!}">
						    				<input type="radio" name="is_dentist" id="radio-dentist" class="register-type" {!! count($errors) && old('is_dentist') ? 'checked="checked"' : '' !!} value="1"> 
											{{ trans('front.page.'.$current_page.'.is_dentist') }}
										</label>
										<label for="radio-patient" class="btn {!! count($errors) && !old('is_dentist') ? 'btn-primary' : 'btn-default' !!}">
									    	<input type="radio" name="is_dentist" id="radio-patient" class="register-type" {!! count($errors) && !old('is_dentist') ? 'checked="checked"' : '' !!} value="0"> 
											{{ trans('front.page.'.$current_page.'.is_patient') }}
									  	</label>
									</div>
								</div>

							</div>
						@endif


						<div {!! count($errors) || $invitation_email ? '' : 'style="display: none;"' !!} id="register-div">

							<div class="form-group">
							  	<div class="col-md-12">
									<p>
										{{ trans('front.page.'.$current_page.'.why-facebook') }}								
									</p>
							  	</div>
							  	<div class="col-md-12 text-center">
									<a class="btn register-social btn-default" title="{{ trans('front.page.'.$current_page.'.facebook') }}" href="{{ getLangUrl('register/facebook') }}">
										<i class="fa fa-facebook"></i> Register with Facebook
									</a>
								</div>
							</div>

						</div>

						<div class="form-group">
							<div class="col-md-12">
								<p class="divider">
									{{ trans('front.page.'.$current_page.'.alternative') }}
								</p>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6">
								<a class="btn btn-default btn-block" href="{{ getLangUrl('login') }}">
									{{ trans('front.page.'.$current_page.'.login') }}
								</a>
							</div>
							<div class="col-md-6">
				            	<a class="btn btn-default btn-block" href="{{ getLangUrl('forgot-password') }}">
				            	{{ trans('front.page.'.$current_page.'.recover') }}
				            	</a>
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>

	</div>
	
@endsection