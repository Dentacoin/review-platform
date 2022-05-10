@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;"></div>

	<div class="home-section tac">
		<div class="container">
			<h1 class="mont">
				{!! nl2br(trans('trp.page.index.title')) !!}
			</h1>
			
			@include('trp.parts.search-form')
			
			@include('trp.parts.flickity-dentists', [
				'title' => 'Trusted Dentist Reviews from Real Patients',
				'subtitle' => 'Check the latest dental reviews in your location'
			])
		</div>
	</div>
	
	<div class="info-section tac">
		<div class="container">
			<h2 class="mont">The First Blockchain Platform</h2>
			<h3>For Dental Treatment Reviews Rewarding Your Feedback</h3>

			<div class="flex flex-text-center">
				<div class="info-box">
					<div class="info-icon to-append-image" data-src="https://urgent.reviews.dentacoin.com/img-trp/dentacoin-find-the-best-dentist-icon.png" data-alt="Find the best dentist on Dentacoin Trusted Reviews icon">
						{{-- {{ trans('trp.alt-tags.best-dentist') }} --}}
					</div>
					<div class="info-text">
						{{-- <h3>{!! nl2br(trans('trp.page.index.intro-title-1')) !!}</h3> --}}
						<h3>Find the best dentist</h3>
						{{-- <p>{!! nl2br(trans('trp.page.index.intro-description-1')) !!}</p> --}}
						<p>Browse dentist ratings and choose the best provider in your area.</p>
						<a href="javascript:;" class="white-button scroll-to-search">Search now</a>
						{{-- data-popup="invite-new-dentist-popup" --}}
					</div>
				</div>
				<div class="info-box">
					<div class="info-icon to-append-image" data-src="https://urgent.reviews.dentacoin.com/img-trp/dentacoin-make-your-voice-heard-icon.png" data-alt="Review your dentist on Dentacoin Trusted Reviews icon">
						{{-- {{ trans('trp.alt-tags.make-voice-heard') }} --}}
					</div>
					<div class="info-text">
						{{-- <h3>{!! nl2br(trans('trp.page.index.intro-title-2')) !!}</h3> --}}
						<h3>Review your dentist</h3>
						{{-- <p>{!! nl2br(trans('trp.page.index.intro-description-2')) !!}</p> --}}
						<p>Submit your feedback on your last dental appointment and help others.</p>
						<a href="javascript:;" class="white-button {{ !empty($user) ? '' : 'open-dentacoin-gateway patient-login' }}">Write a review</a>
					</div>
				</div>
				<div class="info-box">
					<div class="info-icon to-append-image" data-src="https://urgent.reviews.dentacoin.com/img-trp/dentacoin-get-rewarded-icon.png" data-alt="Get rewarded for your Dentacoin Trusted Reviews icon">
						{{-- {{ trans('trp.alt-tags.get-rewarded') }} --}}
					</div>
					<div class="info-text">
						{{-- <h3>{!! nl2br(trans('trp.page.index.intro-title-3')) !!}</h3> --}}
						<h3>Get rewarded</h3>
						{{-- <p>{!! nl2br(trans('trp.page.index.intro-description-3')) !!}</p> --}}
						<p>Earn DCN for sharing your genuine opinion based on your experience.</p>
						<a href="javascript:;" class="white-button {{ !empty($user) ? '' : 'open-dentacoin-gateway patient-login' }}">Start now</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="to-append"></div>

	{{-- @if(!empty($user))
		<div class="strength-parent fixed">
			@include('trp.parts.strength-scale')
		</div>
	@endif --}}

@endsection