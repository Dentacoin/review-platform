@extends('trp')

@section('content')

	<div class="error-wrapper">
		<div class="blue-line"></div>
		<img src="{{ url('img-trp/404.jpg') }}" alt="{{ trans('trp.alt-tags.404') }}">

		<div class="error-container container">
			<h2>{!! nl2br(trans('trp.page.404.title')) !!}</h2>

			<div class="index-gradient">

			    <div class="flickity-oval">
				    <div class="container">
				    	<a href="javascript:;" class="slider-left"></a>
				    	<a href="javascript:;" class="slider-right"></a>
					    <div class="flickity">
					    	<div class="index-slider">
						    	@foreach( $featured as $dentist )
									<a class="slider-wrapper" href="{{ $dentist->getLink() }}">
										<div class="slider-inner">
											@if(!empty($user) && $user->hasReviewTo($dentist->id))
												<img class="has-review-image" src="{{ url('img-trp/patient-review.svg') }}">
											@endif
											<div class="slider-image-wrapper">
												<img 
													class="slider-real-image" 
													src="{{ $dentist->getImageUrl(true) }}" 
													alt="{{ trans('trp.alt-tags.reviews-for', [ 
														'name' => $dentist->getNames(), 
														'location' => $dentist->getLocation() 
													]) }}"
												/> 
												@if($dentist->is_partner)
													<img class="tooltip-text" src="{{ url('img-trp/mini-logo.png') }}" text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}" />
												@endif
											</div>
										    <div class="slider-container">
										    	<h4>{{ $dentist->getNames() }}</h4>
										    	<div class="p">
										    		<div class="img">
										    			<img src="img-trp/map-pin.png">
										    		</div>
													{{ $dentist->getLocation() }}
										    		<!-- <span>(2 km away)</span> -->
										    	</div>
										    	@if( $time = $dentist->getWorkHoursText() )
										    		<div class="p">
										    			<div class="img">
											    			<img src="{{ url('img-trp/open.png') }}">
											    		</div>
											    		<span>
										    				{!! $time !!}
										    			</span>
										    		</div>
										    	@endif
											    <div class="ratings average">
													<div class="stars">
														<div class="bar" style="width: {{ $dentist->avg_rating/5*100 }}%;">
														</div>
													</div>
													<span class="rating">
														({{ trans('trp.common.reviews-count', [ 'count' => intval($dentist->ratings)]) }})
													</span>
												</div>
										    </div>
									    	<div class="flickity-buttons clearfix">
									    		<div>
									    			{!! nl2br(trans('trp.common.see-profile')) !!}				    			
									    		</div>
									    		<div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup">
									    			{!! nl2br(trans('trp.common.submit-review')) !!}				    			
									    		</div>
									    	</div>
									    </div>
									</a>
						    	@endforeach
						    </div>
						</div>
					</div>
				</div>
			</div>
			<div class="tac">
	    		<a href="{{ getLangUrl('/') }}" class="button">{!! nl2br(trans('trp.page.404.back')) !!}</a>
	    	</div>
		</div>
	</div>

@endsection