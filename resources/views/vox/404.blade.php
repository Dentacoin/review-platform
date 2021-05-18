@extends('vox')

@section('content')

	<div class="error-wrapper container tac">
		<img src="{{ url('new-vox-img/404.jpg') }}" alt="Dentavox page not found" width="1078" height="505">

		<h2>{!! nl2br(trans('vox.page.404.title')) !!}</h2>

		<div class="tac">
    		<a href="{{ getLangUrl('/') }}" class="button">{!! nl2br(trans('vox.page.404.button')) !!}</a>
    	</div>
	</div>

@endsection