@extends('vox')

@section('content')

	@include('front.errors')
	<div class="full">
		<div class="first-absolute">
			<h1 class="index-h1">{!! $title !!}</h1>
			<h2>{!! $subtitle !!}</h2>
			<br/>
			<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!}
			</a>
		</div>
		<a href="javascript:;" class="second-absolute">
			{!! nl2br(trans('vox.page.index.more')) !!}
		</a>
	</div>

	<div class="section-recent-surveys new-style-swiper index-swiper">
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
				<video class="video-stats"  onplaying="disableVideoControls();" id="myVideo" playsinline autoplay muted loop src="{{ url('new-vox-img/stats.m4v') }}" type="video/mp4" controls=""></video>
			</a>
			<a class="video-parent-mobile" href="{{ getLangUrl('dental-survey-stats') }}">
				<video class="video-stats" onplaying="disableVideoControls();" id="myVideoMobile" playsinline autoplay muted loop src="{{ url('new-vox-img/stats-mobile.mp4') }}" type="video/mp4" controls=""></video>
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
				<img src="{{ url('new-vox-img/calendar-white.png') }}">
				{!! nl2br(trans('vox.daily-polls.popup.browse-polls')) !!}
			</a>
			<img class="index-poll-man" src="{{ url('new-vox-img/index-poll-man.png') }}">
		</div>
	</div>

	<div class="section-work">
		<div class="container">

			<p class="h2-bold">{!! nl2br(trans('vox.page.index.how-works.title')) !!}</p>
			<h2>{!! nl2br(trans('vox.page.index.how-works.subtitle')) !!}</h2>

			<p class="work-desc">
				{!! nl2br(trans('vox.page.index.how-works.description')) !!}			
			</p>

			<div class="flex">
				<div class="col tac" style="{{ $user ? 'margin-left: 12%' : '' }}">
					<div class="image-wrapper warm-image">
						<img src="{{ url('new-vox-img/warm-up.png') }}">
					</div>
					<div>
						<h4>
							1. {!! nl2br(trans('vox.page.index.how-works.1.title')) !!}
						</h4>
						<p>
							{!! nl2br(trans('vox.page.index.how-works.1.content')) !!}
						</p>
					</div>
				</div>
				@if(!$user)
					<div class="col tac">
						<div class="image-wrapper sign-image">
							<img src="{{ url('new-vox-img/sign-up.png') }}">
						</div>
						<div>
							<h4>
								2. {!! nl2br(trans('vox.page.index.how-works.2.title')) !!}
							</h4>
							<p>
								{!! nl2br(trans('vox.page.index.how-works.2.content')) !!}
							</p>
						</div>
					</div>
				@endif
				<div class="col tac">
					<div class="image-wrapper grab-image">
						<img src="{{ url('new-vox-img/grab-reward.png') }}">
					</div>
					<div>
						<h4>
							{{ $user ? '2' : '3' }}. 
							{!! nl2br(trans('vox.page.index.how-works.3.title')) !!}
						</h4>
						@if($user)
							<p>
								{!! nl2br(trans('vox.page.index.how-works.3.content-logged')) !!}
							</p>
						@else
							<p>
								{!! nl2br(trans('vox.page.index.how-works.3.content')) !!}
							</p>
						@endif
					</div>
				</div>
				<div class="col tac">
					<div class="image-wrapper no-image">
						<img src="{{ url('new-vox-img/take-surveys.png') }}">
					</div>
					<div>
						<h4>
							{{ $user ? '3' : '4' }}. 
							{!! nl2br(trans('vox.page.index.how-works.4.title')) !!}
						</h4>
						<p>
							{!! nl2br(trans('vox.page.index.how-works.4.content')) !!}
						</p>
					</div>
				</div>
			</div>

			<div class="row tac">
				<div class="col">
					<a class="black-button" href="{{ getLangUrl('welcome-survey') }}">
						{!! nl2br(trans('vox.page.index.start')) !!}
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="section-reasons">
		<div class="container">
			<p class="h2-bold">{!! nl2br(trans('vox.page.index.reasons.title')) !!}</p>
			<h2>{!! nl2br(trans('vox.page.index.reasons.subtitle')) !!}</h2>

			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>01</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.1.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.1.content')) !!}
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>02</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.2.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.2.content',[
							"link" => '<a href="https://dentacoin.com/partner-network" target="_blank">',
							"endlink" => '</a>'
						])) !!}						
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>03</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.3.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.3.content')) !!}
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>04</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.4.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.4.content')) !!}
					</p>
				</div>
			</div>
			<div class="reasons-wrap flex flex-center">
				<div class="col reason-number">
					<div>05</div>
				</div>
				<div class="col reason-title">
					<h4>{!! nl2br(trans('vox.page.index.reasons.5.title')) !!}</h4>
				</div>
				<div class="col reason-desc">
					<p>
						{!! nl2br(trans('vox.page.index.reasons.5.content')) !!}
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="section-take-surveys">
		<div class="container">
			<img src="{{ url('new-vox-img/checklist-light.png') }}">
			<h3>
				Browse all survey topics and start earning DCN!
			</h3>
			<a href="{{ getLangUrl('paid-dental-surveys') }}" class="white-button">
				Take surveys
			</a>
		</div>
	</div>

    	
@endsection