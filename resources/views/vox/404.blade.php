@extends('vox')

@section('content')

	<div class="error-wrapper container tac">
		<img src="{{ url('new-vox-img/404.jpg') }}" alt="Dentavox page not found">

		<h2>Sorry, Page Was Not Found.</h2>

		<div class="tac">
    		<a href="{{ getLangUrl('/') }}" class="button">Back to home page</a>
    	</div>
	</div>

@endsection