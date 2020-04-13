@extends('trp')

@section('content')

	<div class="error-wrapper">
		<div class="blue-line"></div>
		<img src="{{ url('img-trp/404.jpg') }}" alt="Dentacoin page not found">

		<div class="error-container container">
			<h2>{!! nl2br(trans('trp.page.404.title')) !!}</h2>

			<div class="index-gradient">

			    <div class="flickity-oval">
				    <div class="container">
					    <div class="flickity">
					    	@foreach( $featured as $dentist )
								<a class="slider-wrapper" href="{{ $dentist->getLink() }}">
									<div class="slider-inner">
										<div class="slider-image-wrapper">
											<div class="slider-image" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
												<img src="{{ $dentist->getImageUrl(true) }}" alt="Reviews for dentist {{ $dentist->getName() }} in {{ $dentist->city_name ? $dentist->city_name.', ' : '' }}{{ $dentist->state_name ? $dentist->state_name.', ' : '' }}{{ $dentist->country->name }}" style="display: none !important;"> 
												@if($dentist->is_partner)
													<img class="tooltip-text" src="{{ url('img-trp/mini-logo.png') }}" text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}" />
												@endif
												<!-- @if($dentist->status == 'added_approved' || $dentist->status == 'admin_imported' || $dentist->status == 'added_by_clinic_unclaimed' || $dentist->status == 'added_by_dentist_unclaimed')
													<div class="invited-dentist">{!! nl2br(trans('trp.page.user.added-by-patient')) !!}</div>
												@endif -->
											</div>
										</div>
									    <div class="slider-container">
									    	<h4>{{ $dentist->getName() }}</h4>
									    	<div class="p">
									    		<div class="img">
									    			<img src="{{ url('img-trp/map-pin.png') }}">
									    		</div>
												{{ $dentist->city_name ? $dentist->city_name.', ' : '' }}
												{{ $dentist->state_name ? $dentist->state_name.', ' : '' }} 
												{{ $dentist->country->name }} 
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
													({{ intval($dentist->ratings) }} reviews)
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
			<div class="tac">
	    		<a href="{{ getLangUrl('/') }}" class="button">{!! nl2br(trans('trp.page.404.back')) !!}</a>
	    	</div>
		</div>
	</div>

@endsection