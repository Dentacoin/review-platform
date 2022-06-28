@extends('admin')

@section('content')

    <h1 class="page-header">
        {{ trans('admin.page.'.$current_page.'.edit-question') }}
    </h1>

    @if(!empty($error))
        <div class="alert alert-danger">
            @foreach($error_arr as $key => $value)
                {!! $value !!} <br/>
            @endforeach
        </div>
    @endif
    <!-- end page-header -->

    <div class="row">
        @include('admin.parts.vox-question')
    </div>

@endsection