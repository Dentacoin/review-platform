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

	<div class="section-recent-surveys new-style-swiper">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.recent-surveys.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.recent-surveys.subtitle')) !!}</h2>

		<div class="swiper-container">
		    <div class="swiper-wrapper">
		    	@foreach($voxes as $survey)
			    	<div class="swiper-slide">
				    	@include('vox.template-parts.vox-taken-swiper-slider')
				    </div>
		      	@endforeach
		    </div>

		    <div class="swiper-pagination"></div>
		</div>
	</div>

	<div class="all-surveys-container container tac">
		<a class="opinion blue-button" href="{{ getLangUrl('paid-dental-surveys') }}">
			{{ trans('vox.page.index.see-all-surveys') }}
		</a>
	</div>

	<div class="check-statictics-wrapper tac">
		<div class="container">
			<p class="h2-bold">DENTAL STATISTICS</p>
			<h2>Check up-to-date market statistics to stay on top of industry trends!</h2>
			<a class="video-parent" href="{{ getLangUrl('dental-survey-stats') }}">
				<video class="inited-video video-stats" id="myVideo" playsinline autoplay muted loop video-url="{{ url('new-vox-img/stats.m4v') }}" type="video/mp4" controls=""></video>
			</a>
			<a class="video-parent-mobile" href="{{ getLangUrl('dental-survey-stats') }}">
				<video class="inited-video video-stats" id="myVideoMobile" playsinline autoplay muted loop video-url="{{ url('new-vox-img/stats-mobile.mp4') }}" type="video/mp4" controls=""></video>
			</a>
		</div>

		<div class="tac">
			<a href="{{ getLangUrl('dental-survey-stats') }}" class="blue-button more-surveys">{!! trans('vox.common.check-statictics') !!}</a>
		</div>
	</div>

	<div class="index-daily-polls tac">
		<div class="container">
			<p class="h2-bold">DAILY POLLS</p>
			<h2>Earn additional rewards by answering polls! Only the first 100 win!</h2>
			<a href="{{ getLangUrl('daily-polls') }}" class="go-polls">
				<img src="{{ url('new-vox-img/calendar-white.png') }}" alt="Dentavox daily polls calendar" width="28" height="29">
				{!! nl2br(trans('vox.daily-polls.popup.browse-polls')) !!}
			</a>
			<img class="index-poll-man" src="{{ url('new-vox-img/dentavox-daily-polls-man.png') }}" alt="Dentavox daily polls man" width="360" height="176">
		</div>
	</div>

	@include('vox.template-parts.index-part')

	<div class="section-take-surveys">
		<div class="container">
			<img src="{{ url('new-vox-img/dentavox-browse-paid-surveys-icon.png') }}" alt="Dentavox browse paid surveys icon" width="108" height="140">
			<h3>
				Browse all survey topics and start earning DCN!
			</h3>
			<a href="{{ getLangUrl('paid-dental-surveys') }}" class="white-button">
				Take surveys
			</a>
		</div>
	</div>
    	
@endsection