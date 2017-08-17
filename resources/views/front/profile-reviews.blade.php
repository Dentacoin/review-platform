@extends('front')

@section('content')

<div class="container">
	<div class="col-md-3">
		@include('front.template-parts.profile-menu')
	</div>
	<div class="col-md-9">
		@include('front.template-parts.profile-reviews')
	</div>
</div>

@endsection