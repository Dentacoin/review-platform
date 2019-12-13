@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/questions-done-man.jpg') }}">
				</div>
				<div class="col taken-survey-description">
					<h3>Great! You've reached your daily limit.</h3>
					<p>
						It seems you've been on a roll today. Come back tomorrow to share your valuable opinion on other topics!
					</p>
					<div class="countdown daily-limit-reached">
						<div class="hours-countdown">
							<img src="{{ url('new-vox-img/banned-cooldown.png') }}">
							{!! trans('vox.page.bans.bans-countdown') !!}:
							<span>{{ $time_left }}</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="blog-wrapper">
			<div class="container flex">
				<div class="col">
					<h2>DENTAVOX BLOG</h2>
					<p>Check our blog for more curious statistics & infographics!</p>
					<a href="https://dentavox.dentacoin.com/blog" target="_blank" class="white-button">VISIT BLOG</a>
				</div>
				<div class="col">
					<img src="{{ url('new-vox-img/a.png') }}">
				</div>
			</div>
		</div>

		<div class="taken-vox-stats without-line">
			<div class="container">
				<h3 class="taken-title">DENTAL STATISTICS</h3>
				<p class="vox-stats-subtitle">Check up-to-date market statistics to stay on top of industry trends!</p>
				<a class="video-parent" href="{{ $vox->has_stats ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}">
					<video id="myVideo" class="video-stats" playsinline autoplay muted loop src="{{ url('new-vox-img/stats.m4v') }}" type="video/mp4" controls=""></video>
				</a>
				<a class="video-parent-mobile" href="{{ $vox->has_stats ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}">
					<video id="myVideoMobile" class="video-stats" playsinline autoplay muted loop src="{{ url('new-vox-img/stats-mobile.mp4') }}" type="video/mp4" controls=""></video>
				</a>
			</div>

			<div class="tac">
				<a href="{{ $vox->has_stats ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}" class="blue-button more-surveys">{!! trans('vox.common.check-statictics') !!}</a>
			</div>
		</div>

	</div>

@endsection