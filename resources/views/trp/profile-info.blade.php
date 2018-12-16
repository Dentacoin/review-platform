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
				{{ trans('vox.page.profile.info.title') }}
			</h2>
               
				@if($user->is_dentist)


					<div class="form-horizontal">
						<div class="black-line-title">
			                <h4 class="bold">
			                	Your dentist profile
			                </h4>
			            </div>

		    			<div class="form-group flex">
						  	<label class="control-label col">You can edit all your information in your public profile. Just scroll to the information you'd like to change and hit the Edit button.</label>
						</div>

						<div class="form-group clearfix">
		                    <a href="{{ $user->getLink() }}" class="btn btn-primary form-control">Open my profile</a>
						</div>
					</div>
	            	

					<form method="post" class="form-horizontal clearfix">
		  				{!! csrf_field() !!}
						<input type="hidden" name="field" value="email">
				

						<div class="black-line-title">
			                <h4 class="bold">
			                	Your email address
			                </h4>
			            </div>
		  				
		  				<div class="form-group flex">
						  	<label class="control-label col">Email address</label>
						  	<div class="flex-5">
						    	<input type="text" name="email" class="form-control" value="{{ $user->email }}" placeholder="Your email address" required>
						    </div>
						</div>
						<div class="form-group">
		                    <button type="submit" name="update" class="btn btn-primary form-control"> Update </button>
						</div>
		    			
		  			</form>

	            	@include('front.errors')

					<form action="{{ getLangUrl('profile/password') }}" method="post" class="form-horizontal clearfix">
		  				{!! csrf_field() !!}
				

						<div class="black-line-title">
			                <h4 class="bold">
			                	Your password
			                </h4>
			            </div>
		  				
		  				<div class="form-group flex">
						  	<label class="control-label col">{{ trans('vox.page.'.$current_page.'.info.change-password-current') }}</label>
						  	<div class="flex-5">
						    	<input type="password" name="cur-password" class="form-control" required>
						    </div>
						</div>
		    			<div class="form-group flex">
						  	<label class="control-label col">{{ trans('vox.page.'.$current_page.'.info.change-password-new') }}</label>
						  	<div class="flex-5">
						    	<input type="password" name="new-password" class="form-control" required>
						    </div>
						</div>
					  	<div class="form-group flex">
						  	<label class="control-label col">{{ trans('vox.page.'.$current_page.'.info.change-password-repeat') }}</label>
						  	<div class="flex-5">
						    	<input type="password" name="new-password-repeat" class="form-control" required>
						    </div>
						</div>
						<div class="form-group">
		                    <button type="submit" name="update" class="btn btn-primary form-control"> {{ trans('vox.page.'.$current_page.'.info.change-password-submit') }} </button>
						</div>
		    			
		  			</form>
				@else
	      			{!! Form::open(array('url' => getLangUrl('profile/info'), 'method' => 'post', 'class' => 'form-horizontal')) !!}

	                    {!! csrf_field() !!}
	            		
	                    @foreach( $fields as $key => $info)
	                        @if( empty($info['hide']) )
		                        <div class="form-group flex break-tablet {{ $errors->has($key) ? 'has-error' : '' }}" >
		                            <label class="col control-label">
		                            	{{ trans('vox.page.profile.info.form-'.$key) }}
		                            </label>
		                            <div class="flex-5">
		                                @if( $info['type'] == 'text')
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
		                                @elseif( $info['type'] == 'country')  
		                                    {{ Form::select( $key , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $user->$key , array('class' => 'form-control country-select') ) }}
		                                @elseif( $info['type'] == 'city')  
		                                    {{ Form::select( $key , $user->country_id ? \App\Models\City::where('country_id', $user->country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('vox.common.select-country')] , $user->$key , array('class' => 'form-control city-select') ) }}
		                                @elseif( $info['type'] == 'select')
		                                    {{ Form::select( $key , $info['values'] , $user->$key , array('class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '')  , (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple')) }}
		                                @endif
		                            </div>
		                        </div>
		                    @endif
	                    @endforeach

	                    <div class="form-group flex break-tablet {{ $errors->has($key) ? 'has-error' : '' }}" >
	                        <label class="col control-label">
	                        	{{ trans('vox.page.profile.info.form-photo') }}
	                        </label>
	                        <div class="flex-5">

								<label for="add-avatar" class="image-label" {!! $user->hasimage ? 'style="background-image: url('.$user->getImageUrl(true).')"' : '' !!}>
							    	@if( !$user->hasimage )
										<div class="centered-hack">
											<i class="fas fa-plus"></i>
											<p>
								    			Add profile photo
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
					    			{!! trans('vox.page.profile.info.photo-hint') !!}
					    			
					    		</i>
								<input type="hidden" id="photo-name" name="photo" >
								<span class="error-message" id="photo-error"></span>
								<div id="photo-upload-error" style="display: none;" class="alert alert-warning">
									{!! trans('vox.page.profile.info.photo-error') !!}
									
								</div>
	                        </div>
	                    </div>

						<script type="text/javascript">
							var upload_url = '{{ getLangUrl('profile/info/upload') }}';
						</script>

	                    <div class="form-group">
	                        <button type="submit" name="update" class="btn btn-block btn-primary form-control">
	                            {{ trans('vox.page.'.$current_page.'.info.form-save') }} 
	                        </button>
	                    </div>

	            		@include('front.errors')

	                {!! Form::close() !!}
                @endif
		</div>
	</div>

@endsection