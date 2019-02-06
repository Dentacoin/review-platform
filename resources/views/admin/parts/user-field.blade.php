@if( $key == 'type')
    {{ Form::select( $key , $info['values'] , ($item->is_dentist ? ( $item->is_clinic ? 'clinic' : 'dentist' ) : 'patient') , array(
        'class' => 'form-control',
        'style' => ''.(!empty($info['multiple']) ? 'height: 200px;' : '')
    )) }}
@elseif( $info['type'] == 'password')
    {{ Form::text( $key, '', array('class' => 'form-control', 'placeholder' => 'Enter a new password to change the existing' )) }}
@elseif( $info['type'] == 'text')
    {{ Form::text( $key, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
    @if($key=='fb_id' && $item->$key)
        <a href="https://facebook.com/{{ $item->$key }}" target="_blank">Open FB profile</a>
    @endif
@elseif( $info['type'] == 'textarea')
    {{ Form::textarea( $key, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
@elseif( $info['type'] == 'bool')
    {{ Form::checkbox( $key, 1, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
@elseif( $info['type'] == 'datepicker')
    {{ Form::text( $key, !empty($item->$key) ? $item->$key->format('d.m.Y') : '' , array('class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
@elseif( $info['type'] == 'datetimepicker')
    {{ Form::text( $key, !empty($item->$key) ? $item->$key->format('Y.m.d H:i:s') : '' , array('class' => 'form-control datetimepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
@elseif( $info['type'] == 'country')  
    {{ Form::select( $key , \App\Models\Country::get()->pluck('name', 'id')->toArray() , $item->$key , array('class' => 'form-control country-select') ) }}
@elseif( $info['type'] == 'city')  
    {{ Form::select( $key , $item->country_id ? \App\Models\City::where('country_id', $item->country_id)->get()->pluck('name', 'id')->toArray() : [] , $item->$key , array('class' => 'form-control city-select') ) }}
@elseif( $info['type'] == 'avatar')
    @if($item->hasimage)
        <a class="thumbnail" href="{{ $item->getImageUrl() }}" target="_blank">
            <img src="{{ $item->getImageUrl(true) }}">
        </a>
        <a class="btn btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/deleteavatar') }}" onclick="return confirm('{{ trans('admin.common.sure') }}')">
            <i class="fa fa-remove"></i> {{ trans('admin.page.'.$current_page.'.delete-avatar') }}
        </a>
    @else
        <div class="alert alert-info">
            {{ trans('admin.page.'.$current_page.'.no-avatar') }}
        </div>
    @endif
@elseif( $info['type'] == 'select')  
    {{ Form::select( $key , $info['values'] , $item->$key , array(
        'class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '') , 
        (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' , 
        (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple',
        'style' => ''.(!empty($info['multiple']) ? 'height: 200px;' : '')
    )) }}
@endif