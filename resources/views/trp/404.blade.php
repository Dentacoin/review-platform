@extends('trp')

@section('content')

	<div class="error-wrapper container">
		<div class="flex flex-center">
			<div class="error-image">
				<img src="{{ url('img-trp/not-found.png') }}"/>
			</div>
			<div>
				<h1 class="mont">
					{!! nl2br(trans('trp.page.404.title')) !!}
				</h1>
			</div>
		</div>

		<div class="error-container">
			@include('trp.parts.flickity-dentists', [
				'subtitle' => trans('trp.page.404.flickity.subtitle'),
			])
			<div class="tac">
	    		<a href="{{ getLangUrl('/') }}" class="blue-button">
					{!! nl2br(trans('trp.page.404.back')) !!}
				</a>
	    	</div>
		</div>
	</div>

@endsection