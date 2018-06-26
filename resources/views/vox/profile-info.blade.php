@extends('vox')

@section('content')

	<div class="container">

		<a href="{{ getLangUrl('/') }}" class="questions-back">
			<i class="fa fa-arrow-left"></i> 
			{{ trans('vox.common.questionnaires') }}
		</a>

		<div class="col-md-3">
			@include('vox.template-parts.profile-menu')
		</div>
		<div class="col-md-9">

        	<div class="panel panel-default personal-panel">
	            <div class="panel-heading">
	                <h3 class="panel-title bold">
	                	{{ trans('vox.page.profile.title-info') }}
	                </h3>
	            </div>
            	<div class="panel-body">
	      			{!! Form::open(array('url' => getLangUrl('profile/info'), 'method' => 'post', 'class' => 'form-horizontal')) !!}
	                    {!! csrf_field() !!}

	                    @foreach( $fields as $key => $info)
	                        <div class="form-group {{ $errors->has($key) ? 'has-error' : '' }}" >
	                            <label class="col-md-3 control-label">
	                            	{{ trans('vox.page.'.$current_page.'.form-'.$key) }}
	                            </label>
                                <div class="col-md-9">
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

                		@include('front.errors')
	                    <div class="form-group">
	                        <div class="col-md-12">
	                            <button type="submit" name="update" class="btn btn-block btn-primary form-control">
	                                {{ trans('vox.page.'.$current_page.'.form-save') }} 
	                            </button>
	                        </div>
	                    </div>


	                {!! Form::close() !!}
	            </div>
	        </div>
		</div>
	</div>

@endsection