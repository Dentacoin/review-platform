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
               
            @include('front.errors')

      			{!! Form::open(array('url' => getLangUrl('profile/info'), 'method' => 'post', 'class' => 'form-horizontal')) !!}
                    {!! csrf_field() !!}

                    @foreach( $fields as $key => $info)
                        <div class="form-group {{ $errors->has($key) ? 'has-error' : '' }}" >
                            <label class="col-md-2 control-label">
                            	{{ trans('vox.page.profile.info.form-'.$key) }}
                            </label>
                            <div class="col-md-10">
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
                    @endforeach

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