@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;"></div>

	<div class="home-section tac">
		<div class="container">
			<h1 class="mont">
				{!! nl2br(trans('trp.page.index.title')) !!}
			</h1>
			
			@include('trp.parts.search-form')
			
			<div class="flickity-oval">
				<div class="flex space-between flex-center">
					<a href="javascript:;" class="slider-left"></a>
					<div>
						<h2 class="mont">Trusted Dentist Reviews from Real Patients</h2>
						<h3>Check the latest dental reviews in your location</h3>
					</div>
					<a href="javascript:;" class="slider-right"></a>
				</div>

				<div class="flickity">
					<div class="index-slider">
						@foreach( $featured as $dentist )
							<a class="slider-wrapper" href="{{ $dentist->getLink() }}">
								<div class="slider-inner">
									{{-- @if(!empty($user) && $user->hasReviewTo($dentist->id))
										<img class="has-review-image" src="{{ url('img-trp/patient-review.svg') }}">
									@endif --}}
									<div class="slider-image-wrapper">
										<img 
											class="slider-real-image" 
											src="{{ $dentist->getImageUrl(true) }}" 
											alt="{{ trans('trp.alt-tags.reviews-for', [
												'name' => $dentist->getNames(), 
												'location' => $dentist->getLocation() 
											]) }}" 
											width="180" 
											height="180"
										/> 
										@if($dentist->is_partner)
											<div class="partner">
												<img 
													class="tooltip-text" 
													src="{{ url('img-trp/mini-logo-white.svg') }}" 
													text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}"
												/>
												DCN Accepted
											</div>
										@endif
									</div>
									<div class="slider-container">
										<h4>{{ $dentist->getNames() }}</h4>
										<div class="p flex flex-center">
											<img src="img-trp/pin-gray.svg" width="20" height="25">
											{{ $dentist->getLocation() }}
										</div>
										<div class="ratings">
											<div class="stars">
												<div class="bar" style="width: {{ $dentist->avg_rating/5*100 }}%;">
												</div>
											</div>
											<span class="rating">
												({{ trans('trp.common.reviews-count', [ 'count' => intval($dentist->ratings)]) }})
											</span>
										</div>
										<p class="review-content">
											{{ $loop->iteration == 2 ? '“Excellent and efficient. ”' : '“David Kaplowitz genuinely cares about
											getting to bottoms of what ills you. I have...”' }}
										</p>
									</div>
								</div>
							</a>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="info-section tac">
		<div class="container">
			<h2 class="mont">The First Blockchain Platform</h2>
			<h3>For Dental Treatment Reviews Rewarding Your Feedback</h3>

			<div class="flex flex-text-center">
				<div class="info-box">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-find-the-best-dentist-icon.png') }}" alt="{{ trans('trp.alt-tags.best-dentist') }}">
					</div>
					<div class="info-text">
						<h3>{!! nl2br(trans('trp.page.index.intro-title-1')) !!}</h3>
						{{-- <p>{!! nl2br(trans('trp.page.index.intro-description-1')) !!}</p> --}}
						<p>Browse dentist ratings and choose the best provider in your area.</p>
						<a href="javascript:;" class="white-button" data-popup="invite-new-dentist-popup">Search now</a>
					</div>
				</div>
				<div class="info-box">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-make-your-voice-heard-icon.png') }}" alt="{{ trans('trp.alt-tags.make-voice-heard') }}">
					</div>
					<div class="info-text">
						{{-- <h3>{!! nl2br(trans('trp.page.index.intro-title-2')) !!}</h3> --}}
						<h3>Review your dentist</h3>
						{{-- <p>{!! nl2br(trans('trp.page.index.intro-description-2')) !!}</p> --}}
						<p>Submit your feedback on your last dental appointment and help others.</p>
						<a href="javascript:;" class="white-button">Write a review</a>
					</div>
				</div>
				<div class="info-box">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-get-rewarded-icon.png') }}" alt="{{ trans('trp.alt-tags.get-rewarded') }}">
					</div>
					<div class="info-text">
						{{-- <h3>{!! nl2br(trans('trp.page.index.intro-title-3')) !!}</h3> --}}
						<h3>Get rewarded</h3>
						{{-- <p>{!! nl2br(trans('trp.page.index.intro-description-3')) !!}</p> --}}
						<p>Earn DCN for sharing your genuine opinion based on your experience.</p>
						<a href="javascript:;" class="white-button">Start now</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="to-append"></div>

	@if(!empty($user))
		<div class="strength-parent fixed">
			@include('trp.parts.strength-scale')
		</div>
	@endif

@endsection