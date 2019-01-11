@extends('vox')

@section('content')

	<div class="container">

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

			<h2 class="page-title">
				<img src="{{ url('new-vox-img/profile-info.png') }}" />
				{{ trans('vox.page.profile.info.title') }}
			</h2>
               

      			{!! Form::open(array('url' => getLangUrl('profile/info'), 'method' => 'post', 'class' => 'form-horizontal address-suggester-wrapper')) !!}
                    {!! csrf_field() !!}
            		
            		@include('front.errors')

                    @foreach( $fields as $key => $info)
                        <div class="form-group {{ $errors->has($key) ? 'has-error' : '' }}" >
                            <label class="col-md-2 control-label">
                            	{{ trans('vox.page.profile.info.form-'.$key) }}
                            </label>
                            <div class="col-md-10">
	                            @if( $key == 'address')  
	                            	<div>
	                                    {{ Form::text( $key, $user->$key, array( 'autocomplete' => 'off', 'class' => 'form-control address-suggester '.($user->is_dentist ? 'full-address' : ' city-only') )) }}
	                                    <div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 200px; display: none; margin-top: 10px;">
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
                    @endforeach

                    <div class="form-group {{ $errors->has($key) ? 'has-error' : '' }}" >
                        <label class="col-md-2 control-label">
                        	{{ trans('vox.page.profile.info.form-photo') }}
                        </label>
                        <div class="col-md-10">
					    	<label class="add-photo" for="add-avatar" {!! $user->hasimage ? 'style="background-image: radial-gradient( rgba(255,255,255,1), rgba(255,255,255,1), rgba(255,255,255,0) ), url('.$user->getImageUrl(true).')"' : '' !!} >
					    		<div class="photo-cta">
					    			<i class="fa fa-plus"></i>
					    			@if( $user->hasimage )
					    				{!! trans('vox.page.profile.info.photo-edit') !!}
					    			@else
					    				{!! trans('vox.page.profile.info.photo-add') !!}
					    			@endif
					    		</div> 
					    		<div class="loader">
					    			<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>
					    		</div>
					    		<input id="add-avatar" name="image" type="file">
					    	</label>
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
                        <div class="col-md-12">
                            <button type="submit" name="update" class="btn btn-block btn-primary form-control">
                                {{ trans('vox.page.'.$current_page.'.info.form-save') }} 
                            </button>
                        </div>
                    </div>


                {!! Form::close() !!}


                @if($user->is_dentist)
					<form action="{{ getLangUrl('profile/password') }}" method="post" class="form-horizontal">
		  				{!! csrf_field() !!}
		  				
		  				<div class="form-group">
						  	<label class="control-label col-md-2">{{ trans('vox.page.'.$current_page.'.info.change-password-current') }}</label>
						  	<div class="col-md-10">
						    	<input type="password" name="cur-password" class="form-control" required>
						    </div>
						</div>
		    			<div class="form-group">
						  	<label class="control-label col-md-2">{{ trans('vox.page.'.$current_page.'.info.change-password-new') }}</label>
						  	<div class="col-md-10">
						    	<input type="password" name="new-password" class="form-control" required>
						    </div>
						</div>
					  	<div class="form-group">
						  	<label class="control-label col-md-2">{{ trans('vox.page.'.$current_page.'.info.change-password-repeat') }}</label>
						  	<div class="col-md-10">
						    	<input type="password" name="new-password-repeat" class="form-control" required>
						    </div>
						</div>
						<div class="form-group">
							<div class="col-md-12">
		                        <button type="submit" name="update" class="btn btn-primary form-control"> {{ trans('vox.page.'.$current_page.'.info.change-password-submit') }} </button>
							</div>
						</div>
		    			
		  			</form>
	  			@endif

		</div>
	</div>

@endsection