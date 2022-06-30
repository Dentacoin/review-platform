<div class="section-dentist-info-wrap">
	<div class="container section-dentist-info">
		<div class="flex">
			<div class="col tac">
				<img src="{{ url('img-trp/attract-new-patients.svg') }}" alt="{{ trans('trp.alt-tags.attract-new-patients') }}">
				<div class="info-padding">
					<h4>{!! nl2br(trans('trp.page.index-dentist.usp.step-1-title')) !!}</h4>
					<p>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-1-description')) !!}
					</p>
				</div>
			</div>
			<div class="col tac">
				<img src="{{ url('img-trp/stimulate-feedback.svg') }}" alt="{{ trans('trp.alt-tags.more-reviews') }}">
				<div class="info-padding"> 			
					<h4>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-2-title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-2-description')) !!}
					</p>
				</div>
			</div>
			<div class="col tac">
				<img src="{{ url('img-trp/rank-higher-google.svg') }}" alt="{{ trans('trp.alt-tags.better-google-ranking') }}">
				<div class="info-padding">
					<h4>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-3-title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-3-description')) !!}
					</p>
				</div>
				{{-- <a href="{{ getLangUrl('review-score-test') }}">
					{{ trans('trp.page.index-dentist.button-lead-magnet') }}
				</a> --}}
			</div>
			<div class="col tac">
				<img src="{{ url('img-trp/access-all-apps.svg') }}" alt="{{ trans('trp.alt-tags.better-online-reputation') }}">
				<div class="info-padding">
					<h4>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-4-title')) !!}
					</h4>
					<p>
						{!! nl2br(trans('trp.page.index-dentist.usp.step-4-description')) !!}
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="testimonials-section">
	<div class="container tac">
		<h2 class="mont">
			{!! nl2br(trans('trp.page.index-dentist.testimonial.title')) !!}
		</h2>
		<h4>
			{!! nl2br(trans('trp.page.index-dentist.testimonial.subtitle')) !!}
		</h4>
	</div>

	@if($testimonials->isNotEmpty())
    	<div class="container">
	    	<div class="flickity-testimonial">
	    		@foreach($testimonials as $testimonial)
		    		<div class="testimonial">
		    			<div class="testimonial-inner flex">
							<div>
			    				<img src="{{ $testimonial->getImageUrl() }}" alt="{{ $testimonial->alt_image_text }}">
							</div>
							<div>
								<span>{!! nl2br($testimonial->description) !!}</span>
								<p class="name">{!! nl2br($testimonial->name) !!}</p>
								<p>{!! nl2br($testimonial->job) !!}</p>
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
				{!! nl2br(trans('trp.page.index-dentist.how-works-title')) !!}
			</h2>
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">1</span>
    			<p>
    				{!! nl2br(trans('trp.page.index-dentist.step-1')) !!}
    			</p>
    		</div>
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">2</span>
    			<p>
    				{!! nl2br(trans('trp.page.index-dentist.step-2')) !!}
    			</p>
    		</div>
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">3</span>
    			<p>
    				{!! nl2br(trans('trp.page.index-dentist.step-3', [
						'link' => '<a href="https://wallet.dentacoin.com/" target="_blank">',
						'endlink' => '</a>'
					])) !!}
    			</p>
    		</div>	    			
			<div class="how-block flex flex-mobile flex-center">
    			<span class="how-number mont">4</span>
    			<p>
    				{!! nl2br(trans('trp.page.index-dentist.step-4')) !!}
    			</p>
    		</div>
			<a href="javascript::" class="blue-button button-sign-up-dentist open-dentacoin-gateway dentist-register">
				{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
			</a>
		</div>
		<div class="right">
			<img src="{{ url('img-trp/launch-listing-dentist.png') }}" alt="{{ trans('trp.alt-tags.launch-listing') }}"/>
		</div>
	</div>
</div>