@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/dentavox-man-survey-taken.jpg') }}" alt="Dentavox man survey taken" width="550" height="524">
				</div>
				<div class="col taken-survey-description">
					<h3>{!! trans('vox.page.vox-daily-limit-reached.title') !!}</h3>
					<p>
						{!! trans('vox.page.vox-daily-limit-reached.description') !!}
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
					<h2>{{ trans('vox.page.stats.dv-blog.title') }}</h2>
					<p>{{ trans('vox.page.stats.dv-blog.description') }}</p>
					<a href="https://dentavox.dentacoin.com/blog" target="_blank" class="white-button">{{ trans('vox.page.stats.dv-blog.button') }}</a>
				</div>
				<div class="col">
					<img src="{{ url('new-vox-img/dentavox-blog-preview.png') }}" alt="Dentavox blog preview" width="500" height="351">
				</div>
			</div>
		</div>

		<div class="taken-vox-stats without-line">
			<div class="container">
				<h3 class="taken-title">{!! trans('vox.page.vox-daily-limit-reached.stats.title') !!}</h3>
				<p class="vox-stats-subtitle">{!! trans('vox.page.vox-daily-limit-reached.stats.description') !!}</p>
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