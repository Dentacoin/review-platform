@extends('trp')

@section('content')

	<div class="error-wrapper container">
		<div class="flex flex-center">
			<div class="error-image">
				<img src="{{ url('img-trp/not-found.png') }}"/>
			</div>
			<div>
				<h1 class="mont">
					{{-- {!! nl2br(trans('trp.page.404.title')) !!} --}}
					Sorry! We couldn't find the page you requested.
				</h1>
			</div>
		</div>

		<div class="error-container">
			@include('trp.parts.flickity-dentists', [
				'subtitle' => 'Were you looking for a dentist near you?',
			])
			<div class="tac">
	    		<a href="{{ getLangUrl('/') }}" class="blue-button">
					{{-- {!! nl2br(trans('trp.page.404.back')) !!} --}}
					BACK TO HOME PAGE	
				</a>
	    	</div>
		</div>
	</div>

@endsection