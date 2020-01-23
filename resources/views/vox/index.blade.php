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
				<img src="{{ url('new-vox-img/calendar-white.png') }}" alt="Dentavox daily polls calendar">
				{!! nl2br(trans('vox.daily-polls.popup.browse-polls')) !!}
			</a>
			<img class="index-poll-man" src="{{ url('new-vox-img/dentavox-daily-polls-man.png') }}" alt="Dentavox daily polls man">
		</div>
	</div>

	@include('vox.template-parts.index-part')

	<div class="section-take-surveys">
		<div class="container">
			<img src="{{ url('new-vox-img/dentavox-browse-paid-surveys-icon.png') }}" alt="Dentavox browse paid surveys icon">
			<h3>
				Browse all survey topics and start earning DCN!
			</h3>
			<a href="{{ getLangUrl('paid-dental-surveys') }}" class="white-button">
				Take surveys
			</a>
		</div>
	</div>

	<div class="popup login-register" id="login-register-popup">
	<div class="wrapper">
		<div class="inner vox-not-logged">
			<h2>
				{{ trans('vox.page.questionnaire.not-logged-title') }}
				
			</h2>

			<div class="flex break-mobile">
				<div class="col">
					<img src="{{ url('new-vox-img/vox-not-logged-register.png') }}" />
					<div class="flex flex-column">
						<h3>
							{{ trans('vox.page.questionnaire.not-logged-register-title') }}
						</h3>
						<p class="flex-1">
							{{ trans('vox.page.questionnaire.not-logged-register-content') }}
						</p>
						<a class="btn reg-but" href="{{ getLangUrl('welcome-survey') }}">
							{{ trans('vox.page.questionnaire.not-logged-register-button') }}
						</a>
					</div>
				</div>
				<div class="col">
					<img src="{{ url('new-vox-img/vox-not-logged-login.png') }}" />
					<div class="flex flex-column">
						<h3>
							{{ trans('vox.page.questionnaire.not-logged-login-title') }}
						</h3>
						<p class="flex-1">
							{{ trans('vox.page.questionnaire.not-logged-login-content') }}
						</p>
						<a class="btn" href="{{ getLangUrl('login') }}">
							{{ trans('vox.page.questionnaire.not-logged-login-button') }}
						</a>
					</div>
				</div>
			</div>

		</div>
		<a class="closer x">
			<i class="fas fa-times"></i>
		</a>
	</div>
</div>

    	
@endsection