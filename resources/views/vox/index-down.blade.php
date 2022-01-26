<div class="section-recent-surveys new-style-swiper">
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

<div class="all-surveys-container tac">
	<a class="opinion blue-button new-style" href="{{ getLangUrl('paid-dental-surveys') }}">
		{{ trans('vox.page.index.see-all-surveys') }}
	</a>

	<img src="{{ url('new-vox-img/left-bubbles.png') }}">
</div>

@include('vox.template-parts.index-part')

<div class="genuine-reports-wrapper index-container">
	<div class="tac">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.genuine-research.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.genuine-research.subtitle')) !!}</h2>
	</div>
	<div class="flex flex-center flex-text-center break-mobile">
		<div class="col">
			<img src="{{ url('new-vox-img/buy-detailed-reports.png') }}">
			<h3>{!! nl2br(trans('vox.page.index.genuine-research.1.title')) !!}</h3>
			<p>{!! nl2br(trans('vox.page.index.genuine-research.1.description')) !!}</p>
			<a class="blue-button gray-button" href="javascript:;">
				{!! nl2br(trans('vox.page.index.genuine-research.1.button')) !!}
			</a>
		</div>
		<div class="col">
			<img src="{{ url('new-vox-img/browse-free-stats.png') }}">
			<h3>{!! nl2br(trans('vox.page.index.genuine-research.2.title')) !!}</h3>
			<p>{!! nl2br(trans('vox.page.index.genuine-research.2.description')) !!}</p>
			<a class="blue-button" href="{{ getLangUrl('dental-survey-stats') }}">
				{!! nl2br(trans('vox.page.index.genuine-research.2.button')) !!}
			</a>
		</div>
		<div class="col">
			<img src="{{ url('new-vox-img/read-our-blog.png') }}">
			<h3>{!! nl2br(trans('vox.page.index.genuine-research.3.title')) !!}</h3>
			<p>{!! nl2br(trans('vox.page.index.genuine-research.3.description')) !!}</p>
			<a class="blue-button" href="https://dentavox.dentacoin.com/blog/" target="_blank">
				{!! nl2br(trans('vox.page.index.genuine-research.3.button')) !!}
			</a>
		</div>
	</div>
</div>

<div class="get-app-wrapper tac flex flex-center flex-text-center break-mobile">
	<div class="col phone-col">
		<img class="phone" src="{{ url('new-vox-img/phone-left.png') }}">
	</div>
	<div class="col mobile-info-col">
		<img src="{{ url('new-vox-img/app-logo.png') }}">
		<p class="h2-bold">{!! nl2br(trans('vox.page.index.mobile-app.title')) !!}</p>
		<h2>{!! nl2br(trans('vox.page.index.mobile-app.subtitle')) !!}</h2>
		<div class="flex flex-text-center">
			<a class="mobile-app-button google-play" href="https://play.google.com/store/apps/details?id=com.dentacoin.dentavox" target="_blank"></a>
			<a class="mobile-app-button app-store" href="https://apps.apple.com/mm/app/dentavox-surveys/id1538575449" target="_blank"></a>
		</div>
	</div>
	<div class="col phone-col">
		<img class="phone" src="{{ url('new-vox-img/phone-right.png') }}">
	</div>

	<img class="white-wave" src="{{ url('new-vox-img/white-wave2.png') }}" />
</div>

<div class="ready-to-start-wrapper">
	<div class="flex flex-center break-mobile">
		<div class="col">
			<img src="{{ url('new-vox-img/before-footer-img.png') }}">
		</div>
		<div class="col tal">
			<p class="h2-bold">{!! nl2br(trans('vox.page.index.start.title')) !!}</p>
			<h2>{!! nl2br(trans('vox.page.index.start.subtitle')) !!}</h2>
			<a class="blue-button new-style with-arrow" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!} <img src="{{ url('new-vox-img/white-arrow-right.svg') }}">
			</a>
		</div>
	</div>
</div>