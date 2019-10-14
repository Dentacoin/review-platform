@extends('trp')

@section('content')

	<div class="welcome-dentist-section">
		<div class="signin-top">
	    	<!-- <span class="h2 with-margin">
	    		{{ trans('trp.page.index-dentist.title') }}
	    		
	    	</span> -->
	    	<h1>
	    		Grow your practice with Trusted Reviews' Dental Directory
	    	</h1>

	    	<p>
	    		<!-- {!! nl2br(trans('trp.page.index-dentist.subtitle')) !!} -->
	    		“Showcase your expertise with up-to-date, qualified and detailed reviews from verified patients and see your dental practice succeed!”
	    	</p>

			<div class="ratings biggest">
				<div class="stars">
					<div class="bar" style="width: 100%;">
					</div>
				</div>
			</div>

			<div class="tac button-wrap">
				<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>

			@if($unsubscribed)
				<div class="alert alert-info">
					{{ trans('trp.page.index-dentist.unsubscribed') }}
				</div>
			@endif

	    </div>

	    <div class="signin-form-wrapper">
	    	<img src="{{ url('img-trp/dentist.png') }}">
	    	<div class="container clearfix">
	    		<form class="signin-form tablet-fixes">

					<div class="form-inner">
						<div class="modern-field">
							<input type="email" name="email" id="dentist-mail" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="dentist-mail">
								<span>{{ trans('trp.page.index-dentist.email') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password" id="dentist-pass" class="modern-input" autocomplete="off">
							<label for="dentist-pass">
								<span>{{ trans('trp.page.index-dentist.password') }}</span>
							</label>
						</div>
						
						<div class="modern-field">
							<input type="password" name="password-repeat" id="dentist-pass-repeat" class="modern-input" autocomplete="off">
							<label for="dentist-pass-repeat">
								<span>{{ trans('trp.page.index-dentist.repeat-password') }}</span>
							</label>
						</div>

						<div class="tac">
							<input type="submit" value="{{ trans('trp.page.index-dentist.signup') }}" class="button button-sign-up-dentist">
						</div>
					</div>

					<p class="have-account">
						{!! nl2br(trans('trp.page.index-dentist.have-account', [
							'link' => '<a href="javascript:;" data-popup="popup-login">',
							'endlink' => '</a>',
						])) !!}					
					</p>

	    		</form>
	    	</div>
	    </div>
	</div>

    <div class="container section-dentist-info">
    	<h2 class="tac">
    		{!! nl2br(trans('trp.page.index-dentist.usp-title')) !!}
    	</h2>

    	<div class="flex">
    		<div class="col tac">
    			<img src="{{ url('img-trp/index-dentist-1.png') }}">
    			<div class="info-padding">
	    			<h3>Attract new patients</h3>
	    			<p>Let patients hear from other patients. Over 80% of people trust online recommendations as much as personal ones.</p>
	    		</div>
    		</div>
    		<div class="col tac">
    			<img src="{{ url('img-trp/index-dentist-2.png') }}">   
    			<div class="info-padding"> 			
	    			<h3>Get more reviews</h3>
	    			<p>Motivate your patients to contribute with DCN rewards for their feedback provided by Dentacoin at no cost for you.</p>
	    		</div>
    		</div>
    	</div>

    	<div class="flex">
    		<div class="col tac">
    			<img src="{{ url('img-trp/index-dentist-3.png') }}">
    			<div class="info-padding">
	    			<h3>Benefit from high ranking in Google search</h3>
	    			<p>Take advantage of TrustedReviews SEO optimized platform to dominate Google search results.</p>
	    		</div>
    		</div>
    		<div class="col tac">
    			<img src="{{ url('img-trp/index-dentist-4.png') }}">
    			<div class="info-padding">
	    			<h3>Strengthen your online reputation</h3>
	    			<p>Attract potential patients with enhanced Trusted Reviews profile which highlights your expertise.</p>
	    		</div>
    		</div>
    	</div>

    	<div class="tac button-wrap">
			<a href="javascript:;" class="button button-sign-up-dentist" data-popup="popup-register">
    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
    		</a>
    	</div>

    	<!-- <div class="clearfix">
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/grow-icon.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!}
		    		</p>
	    		</div>
    		</div>
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/expertise.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!}
		    		</p>
	    		</div>
    		</div>
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/ranking.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!}
		    		</p>
	    		</div>
    		</div>
			<div class="flex flex-bottom">
				<div class="col col-icon">
	    			<img src="{{ url('img-trp/present.png') }}">
	    		</div>
	    		<div class="col">
	    			<h3>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!}
	    			</h3>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!}
	    			</p>
	    		</div>
    		</div>
    	</div> -->
    </div>

    <div class="testimonials-section">
    	<div class="container tac">
    		<h2>Trusted by over 1850 dental practices</h2>
    		<span>Trusted Reviews by Dentacoin enables dental clinics and independent dental practices to harness the power of patient feedback and to build stronger dentist-patient relations.</span>
    	</div>

    	@if($testimonials->isNotEmpty())
	    	<div class="container">
		    	<div class="flickity-testimonial">
		    		@foreach($testimonials as $testim)
			    		<div class="testimonial">
			    			<div class="testimonial-inner">
				    			<img src="{{ $testim->getImageUrl() }}">
				    			<h4>{!! nl2br($testim->description) !!}</h4>
				    			<p class="name">{!! nl2br($testim->name) !!}</p>
				    			<p>{!! nl2br($testim->job) !!}</p>
				    		</div>
			    		</div>
			    	@endforeach
		    	</div>
			</div>
		@endif

    </div>

    <div class="container section-how">

    	<h2 class="tac">
    		<span>How it Works?</span> <br/>
    		Create a FREE Listing in <span>6</span> Simple Steps
    		
    		<!-- {!! nl2br(trans('trp.page.index-dentist.how-works-title', [
				'firstblue' => '<span class="h2">',
				'secondblue' => '<span class="h1">',
				'endblue' => '</span>',
			])) !!} -->
    	</h2>


    	<div class="clearfix mobile-flickity">
    		<div class="left">
    			<div class="how-block flex flex-center">
	    			<span class="how-number">01</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-1', [
							'link' => '<a href="javascript:;" data-popup="popup-register">',
							'endlink' => '</a>',
						])) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">02</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-2')) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">03</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-3')) !!}
	    				
	    			</p>
	    		</div>
    		</div>
    		<div class="right">		    			
    			<div class="how-block flex flex-center">
	    			<span class="how-number">04</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-4', [
							'link' => '<a href="https://wallet.dentacoin.com/" target="_blank">',
							'endlink' => '</a>',
						])) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">05</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-5')) !!}
	    				
	    			</p>
	    		</div>
    			<div class="how-block flex flex-center">
	    			<span class="how-number">06</span>
	    			<p>
	    				{!! nl2br(trans('trp.page.index-dentist.step-6')) !!}
	    				
	    			</p>
	    		</div>
    		</div>
    	</div>

    	<div class="tac">
    		<a href="javascript::" class="button button-sign-up-dentist" data-popup="popup-register">Create listing</a>
    	</div>
    </div>

    <div class="section-learn">
    	<div class="container flex">
    		<div class="col">
    			<img src="{{ url('img-trp/statistick-bubble.png') }}">
    		</div>
    		<div class="col">
	    		<h2>
	    			{!! nl2br(trans('trp.page.index-dentist.cta')) !!}
	    			
	    		</h2>
	    		<a href="javascript:;" class="button button-yellow button-sign-up-dentist" data-popup="popup-register">
	    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
	    		</a>
	    	</div>
    	</div>
    </div>
@endsection