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
						{!! csrf_field() !!}

						<p>
							{{ trans('front.page.'.$current_page.'.hint') }}								
						</p>

						
						@include('front.errors')

						<div class="form-group">

							<div class="col-md-12">
							  	<div class="btn-group btn-group-justified" role="group" aria-label="...">
									<label for="radio-patient" class="btn {!! count($errors) && old('is_dentist')=='patient' ? 'btn-primary' : 'btn-default' !!}">
								    	<input type="radio" name="type" id="radio-patient" class="register-type" {!! count($errors) && !old('is_dentist') ? 'checked="checked"' : '' !!} value="patient"> 
										{{ trans('front.page.'.$current_page.'.is_patient') }}
								  	</label>
									<label for="radio-dentist" class="btn {!! count($errors) && old('is_dentist') ? 'btn-primary' : 'btn-default' !!}">
					    				<input type="radio" name="type" id="radio-dentist" class="register-type" {!! count($errors) && old('is_dentist') ? 'checked="checked"' : '' !!} value="dentist"> 
										{{ trans('front.page.'.$current_page.'.is_dentist') }}
									</label>
									<label for="radio-clinic" class="btn {!! count($errors) && old('is_dentist') ? 'btn-primary' : 'btn-default' !!}">
					    				<input type="radio" name="type" id="radio-clinic" class="register-type" {!! count($errors) && old('is_dentist') ? 'checked="checked"' : '' !!} value="clinic"> 
										{{ trans('front.page.'.$current_page.'.is_clinic') }}
									</label>
								</div>
							</div>

						</div>

						<div id="register-error" class="alert alert-warning" style="display: none;">
							{{ trans('front.page.'.$current_page.'.register-error')  }}<br/>
							<span>

							</span>
						</div>

						<div style="display: none;" id="register-div-patient">

							<div class="form-group">
							  	<div class="col-md-12">
									<p>
										{{ trans('front.page.'.$current_page.'.why-facebook') }}								
									</p>
							  	</div>
							  	<div class="col-md-12 text-center">

									<label for="read-privacy" class="reg-privacy">
										<input id="read-privacy" type="checkbox" name="read-privacy">
										{!! nl2br(trans('front.page.'.$current_page.'.agree-privacy', [
											'privacylink' => '<a href="'.getLangUrl('privacy').'">', 
											'endprivacylink' => '</a>'
										])) !!}
									</label>

									<div class="fb-button-inside" style="display: none;">
										<a href="{{ getLangUrl('register/facebook') }}" class="fb-register">
										</a>
										<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
									</div>
								</div>
							</div>

						</div>


						<div {!! count($errors) ? '' : 'style="display: none;"' !!} id="register-div">

							<div id="step-1">
								<p>
									{{ trans('front.page.'.$current_page.'.step-1-hint') }}								
								</p>


								<div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		<span id="dentist-name-label">
								  			{{ trans('front.page.'.$current_page.'.name') }}								  			
								  		</span>
								  		<span id="clinic-name-label">
								  			{{ trans('front.page.'.$current_page.'.clinic-name') }}								  			
								  		</span>
								  	</label>
								  	<div class="col-md-8">
								  		<input type="text" id="dentist-name" name="name" value="{{ old('name') }}" class="form-control">
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
										<button class="btn btn-primary btn-block" id="go-to-2" data-validator="{{ getLangUrl('register/step1') }}">
											{{ trans('front.page.'.$current_page.'.next')  }}
										</button>
									</div>
								</div>
							</div>

							<div id="step-2" style="display: none;">

								<div id="has-address" style="display: none;">
									<p>
										{{ trans('front.page.'.$current_page.'.has-address') }}								
									</p>
								  	<div id="dentist-map" style="min-height: 300px;">
								  	</div>
								  	<br/>
								</div>

							  	<div class="form-group {{ $errors->has('country_id') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.country') }} 
								  	</label>
								  	<div class="col-md-8">
								  		<select name="country_id" id="dentist-country" class="form-control country-select">
								  			@if(!$country_id)
								  				<option>-</option>
								  			@endif
								  			@foreach( $countries as $country )
								  				<option value="{{ $country->id }}" data-code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
								  			@endforeach
								  		</select>
								    </div>
								</div>
							  	<div class="form-group {{ $errors->has('city_id') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.city') }} 
								  	</label>
								  	<div class="col-md-8">
                                        {{ Form::select( 'city_id' , $country_id ? ['' => '-'] + \App\Models\City::where('country_id', $country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('front.common.select-country')] , $city_id , array('id' => 'dentist-city', 'class' => 'form-control city-select') ) }}
								    </div>
								</div>                                            

							  	<div class="form-group {{ $errors->has('zip') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.zip') }} 
								  	</label>
								  	<div class="col-md-8">
								    	<input type="text" name="zip" id="dentist-zip" class="form-control">
								    </div>
								</div>    
							  	<div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.address') }} 
								  	</label>
								  	<div class="col-md-8">
								    	<input type="text" name="address" id="dentist-address" class="form-control">
								    </div>
								</div>                                    

							  	<div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.phone') }} 
								  	</label>
								  	<div class="col-md-8">
								    	<input type="text" name="phone" id="dentist-phone" class="form-control">
								    </div>
								</div>

							  	<div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.website') }} 
								  	</label>
								  	<div class="col-md-8">
								    	<input type="text" name="website" id="dentist-website" class="form-control">
								    </div>
								</div>

								<p class="dentist-show">{{ trans('front.page.'.$current_page.'.workplace-hint') }}</p>

				                <div class="form-group dentist-show">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.workplace') }}
								  	</label>
								  	<div class="col-md-8">
								    	<div class="clinic-suggester">

				                    	<input class="form-control" autocomplete="off" type="text" id="joinclinic" name="joinclinic" />
					                    	<input type="hidden" name="joinclinicid" id="joinclinicid" />

					                    	<div class="clinic-suggests">
												<div class="loader">
													<i class="fa fa fa-circle-o-notch fa-spin fa-2x fa-fw">
													</i>
												</div>
												<div class="results">
												</div>
											</div>
					                    </div>
								    </div>
								</div>

							  	<div class="form-group {{ $errors->has('photo') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		<span class="dentist-show">
								  			{{ trans('front.page.'.$current_page.'.photo') }} 
								  		</span>
								  		<span class="clinic-show">
								  			{{ trans('front.page.'.$current_page.'.clinic-photo') }} 
								  		</span>
								  	</label>
								  	<div class="col-md-8">
								    	<label class="add-photo" for="add-avatar">
								    		<div class="photo-cta">
								    			<i class="fa fa-plus"></i>
								    			{{ trans('front.page.'.$current_page.'.photo-add') }} 
								    		</div> 
								    		<div class="loader">
								    			<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
								    		</div>
								    		<input id="add-avatar" name="image" type="file">
								    	</label>
								    </div>
									<input type="hidden" id="photo-name" name="photo" >
								</div>

							  	<div class="form-group {{ $errors->has('categories') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.categories') }} 
								  	</label>
								  	<div class="col-md-8 checkbox-group">
								    	@foreach($categories as $k => $v)
                                            <div class="form-group" >
                                                <label class="col-md-12">
                                                    {{ Form::checkbox( 'categories[]', $loop->index, is_array(old('categories')) && in_array($k, old('categories') ) , [] ) }}
                                                    {{ $v }}
                                                </label>
                                            </div>
                                        @endforeach
								    </div>
								</div>

							  	<div class="form-group {{ $errors->has('captcha') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.captcha') }} 
								  	</label>
								  	<div class="col-md-8">
								    	<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-sitekey="6LdmpjQUAAAAAMlVjnFzaKp5nyKsGcalxhS_hcDd"></div>
								    </div>
								</div>

							  	<div class="form-group {{ $errors->has('privacy') ? 'has-error' : '' }}">
								  	<label class="control-label col-md-4">
								  		{{ trans('front.page.'.$current_page.'.privacy') }} 
								  	</label>
								  	<label class="col-md-8 checkbox-group" for="privacy">
								  		<input type="checkbox" name="privacy" id="privacy" value="1" />
										{!! nl2br(trans('front.page.'.$current_page.'.agree-privacy', [
											'privacylink' => '<a href="'.getLangUrl('privacy').'">', 
											'endprivacylink' => '</a>'
										])) !!}
								    </label>
								</div>

								<div class="form-group">
									<div class="col-md-12">
										<button class="btn btn-primary btn-block" id="go-to-3" type="submit">
											{{ trans('front.page.'.$current_page.'.submit')  }}
										</button>
									</div>
								</div>
							</div>

						</div>

						<!--

						-->

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
	
	<script type="text/javascript">
		var upload_url = '{{ getLangUrl('register/upload') }}';
	</script>

@endsection