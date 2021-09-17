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

		@include('vox.template-parts.blog-wrapper')

		@include('vox.template-parts.stats-video', [
			'vox' => $vox,
			'related_voxes' => false,
			'suggested_voxes' => false,
		])

	</div>

@endsection