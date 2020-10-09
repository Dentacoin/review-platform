@extends('vox')

@section('content')

	@include('front.errors')
	@if($phone)
		<h1 class="index-h1">{!! $title !!}</h1>
	@endif
	<div class="full">
		<div class="first-absolute">
			@if(!$phone)
				<h1 class="index-h1">{!! $title !!}</h1>
			@endif
			<h2>{!! $subtitle !!}</h2>
			<br/>
			<a class="black-button check-welcome" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!}
			</a>
		</div>
		<a href="javascript:;" class="second-absolute">
			{!! nl2br(trans('vox.page.index.more')) !!}
		</a>
	</div>

	<div class="index-swiper"></div>

	<div id="to-append"></div>
    	
@endsection