@extends('front')

@section('content')

	<div class="container">
			<div class="col-md-6 col-md-offset-3">

				<div class="panel panel-default">
				<div class="panel-body">
					<h1>
						{{ trans('front.page.'.$current_page.'.title') }}
					</h1>

					<form action="{{ getLangUrl('register') }}" method="post" role="form" data-toggle="validator" class="form-horizontal">

						<p>
							{{ trans('front.page.'.$current_page.'.hint') }}								
						</p>
						
						@include('front.errors')

						<div class="form-group">

							<div class="col-md-12">
							  	<div class="btn-group btn-group-justified" role="group" aria-label="...">
									<button type="button" class="btn {!! old('is_dentist') ? 'btn-primary' : 'btn-default' !!}">
										<label for="radio-dentist">
						    				<input type="radio" name="is_dentist" id="radio-dentist" class="register-type" {!! old('is_dentist') ? 'checked="checked"' : '' !!} value="1"> 
											{{ trans('front.page.'.$current_page.'.is_dentist') }}
										</label>
									</button>
									<button type="button" class="btn {!! count($errors) && !old('is_dentist') ? 'btn-primary' : 'btn-default' !!}">
										<label for="radio-patient">
									    	<input type="radio" name="is_dentist" id="radio-patient" class="register-type" {!! count($errors) && !old('is_dentist') ? 'checked="checked"' : '' !!} value="0"> 
											{{ trans('front.page.'.$current_page.'.is_patient') }}
									  	</label>
									</button>
								</div>
							</div>

						</div>


						<div {!! count($errors) ? '' : 'style="display: none;"' !!} id="register-form">

							<div class="form-group">
							  	<div class="col-md-12 text-center">
									<a class="btn register-social btn-default" title="{{ trans('front.page.'.$current_page.'.facebook') }}" href="{{ getLangUrl('register/facebook') }}"><i class="fa fa-facebook"></i></a>
									<a class="btn register-social btn-default" href="{{ getLangUrl('register/twitter') }}" title="{{ trans('front.page.'.$current_page.'.twitter') }}"><i class="fa fa-twitter"></i></a>
									<a class="btn register-social btn-default" href="{{ getLangUrl('register/gplus') }}" title="{{ trans('front.page.'.$current_page.'.gplus') }}"><i class="fa fa-google-plus"></i></a>
								</div>
							</div>

								{!! csrf_field() !!}

								<div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.name') }}
								  	</label>
								  	<div class="col-md-8">
								  		<input type="text" name="name" value="{{ old('name') }}" class="form-control">
								    </div>
								</div>
								<div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.email')  }}
								  	</label>
								  	<div class="col-md-8">
								    	<input type="email" name="email" value="{{ old('email') }}" class="form-control">
								    </div>
								</div>
							  	<div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.password')  }}
								  	</label>
								  	<div class="col-md-8">
								    	<input type="password" name="password" class="form-control">
								    </div>
								</div>
							  	<div class="form-group {{ $errors->has('password-repeat') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.password-repeat') }} 
								  	</label>
								  	<div class="col-md-8">
								    	<input type="password" name="password-repeat" class="form-control">
								    </div>
								</div>
								<div class="form-group">
									<div class="col-md-12">
										<button class="btn btn-primary btn-block" type="submit">
											{{ trans('front.page.'.$current_page.'.submit')  }}
										</button>
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