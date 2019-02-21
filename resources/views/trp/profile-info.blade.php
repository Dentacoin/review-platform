@extends('trp')

@section('content')

	<div class="blue-background"></div>

	<div class="container flex break-tablet">
		<div class="col">
			@include('trp.parts.profile-menu')
		</div>
		<div class="flex-3">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-info.png') }}" />
				{{ trans('trp.page.profile.info.title') }}
			</h2>
               
				@if($user->is_dentist)


					<div class="form-horizontal">
						<div class="black-line-title">
			                <h4 class="bold">
			                	{{ trans('trp.page.profile.info.dentist-title') }}
			                	
			                </h4>
			            </div>

		    			<div class="form-group flex">
						  	<label class="control-label col">
						  		{{ trans('trp.page.profile.info.dentist-hint') }}
						  		
						  	</label>
						</div>

						<div class="form-group clearfix">
		                    <a href="{{ $user->getLink() }}" class="btn btn-primary form-control">
		                    	{{ trans('trp.page.profile.info.dentist-open') }}
		                    	
		                    </a>
						</div>
					</div>
	            	

					<form method="post" class="form-horizontal clearfix">
		  				{!! csrf_field() !!}
						<input type="hidden" name="field" value="email">
				

						<div class="black-line-title">
			                <h4 class="bold">
			                	{{ trans('trp.page.profile.info.email-title') }}
			                	
			                </h4>
			            </div>
		  				
		  				<div class="form-group flex">
						  	<label class="control-label col">{{ trans('trp.page.profile.info.email-address') }}</label>
						  	<div class="flex-5">
						    	<input type="text" name="email" class="form-control" value="{{ $user->email }}" placeholder="{{ trans('trp.page.profile.info.email-placeholder') }}Your email address" required>
						    </div>
						</div>
						<div class="form-group">
		                    <button type="submit" name="update" class="btn btn-primary form-control"> 
		                    	{{ trans('trp.page.profile.info.email-update') }}
		                    	
		                    </button>
						</div>
		    			
		  			</form>

	            	@include('front.errors')

					<form action="{{ getLangUrl('profile/password') }}" method="post" class="form-horizontal clearfix">
		  				{!! csrf_field() !!}
				

						<div class="black-line-title">
			                <h4 class="bold">
			                	{{ trans('trp.page.'.$current_page.'.info.change-password-title') }}
			                	
			                </h4>
			            </div>
		  				
		  				<div class="form-group flex">
						  	<label class="control-label col">{{ trans('trp.page.'.$current_page.'.info.change-password-current') }}</label>
						  	<div class="flex-5">
						    	<input type="password" name="cur-password" class="form-control" required>
						    </div>
						</div>
		    			<div class="form-group flex">
						  	<label class="control-label col">{{ trans('trp.page.'.$current_page.'.info.change-password-new') }}</label>
						  	<div class="flex-5">
						    	<input type="password" name="new-password" class="form-control" required>
						    </div>
						</div>
					  	<div class="form-group flex">
						  	<label class="control-label col">{{ trans('trp.page.'.$current_page.'.info.change-password-repeat') }}</label>
						  	<div class="flex-5">
						    	<input type="password" name="new-password-repeat" class="form-control" required>
						    </div>
						</div>
						<div class="form-group">
		                    <button type="submit" name="update" class="btn btn-primary form-control"> {{ trans('trp.page.'.$current_page.'.info.change-password-submit') }} </button>
						</div>
		    			
		  			</form>
				@else
	      			{!! Form::open(array('url' => getLangUrl('profile/info'), 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal address-suggester-wrapper')) !!}

	                    {!! csrf_field() !!}
	            		
	                    @foreach( $fields as $key => $info)
	                        @if( empty($info['hide']) )
		                        <div class="form-group flex break-tablet {{ $errors->has($key) ? 'has-error' : '' }}" >
		                            <label class="col control-label">
		                            	{{ trans('trp.page.profile.info.form-'.$key) }}
		                            </label>
		                            <div class="flex-5">
		                                @if( $key == 'address')  
		                                	<div>
			                                    {{ Form::text( $key, $user->$key, array( 'autocomplete' => 'off', 'class' => 'form-control address-suggester '.($user->is_dentist ? 'full-address' : ' city-only') )) }}
			                                    <div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 200px; display: none; margin-top: 10px;">
			                                    </div>
			                                    <div class="alert alert-info geoip-confirmation" style="display: none;">
			                                    	{!! nl2br(trans('trp.common.check-address')) !!}
			                                    </div>
			                                    <div class="alert alert-warning geoip-hint" style="display: none;">
			                                    	{!! nl2br(trans('trp.common.invalid-address')) !!}
			                                    </div>
		                                    </div>
		                                @elseif( $info['type'] == 'country')  
		                                	<select class="form-control country-select" name="country_id">
		                                		<option value="">-</option>
		                                		@foreach(\App\Models\Country::get() as $country)
		                                			<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $user->$key==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
		                                		@endforeach
		                                	</select>
		                                @elseif( $info['type'] == 'text')
		                                    {{ Form::text( $key, $user->$key, array('class' => 'form-control' )) }}
		                                @elseif( $info['type'] == 'number')
		                                    {{ Form::number( $key, $user->$key, array('class' => 'form-control' )) }}
		                                @elseif( $info['type'] == 'textarea')
		                                    {{ Form::textarea( $key, $user->$key, array('class' => 'form-control' )) }}
		                                @elseif( $info['type'] == 'bool')
		                                    {{ Form::checkbox( $key, 1, $user->$key, array('class' => 'form-control' )) }}
		                                @elseif( $info['type'] == 'datepicker')
		                                    {{ Form::text( $key, !empty($user->$key) ? $user->$key->format('d.m.Y') : '' , array('class' => 'form-control datepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
		                                @elseif( $info['type'] == 'datetimepicker')
		                                    {{ Form::text( $key, !empty($user->$key) ? $user->$key->format('d.m.Y H:i:s') : '' , array('class' => 'form-control datetimepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
		                                @elseif( $info['type'] == 'select')
		                                    {{ Form::select( $key , $info['values'] , $user->$key , array('class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '')  , (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple')) }}
		                                @endif
		                            </div>
		                        </div>
		                    @endif
	                    @endforeach

	                    <div class="form-group flex break-tablet {{ $errors->has($key) ? 'has-error' : '' }}" >
	                        <label class="col control-label">
	                        	{{ trans('trp.page.profile.info.form-photo') }}
	                        </label>
	                        <div class="flex-5">

								<label for="add-avatar" class="image-label" {!! $user->hasimage ? 'style="background-image: url('.$user->getImageUrl(true).')"' : '' !!}>
							    	@if( !$user->hasimage )
										<div class="centered-hack">
											<i class="fas fa-plus"></i>
											<p>
												{{ trans('trp.page.profile.info.form-photo-add') }}
								    			
								    		</p>
										</div>
									@endif
						    		<div class="loader">
						    			<i class="fas fa-circle-notch fa-spin"></i>
						    		</div>
									<input type="file" name="image" id="add-avatar" upload-url="{{ getLangUrl('profile/info/upload') }}">
								</label>
								<input type="hidden" id="photo-name" name="photo" >

					    		<i>
					    			{!! trans('trp.page.profile.info.photo-hint') !!}
					    			
					    		</i>
								<input type="hidden" id="photo-name" name="photo" >
								<span class="error-message" id="photo-error"></span>
								<div id="photo-upload-error" style="display: none;" class="alert alert-warning">
									{!! trans('trp.page.profile.info.photo-error') !!}
									
								</div>
	                        </div>
	                    </div>

						<script type="text/javascript">
							var upload_url = '{{ getLangUrl('profile/info/upload') }}';
						</script>

	                    <div class="form-group">
	                        <button type="submit" name="update" class="btn btn-block btn-primary form-control">
	                            {{ trans('trp.page.'.$current_page.'.info.form-save') }} 
	                        </button>
	                    </div>

	            		@include('front.errors')

	                {!! Form::close() !!}
                @endif
		</div>
	</div>

@endsection