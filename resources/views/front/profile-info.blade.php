@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title">
                    {{ trans('front.page.profile.'.$current_subpage.'.title') }}
                </h1>
            </div>
            <div class="panel-body">
                <p>
                    Filling your profile data is super important because Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                </p>

                @include('front.errors')

      			{!! Form::open(array('url' => getLangUrl('profile/info'), 'method' => 'post', 'class' => 'form-horizontal')) !!}
                    {!! csrf_field() !!}

                    @foreach( $fields as $key => $info)
                        <div class="form-group {{ $errors->has($key) ? 'has-error' : '' }}" >
                            <label class="col-md-3 control-label {{ $key=='categories' || $key=='work_hours' ? 'form-control-checkboxes' : '' }} ">{{ trans('front.page.'.$current_page.'.form-'.$key) }}</label>
                            @if(!empty($info['subtype']) && $info['subtype']=='phone')
                                <div class="col-md-1 phone-code-holder">
                                    {{ $user->country ? '+'.$user->country->phone_code : '' }}
                                </div>
                                <div class="col-md-8">
                                    {{ Form::text( $key, $user->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                </div>
                            @else
                                <div class="col-md-9">
                                    @if( $info['type'] == 'text')
                                        {{ Form::text( $key, $user->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                    @elseif( $info['type'] == 'textarea')
                                        {{ Form::textarea( $key, $user->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                    @elseif( $info['type'] == 'bool')
                                        {{ Form::checkbox( $key, 1, $user->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                    @elseif( $info['type'] == 'datepicker')
                                        {{ Form::text( $key, !empty($user->$key) ? $user->$key->format('d.m.Y') : '' , array('class' => 'form-control datepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
                                    @elseif( $info['type'] == 'datetimepicker')
                                        {{ Form::text( $key, !empty($user->$key) ? $user->$key->format('d.m.Y H:i:s') : '' , array('class' => 'form-control datetimepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
                                    @elseif( $info['type'] == 'country')  
                                        {{ Form::select( $key , ['' => '-'] + \App\Models\Country::get()->pluck('name', 'id')->toArray() , $user->$key , array('class' => 'form-control country-select') ) }}
                                    @elseif( $info['type'] == 'city')  
                                        {{ Form::select( $key , $user->country_id ? \App\Models\City::where('country_id', $user->country_id)->get()->pluck('name', 'id')->toArray() : ['' => trans('front.common.select-country')] , $user->$key , array('class' => 'form-control city-select') ) }}
                                    @elseif( $info['type'] == 'select')  
                                        {{ Form::select( $key , $info['values'] , $user->$key , array('class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '') , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' , (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple')) }}
                                    @elseif( $info['type'] == 'work_hours')
                                        @for($day=1;$day<=7;$day++)
                                            <div class="form-group" >
                                                <label class="col-md-4" for="day-{{ $day }}"> 
                                                    {{ Form::checkbox( 'day-'.$day, 1, '', array( 'id' => 'day-'.$day, 'class' => 'work-hour-cb', !empty(json_decode($user->$key, true)[$day]) ? 'checked' : 'something' => 'checked' ) ) }}
                                                    {{ date('l', strtotime("Sunday +{$day} days")) }}
                                                </label>
                                                <div class="col-md-4">
                                                    {{ Form::text( 
                                                        $key.'['.$day.'][0]', 
                                                        !empty( !empty(json_decode($user->$key, true)[$day][0]) ) ? json_decode($user->$key, true)[$day][0] : '' , 
                                                        array(
                                                            'class' => 'form-control', 
                                                            'placeholder' => trans('front.page.profile.'.$current_subpage.'.work-hours-from'),
                                                            !empty(json_decode($user->$key, true)[$day]) ? 'something' : 'disabled' => 'disabled'
                                                        ) 
                                                    ) }}
                                                </div>
                                                <div class="col-md-4">
                                                    {{ Form::text( 
                                                        $key.'['.$day.'][1]', 
                                                        !empty( !empty(json_decode($user->$key, true)[$day][1]) ) ? json_decode($user->$key, true)[$day][1] : '' , 
                                                        array(
                                                            'class' => 'form-control', 
                                                            'placeholder' => trans('front.page.profile.'.$current_subpage.'.work-hours-to'),
                                                            !empty(json_decode($user->$key, true)[$day]) ? 'something' : 'disabled' => 'disabled'
                                                        ) 
                                                    ) }}
                                                </div>
                                            </div>
                                        @endfor
                                    @elseif( $info['type'] == 'checkboxes')
                                        @foreach($info['values'] as $k => $v)
                                            <div class="form-group" >
                                                <label class="col-md-12">
                                                    {{ Form::checkbox( $key.'[]', $loop->index, in_array($loop->index, $user->$key->pluck('category_id')->toArray()) , [] ) }}
                                                    {{ $v }}
                                                </label>
                                            </div>
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
            </div>
        </div>
	</div>
</div>

@endsection