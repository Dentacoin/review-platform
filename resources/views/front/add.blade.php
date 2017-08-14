@extends('front')

@section('content')

	<div class="container">
			<div class="col-md-8 col-md-offset-2">

				<div class="panel panel-default">
				<div class="panel-body">
					<h1>
						{{ trans('front.page.'.$current_page.'.title') }}
					</h1>

					@if($user)
						<p>
							{{ trans('front.page.'.$current_page.'.hint') }}
						</p>
							
	                    @include('front.errors')

	          			{!! Form::open(array( 'method' => 'post', 'class' => 'form-horizontal')) !!}
	                        {!! csrf_field() !!}

	                        @foreach( $fields as $key => $info)
	                            <div class="form-group {{ $errors->has($key) ? 'has-error' : '' }}" >
	                                <label class="col-md-4 control-label">
	                                	{{ trans('front.page.'.$current_page.'.form-'.$key) }}
	                                	@if(!empty($info['required']))
	                                		*
	                                	@endif
	                                </label>
		                            @if(!empty($info['subtype']) && $info['subtype']=='phone')
		                                <div class="col-md-1 phone-code-holder">
		                                    {{ $country_id ? '+'.\App\Models\Country::find($country_id)->phone_code  : ''}}
		                                </div>
		                                <div class="col-md-7">
		                                    {{ Form::text( $key, '', array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
		                                </div>
		                            @else
		                                <div class="col-md-8">
		                                    @if( $info['type'] == 'text')
		                                        {{ Form::text( $key, '', array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
		                                    @elseif( $info['type'] == 'textarea')
		                                        {{ Form::textarea( $key, '', array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
		                                    @elseif( $info['type'] == 'bool')
		                                        {{ Form::checkbox( $key, 1, false, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
		                                    @elseif( $info['type'] == 'datepicker')
		                                        {{ Form::text( $key, '' , array('class' => 'form-control datepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
		                                    @elseif( $info['type'] == 'datetimepicker')
		                                        {{ Form::text( $key, '' , array('class' => 'form-control datetimepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
		                                    @elseif( $info['type'] == 'country')  
		                                        {{ Form::select( $key , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $country_id , array('class' => 'form-control country-select') ) }}
		                                    @elseif( $info['type'] == 'city')  
		                                        {{ Form::select( $key , $country_id ? \App\Models\City::where('country_id', $country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('front.common.select-country')] , $city_id , array('class' => 'form-control city-select') ) }}
		                                    @elseif( $info['type'] == 'select')  
		                                        {{ Form::select( $key , $info['values'] , null , array('class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '') , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' , (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple')) }}
		                                    @elseif( $info['type'] == 'checkboxes')
		                                        @foreach($info['values'] as $k => $v)
		                                            <label class="form-control form-control-checkboxes">
		                                                {{ Form::checkbox( $key.'[]', $loop->index, false , [] ) }}
		                                                {{ $v }}
		                                            </label>
		                                        @endforeach
		                                    @endif
		                                </div>
		                            @endif
	                            </div>
	                        @endforeach

	                        <div class="form-group">
	                            <div class="col-md-12">
	                                <button type="submit" name="update" class="btn btn-block btn-primary form-control">
	                                    {{ trans('front.page.'.$current_page.'.form-save') }} 
	                                </button>
	                            </div>
	                        </div>

	                    {!! Form::close() !!}
					@else

						<p>
							{{ trans('front.page.'.$current_page.'.not-logged') }}
						</p>
						<div class="form-group">
							<div class="col-md-6">
								<a class="btn btn-primary btn-block" href="{{ getLangUrl('register') }}">
									{{ trans('front.page.'.$current_page.'.register') }}
								</a>
							</div>
							<div class="col-md-6">
								<a class="btn btn-default btn-block" href="{{ getLangUrl('login') }}">
									{{ trans('front.page.'.$current_page.'.login') }}
								</a>
							</div>
						</div>
					@endif

				</div>
			</div>
		</div>

	</div>
	
@endsection