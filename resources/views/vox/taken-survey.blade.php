@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/taken-vox-man.jpg') }}">
				</div>
				<div class="col taken-survey-description">
					<h3>Oops! You've been here before...</h3>
					<p>
						You have already taken “<span>{{ $vox->title }}</span>" survey. No worries: We have plenty of other opportunities for you!
					</p>
				</div>
			</div>
		</div>

		<div class="related-wrap">
			@if(!empty($related_voxes))
				<div class="section-recent-surveys">
					<h3 class="taken-title">Related surveys</h3>

					<div class="swiper-container">
					    <div class="swiper-wrapper">
					    	@foreach($related_voxes as $survey)
						      	@include('vox.template-parts.vox-taken-swiper-slider')
					      	@endforeach
					    </div>

					    <div class="swiper-pagination"></div>
					</div>
				</div>
			@else
				<div class="section-recent-surveys">
					<h3 class="taken-title">PICK YOUR NEXT SURVEY</h3>

					<div class="swiper-container">
					    <div class="swiper-wrapper">
					    	@foreach($suggested_voxes as $survey)
						      	@include('vox.template-parts.vox-taken-swiper-slider')
					      	@endforeach
					    </div>

					    <div class="swiper-pagination"></div>
					</div>

					<div class="tac">
						<a href="{{ getLangUrl('/') }}" class="blue-button more-surveys">See all surveys</a>
					</div>
				</div>
			@endif

		</div>

		@if($vox->has_stats)
			<div class="taken-vox-stats {!! empty($related_voxes) ? 'without-line' : '' !!}">
				<div class="container">
					<h3 class="taken-title">Curious to see survey results?</h3>
					<a href="{{ $vox->getStatsList() }}">
						<video id="myVideo" playsinline autoplay muted loop src="{{ url('new-vox-img/stats.m4v') }}" type="video/mp4" controls=""></video>
					</a>
				</div>

				<div class="tac">
					<a href="{{ $vox->getStatsList() }}" class="blue-button more-surveys">Check stats</a>
				</div>
			</div>
		@endif

		@if(!empty($related_voxes))
			<div class="suggested-wrap">
				<div class="section-recent-surveys">
					<h3 class="taken-title">PICK YOUR NEXT SURVEY</h3>

					<div class="swiper-container">
					    <div class="swiper-wrapper">
					    	@foreach($suggested_voxes as $survey)
						      	@include('vox.template-parts.vox-taken-swiper-slider')
					      	@endforeach
					    </div>

					    <div class="swiper-pagination"></div>
					</div>

					<div class="tac">
						<a href="{{ getLangUrl('/') }}" class="blue-button more-surveys">See all surveys</a>
					</div>
				</div>
			</div>
		@endif

	</div>

@endsection