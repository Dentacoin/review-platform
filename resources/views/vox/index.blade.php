@extends('vox')

@section('content')

	@include('front.errors')
	<div class="full">
		<div class="first-absolute">
			<h1 class="index-h1">{!! $title !!}</h1>
			<h2>{!! $subtitle !!}</h2>
			<br/>
			<img class="mobile-home-image" src="{{ url('new-vox-img/dv-home-mobilee.jpg') }}">
			<a class="blue-button new-style with-arrow" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!} <img src="{{ url('new-vox-img/white-arrow-right.svg') }}">
			</a>
		</div>
		<img class="blue-circle" src="{{ url('new-vox-img/blue-circle-corner.png') }}"/>
	</div>

	<div class="section-recent-surveys new-style-swiper">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.recent-surveys.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.recent-surveys.subtitle')) !!}</h2>
	</div>

	<div id="to-append"></div>
    	
@endsection