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
		<p class="h2-bold">GENUINE RESEARCH</p>
		<h2>Results from 73+ million answers</h2>
	</div>
	<div class="flex flex-center flex-text-center break-mobile">
		<div class="col">
			<img src="{{ url('new-vox-img/buy-detailed-reports.png') }}">
			<h3>BUY DETAILED REPORT</h3>
			<p>Suitable for researchers, media, <br/> dental professionals, suppliers <br/> and students who seek detailed <br/> data and analysis.</p>
			<a class="blue-button" href="{{ getLangUrl('welcome-survey') }}">
				Coming soon
			</a>
		</div>
		<div class="col">
			<img src="{{ url('new-vox-img/browse-free-stats.png') }}">
			<h3>BROWSE FREE STATS</h3>
			<p>Every week we publish free, <br/> downloadable charts based <br/> on DentaVox surveys. No strings <br/> attached - just sign up and take a look! </p>
			<a class="blue-button" href="{{ getLangUrl('dental-survey-stats') }}">
				See stats
			</a>
		</div>
		<div class="col">
			<img src="{{ url('new-vox-img/read-our-blog.png') }}">
			<h3>READ OUR BLOG</h3>
			<p>Curious blitz stats, oral health <br/> myths and facts, dental <br/> infographics… There is so much <br/> content you can read and share <br/> on social media! </p>
			<a class="blue-button" href="https://dentavox.dentacoin.com/blog/" target="_blank">
				See blog
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
		<p class="h2-bold">WHEREVER YOU ARE</p>
		<h2>Get DentaVox mobile app!</h2>
		<div class="flex flex-text-center">
			<a class="mobile-app-button google-play" href="https://play.google.com/store/apps/details?id=com.dentacoin.dentavox" target="_blank"></a>
			@if(false)
				<a class="mobile-app-button app-store" href="https://play.google.com/store/apps/details?id=com.dentacoin.dentavox" target="_blank"></a>
			@endif
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
			<p class="h2-bold">READY TO START?</p>
			<h2>Choose a survey and earn DCN!</h2>
			<a class="blue-button new-style with-arrow" href="{{ getLangUrl('welcome-survey') }}">
				{!! nl2br(trans('vox.page.index.start')) !!} <img src="{{ url('new-vox-img/white-arrow-right.svg') }}">
			</a>
		</div>
	</div>
</div>