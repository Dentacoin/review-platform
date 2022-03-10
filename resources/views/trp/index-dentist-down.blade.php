<div class="section-dentist-info-wrap">
	<div class="container section-dentist-info">
		<div class="flex">
			<div class="col tac">
				<img src="{{ url('img-trp/attract-new-patients.svg') }}" alt="{{ trans('trp.alt-tags.attract-new-patients') }}">
				<div class="info-padding">
					<h3>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}</h3>
					<p>
						Follow the blockchain trend and get noticed by digital natives who care about their dental health.
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!} --}}
					</p>
				</div>
			</div>
			<div class="col tac">
				<img src="{{ url('img-trp/stimulate-feedback.svg') }}" alt="{{ trans('trp.alt-tags.more-reviews') }}">   
				<div class="info-padding"> 			
					<h3>
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!} --}}
						Stimulate feedback
					</h3>
					<p>
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!} --}}
						Reward your patients for their honest input with motivating DCN incentives at no cost for you.
					</p>
				</div>
			</div>
			<div class="col tac">
				<img src="{{ url('img-trp/rank-higher-google.svg') }}" alt="{{ trans('trp.alt-tags.better-google-ranking') }}">
				<div class="info-padding">
					<h3>
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!} --}}
						Rank higher on Google
					</h3>
					<p>
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!} --}}
						Boost your online reputation and climb to the top of search results with our SEO-optimized platform.
					</p>
				</div>
				<a href="javascript:;" class="magnet-popup" id="open-magnet" data-url="{{ getLangUrl('lead-magnet-session') }}">
					{{-- {{ trans('trp.page.index-dentist.button-lead-magnet') }} --}}
					Get your reputation score now!
				</a>
			</div>
			<div class="col tac">
				<img src="{{ url('img-trp/access-all-apps.svg') }}" alt="{{ trans('trp.alt-tags.better-online-reputation') }}">
				<div class="info-padding">
					<h3>
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!} --}}
						Access All Apps
					</h3>
					<p>
						{{-- {!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!} --}}
						By creating your Trusted Reviews profile, you get immediate access to all Dentacoin Apps.
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="testimonials-section">
	<div class="container tac">
		<h2 class="mont">
			{{-- {!! nl2br(trans('trp.page.index-dentist.testimonial.title')) !!} --}}
			Trusted by <n>2600+</n> Dental Practices
		</h2>
		<h4>
			{{-- {!! nl2br(trans('trp.page.index-dentist.testimonial.subtitle')) !!} --}}
			Dentacoin Trusted Reviews enables dental clinics and solo practicing dentists to harness the power of patient feedback and to build better patient relations.
		</h4>
	</div>

	@if($testimonials->isNotEmpty())
    	<div class="container">
	    	<div class="flickity-testimonial">
	    		@foreach($testimonials as $testim)
		    		<div class="testimonial">
		    			<div class="testimonial-inner flex">
							<div>
			    				<img src="{{ $testim->getImageUrl() }}">
							</div>
							<div>
								<span>{!! nl2br($testim->description) !!}</span>
								<p class="name">{!! nl2br($testim->name) !!}</p>
								<p>{!! nl2br($testim->job) !!}</p>
							</div>
			    		</div>
		    		</div>
		    	@endforeach
	    	</div>
		</div>
	@endif
</div>

<div class="container section-how">
	<div class="flex">
		<div class="left">
			<h2 class="mont">
				{{-- {!! nl2br(trans('trp.page.index-dentist.how-works-title')) !!} --}}
				How to Launch Your <span class="mont">FREE</span> Listing
			</h2>
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">1</span>
    			<p>
					Create your dentist / clinic profile and wait for our verification email.
    				{{-- {!! nl2br(trans('trp.page.index-dentist.step-1', [
						'link' => '<a href="javascript:;" class="open-dentacoin-gateway dentist-register">',
						'endlink' => '</a>',
					])) !!} --}}
    			</p>
    		</div>
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">2</span>
    			<p>
    				{{-- {!! nl2br(trans('trp.page.index-dentist.step-2')) !!} --}}
					Invite your patients to leave a review straight from your profile.
    			</p>
    		</div>
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">3</span>
    			<p>
    				{{-- {!! nl2br(trans('trp.page.index-dentist.step-3')) !!} --}}
					Set up a <a href="https://wallet.dentacoin.com/" target="_blank">Dentacoin Wallet</a> and earn DCN for each verified review.
    			</p>
    		</div>	    			
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">4</span>
    			<p>
    				{{-- {!! nl2br(trans('trp.page.index-dentist.step-4', [
						'link' => '<a href="https://wallet.dentacoin.com/" target="_blank">',
						'endlink' => '</a>',
					])) !!} --}}
					Embed Trusted Reviews widget on your website or Facebook page.
    			</p>
    		</div>
			<a href="javascript::" class="blue-button button-sign-up-dentist open-dentacoin-gateway dentist-register">
				{{-- {!! nl2br(trans('trp.page.index-dentist.create-listing')) !!} --}}
				List your practice
			</a>
		</div>
		<div class="right">
			<img src="{{ url('img-trp/launch-listing-dentist.png') }}"/>
		</div>
	</div>
</div>