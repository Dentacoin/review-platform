@extends('vox')

@section('content')

	<div class="taken-survey-wrapper">
		<div class="container">
			<div class="flex">
				<div class="col">
					<img class="taken-survey-image" src="{{ url('new-vox-img/dentavox-man-taken-survey.jpg') }}" alt="Dentavox man taken survey">
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

		@if($suggested_voxes->count() || !empty($related_voxes))
			<div class="related-wrap">
				@if(!empty($related_voxes))
					<div class="section-recent-surveys">
						<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.related-surveys-title') !!}</h3>

						<div class="swipe-cont {{ count($related_voxes) > 2 ? 'swiper-container' : '' }}">
					    	<div class="swiper-wrapper {{ count($related_voxes) <= 2 ? 'flex' : '' }}">
						    	@foreach($related_voxes as $survey)
							    	<div class="swiper-slide" survey-id="{{ $survey->id }}">
								      	@include('vox.template-parts.vox-taken-swiper-slider')
								    </div>
						      	@endforeach
						    </div>

						    <div class="swiper-pagination"></div>
						</div>
					</div>
				@else
					<div class="section-recent-surveys">
						<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.next-surveys-title') !!}</h3>

						<div class="swipe-cont {{ $suggested_voxes->count() > 2 ? 'swiper-container' : '' }}">
					    	<div class="swiper-wrapper {{ $suggested_voxes->count() <= 2 ? 'flex' : '' }}">
						    	@foreach($suggested_voxes as $survey)
							    	<div class="swiper-slide" survey-id="{{ $survey->id }}">
								      	@include('vox.template-parts.vox-taken-swiper-slider')
								    </div>
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
		@endif

		<div class="taken-vox-stats {!! empty($related_voxes) || $suggested_voxes->isEmpty() ? 'without-line' : '' !!}">
			<div class="container">
				<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.survey-stats-title') !!}</h3>
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
		@if(!empty($related_voxes) && $suggested_voxes->count())
			<div class="suggested-wrap">
				<div class="section-recent-surveys new-style-swiper">
					<h3 class="taken-title">{!! trans('vox.page.taken-questionnaire.next-surveys-title') !!}</h3>

					<div class="swipe-cont {{ $suggested_voxes->count() > 2 ? 'swiper-container' : '' }}">
					    <div class="swiper-wrapper {{ $suggested_voxes->count() <= 2 ? 'flex' : '' }}">
					    	@foreach($suggested_voxes as $survey)
						    	<div class="swiper-slide" survey-id="{{ $survey->id }}">
							      	@include('vox.template-parts.vox-taken-swiper-slider')
							    </div>
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