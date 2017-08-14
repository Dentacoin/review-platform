@extends('front')

@section('content')

<div class="container">
	<div class="alert alert-info">
		<h2>
			{{ trans('front.page.profile.verified.title') }}
		</h2>
		{!! nl2br(trans('front.page.registration.not-confirm', ['email' => $user->email])) !!}
	</div>
</div>

@endsection