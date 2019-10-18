@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/taken-vox-man.jpg') }}">
				</div>
				<div class="col taken-survey-description">
					<h3>{!! trans('vox.page.taken-questionnaire.title') !!}</h3>
					<p>
						{!! trans('vox.page.taken-questionnaire.description', [
							'title' => '<span>'.$vox->title.'</span>'
						]) !!}
					</p>
				</div>
			</div>
		</div>

		<div class="related-wrap">
			@if(!empty($related_voxes))
				<div class="section-recent-surveys">
					<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.related-surveys-title') !!}</h3>

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
					<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.next-surveys-title') !!}</h3>

					<div class="swiper-container">
					    <div class="swiper-wrapper">
					    	@foreach($suggested_voxes as $survey)
						      	@include('vox.template-parts.vox-taken-swiper-slider')
					      	@endforeach
					    </div>

					    <div class="swiper-pagination"></div>
					</div>

					<div class="tac">
						<a href="{{ getLangUrl('/') }}" class="blue-button more-surveys">{!! trans('vox.page.taken-questionnaire.see-surveys') !!}</a>
					</div>
				</div>
			@endif

		</div>

		@if($vox->has_stats)
			<div class="taken-vox-stats {!! empty($related_voxes) ? 'without-line' : '' !!}">
				<div class="container">
					<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.survey-stats-title') !!}</h3>
					<a href="{{ $vox->getStatsList() }}">
						<video id="myVideo" playsinline autoplay muted loop src="{{ url('new-vox-img/stats.m4v') }}" type="video/mp4" controls=""></video>
					</a>
				</div>

				<div class="tac">
					<a href="{{ $vox->getStatsList() }}" class="blue-button more-surveys">{!! trans('vox.common.check-statictics') !!}</a>
				</div>
			</div>
		@endif

		@if(!empty($related_voxes))
			<div class="suggested-wrap">
				<div class="section-recent-surveys">
					<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.next-surveys-title') !!}</h3>

					<div class="swiper-container">
					    <div class="swiper-wrapper">
					    	@foreach($suggested_voxes as $survey)
						      	@include('vox.template-parts.vox-taken-swiper-slider')
					      	@endforeach
					    </div>

					    <div class="swiper-pagination"></div>
					</div>

					<div class="tac">
						<a href="{{ getLangUrl('/') }}" class="blue-button more-surveys">{!! trans('vox.page.taken-questionnaire.see-surveys') !!}</a>
					</div>
				</div>
			</div>
		@endif

	</div>

@endsection