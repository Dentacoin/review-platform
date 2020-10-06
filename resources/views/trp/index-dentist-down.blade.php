<div class="testimonials-section">
	<div class="container tac">
		<h2>{!! nl2br(trans('trp.page.index-dentist.testimonial.title')) !!}</h2>
		<span>{!! nl2br(trans('trp.page.index-dentist.testimonial.subtitle')) !!}</span>
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
		{!! nl2br(trans('trp.page.index-dentist.how-works-title')) !!}
	</h2>

	<div class="clearfix mobile-flickity">
		<div class="left">
			<div class="how-block flex flex-center">
    			<span class="how-number">01</span>
    			<p>
    				{!! nl2br(trans('trp.page.index-dentist.step-1', [
						'link' => '<a href="javascript:;" class="open-dentacoin-gateway dentist-register">',
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
		<a href="javascript::" class="button button-sign-up-dentist open-dentacoin-gateway dentist-register">{!! nl2br(trans('trp.page.index-dentist.create-listing')) !!}</a>
	</div>
</div>

<div class="section-learn">
	<div class="container flex">
		<div class="col">
			<img src="{{ url('img-trp/dentacoin-patients-rely-on-only-reviews.png') }}" alt="{{ trans('trp.alt-tags.patients-rely-reviews') }}">
		</div>
		<div class="col">
    		<h2>
    			{!! nl2br(trans('trp.page.index-dentist.cta')) !!}
    		</h2>
    		<a href="javascript:;" class="button button-yellow button-sign-up-dentist open-dentacoin-gateway dentist-register">
    			{!! nl2br(trans('trp.page.index-dentist.signup')) !!}
    		</a>
    	</div>
	</div>
</div>