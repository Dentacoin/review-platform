@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;">
	</div>
	<div class="home-search-form">
		<div class="tac">
	    	<h1>Find your dentist</h1>
	    	<h2>Earn Dentacoin by Reviewing Your Dentist</h2>
	    </div>
    	<form class="front-form search-form">
    		<i class="fas fa-search"></i>
    		<input id="search-input" type="text" name="location" placeholder="Search by location or name..." autocomplete="off" />
    		<input type="submit" value="">			    		
			<div class="loader">
				<i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i>
			</div>
			<div class="results" style="display: none;">
				<div class="locations-results results-type">
					<span class="result-title">
						Locations
					</span>

					<div class="clearfix list">
					</div>
				</div>
				<div class="dentists-results results-type">
					<span class="result-title">
						Clinics / Dentists
					</span>

					<div class="clearfix list">
					</div>
				</div>
			</div>
    	</form>	
		
	</div>

	<div class="main-top">
    </div>

    <div class="flickity-oval">
	    <div class="container">
		    <div class="flickity">
		    	@foreach( $featured as $dentist )
					<a class="slider-wrapper" href="{{ $dentist->getLink() }}">
						<div class="slider-image" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
							@if($dentist->is_partner)
								<img src="{{ url('img-trp/mini-logo.png') }}"/>
							@endif
						</div>
					    <div class="slider-container">
					    	<h4>{{ $dentist->getName() }}</h4>
					    	<p>
					    		<img src="img-trp/map-pin.png">{{ $dentist->city->name }}, {{ $dentist->country->name }} 
					    		<!-- <span>(2 km away)</span> -->
					    	</p>
					    	@if( $time = $dentist->getWorkHoursText() )
					    		<p>
					    			<img src="{{ url('img-trp/open.png') }}">
					    			{!! $time !!}
					    		</p>
					    	@endif
						    <div class="ratings">
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
				    			See profile
				    		</div>
				    		<div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup">
				    			Submit review
				    		</div>
				    	</div>
					</a>
		    	@endforeach
			</div>
		</div>
	</div>

	@if(empty($user))

		<div class="gray-background">

			<div class="container">
				<div class="front-info">
					<div class="container-middle">
						<h2 class="tac">The first Blockchain-Based Platform for Trusted, Detailed & Rewarded Feedback on Dental Services!</h2>
					</div>
					<div class="flex first">
						<div class="col">
							<img src="img-trp/front-first.png">
						</div>
						<div class="col fixed-width">
							<h3>Bringing Patients Back Into Focus</h3>
							<p>
								Trusted Reviews makes sure that your voice as a patient is heard. Help your dentist improve by sharing your detailed feedback and get rewarded! Your reward comes in the form of real Dentacoin tokens, which you can spend on dental treatment at clinics in 14 countries across the world!
							</p>
							<a href="javascript:;" class="button" data-popup="popup-register">Join now</a>
						</div>
					</div>
					<div class="flex second">
						<div class="col fixed-width">
							<h3>Helping Dentists Achieve Excellence</h3>
							<p>
								Patients feedback is the most valuable asset for all service-oriented industries. Dentistry is no exception. Harness the power of up-to-date qualified feedback and see your dental practice succeeding! Your willingness to improve is rewarded with Dentacoin tokens, usable for buying dental supplies.
							</p>
							<a href="{{ getLangUrl('welcome-dentist') }}" class="button">Join as a dentist</a>
						</div>
						<div class="col">
							<img src="img-trp/front-second.png">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="front-info">
			<div class="third">
				<div class="container">
					<div class="fixed-width">
						<h3>Valuable Patient Feedback to Help Improve Global Dental Health</h3>
						<p>
							Dentacoin Trusted Reviews is the first Blockchain-based review platform on dental services, developed by the Dentacoin Foundation. It incentivizes patients (for sharing their valuable feedback) and dentists (for willing to improve their service and treatment quality) with Dentacoin (DCN) - the first cryptocurrency created for the dental industry. 
							<br/><br/>
								The Dentacoin tokens collected can be stored in a wallet, exchanged to other currencies or used to pay for dental services in multiple partner venues across the world. Check them here.
						</p>
						<div class="tac">
							<a href="javascript:;" class="button" data-popup="popup-register">Join now</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif

@endsection