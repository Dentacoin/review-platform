@extends('trp')

@section('content')

	<div class="black-overflow" style="display: none;">
	</div>
	<div class="index-gradient green-gradient">
		<div class="home-search-form main-box">
			<div class="tac">
		    	<p class="index-big-title">
		    		{!! nl2br(trans('trp.page.index.title')) !!}
		    	</p>
		    	<h1 class="index-main-title">
		    		{!! nl2br(trans('trp.page.index.subtitle')) !!}
		    	</h1>
		    </div>
		    @include('trp.parts.search-form')
			
		</div>

		<div class="main-top">
	    </div>

	    @if(!empty($country_id))
		    <div class="container">
			    <div class="flickity-dentists-form">
			    	<img class="black-filter" src="{{ url('img-trp/map-pin.png') }}"> Dentists {{ !empty($city_cookie) || !empty($city_id) || (!empty($user) && !empty($user->city_name)) ? 'near' : 'in' }}: 
			    	<a href="javascript:;" data-popup="change-dentist-popup" class="current-city">
			    		{{ !empty($city_cookie) ? $city_cookie['city_name'] : (!empty($city_id) ? App\Models\City::find($city_id)->name : (!empty($user) && !empty($user->city_name) ? $user->city_name : (!empty($country_id) ? App\Models\Country::find($country_id)->name : null))) }}
			    		<i class="fas fa-caret-down"></i>
			    	</a>
			    </div>
			</div>
		@endif

	    <div class="flickity-oval">
		    <div class="container">
			    <div class="flickity">
			    	@foreach( $featured as $dentist )
						<a class="slider-wrapper" href="{{ $dentist->getLink() }}">
							<div class="slider-inner">
								<div class="slider-image-wrapper">
									<div class="slider-image" style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
										@if($dentist->is_partner)
											<img class="tooltip-text" src="{{ url('img-trp/mini-logo.png') }}" text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}" />
										@endif
										@if($dentist->status == 'added_approved' || $dentist->status == 'admin_imported')
											<div class="invited-dentist">{!! nl2br(trans('trp.page.user.added-by-patient')) !!}</div>
										@endif
									</div>
								</div>
							    <div class="slider-container">
							    	<h4>{{ $dentist->getName() }}</h4>
							    	<div class="p">
							    		<div class="img">
							    			<img src="img-trp/map-pin.png">
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


	@if(empty($user))

		<div class="index-invite-dentist">
			<div class="container">
				<div class="flex flex-mobile">
					<div class="col">
						<img src="{{ url('img-trp/index-dentist.png') }}">
					</div>
					<div class="col">
						<h2>{!! nl2br(trans('trp.page.invite.title')) !!}</h2>
						<!-- <h3>{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3> -->
						<h3>Fill in their details below and we will invite them to claim their profile on Trusted Reviews!</h3>
						<a href="javascript:;" data-popup="popup-register" class="button button-yellow button-sign-up-patient button-want-to-add-dentist">Add your dentist</a>
					</div>
				</div>
			</div>
		</div>

		<div class="info-section">
			<div class="container">
				<h2 class="gbb">{!! nl2br(trans('trp.page.index.hint')) !!}</h2>

				<div class="info-box flex flex-mobile">
					<div class="info-icon">
						<img src="{{ url('img-trp/index-icon-1.png') }}">
					</div>
					<div class="info-text">
						<h3>Find The Best Dentist</h3>
						<p>Search for a dental practice by name or area location. Filter by specialty, compare ratings and read reviews from real patients to make an informed choice.</p>
					</div>
				</div>
				<div class="info-box flex flex-mobile">
					<div class="info-icon">
						<img src="{{ url('img-trp/index-icon-2.png') }}">
					</div>
					<div class="info-text">
						<h3>Make Your Voice Heard</h3>
						<p>Trusted Reviews gives your a platform to share detailed feedback for every aspect of your experience as a patient. Help your dentist improve by providing а valuable insight!</p>
					</div>
				</div>
				<div class="info-box flex flex-mobile">
					<div class="info-icon">
						<img src="{{ url('img-trp/index-icon-3.png') }}">
					</div>
					<div class="info-text">
						<h3>Get Rewarded For Your Contribution</h3>
						<p>Your reward comes in the form of real Dentacoin (DCN) cryptocurrency which you can spend on dental treatment at clinics in 20 countries across the world!</p>
					</div>
				</div>
				<div class="tac">
					<a href="javascript:;" data-popup="popup-register" class="button button-sign-up-patient">Sign up</a>
				</div>
			</div>
		</div>

		<div class="add-practice">
			<div class="container">
				<div class="flex flex-mobile">
					<div class="col">
						<div class="practice-image">
							<img class="pc-practice-img" src="{{ url('img-trp/index-rated-dentist.png') }}">
						</div>
					</div>
					<div class="col">
						<h2>Are you a first-rate dentist?</h2>
						<div class="mobile-practice-img">
							<img src="{{ url('img-trp/index-rated-dentist.png') }}">
						</div>
						<p class="practice-subtitle">Grow your practice with the power of patient reviews.</p>
						<p>• Attract and build trust with new patients</p>
						<p>• Get to the top of search results and reach more patients</p>
						<p>• Learn from patient feedback and achieve excellence </p>
						<div class="tac-tablet">
							<a href="{{ getLangUrl('welcome-dentist') }}" class="button button-yellow">
								Add your practice
							</a>
						</div>
					</div>
				</div>
			</div>

		</div>

		<div class="front-info">
			<div class="third">
				<div class="container">
					<div class="how-works">
						<div class="fixed-width">
							<h2>
								{!! nl2br(trans('trp.page.index.usp-3-title')) !!}
							</h2>
							<p>
								<!-- {!! nl2br(trans('trp.page.index.usp-3-content')) !!} -->
								Dentacoin Trusted Reviews is the first Blockchain-based review platform on dental services, developed by the Dentacoin Foundation. It incentivizes patients (for sharing their valuable  feedback) and dentists (for willing to improve their service and treatment quality) with Dentacoin (DCN) - the first cryptocurrency created for the dental industry. <br/><br/>

	 							The Dentacoin tokens collected can be stored in a wallet, exchanged to other currencies or used to pay for dental services in multiple partner venues across the world. Check them here.
							</p>
							<div class="tac">
								<a href="javascript:;" class="button button-white button-sign-up-patient" data-popup="popup-register">
									<!-- {!! nl2br(trans('trp.page.index.join-now')) !!} -->
									Join now
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	@elseif(!empty($user) && !$user->is_dentist)
		<div class="index-invite-dentist patient-invite">
			<div class="container">
				<img src="{{ url('img-trp/index-dentist.png') }}">
			</div>
		</div>

		<div class="invite-new-dentist-wrapper white-invite">

			<div class="invite-new-dentist-titles">
				<h2>{!! nl2br(trans('trp.page.invite.title')) !!}</h2>
				<!-- <h3>{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3> -->
				<h3 class="gbb">Fill in their details below and we will invite them to claim their profile on Trusted Reviews!</h3>
			</div>

			<div class="colorfull-wrapper">
				@include('trp.parts.invite-new-dentist-form')
			</div>
		</div>
	@endif

	@if(!empty($user))
		<div class="strength-parent fixed">
			@include('trp.parts.strength-scale')
		</div>
	@endif

	<div class="popup fixed-popup" id="change-dentist-popup">
		<div class="popup-inner inner-white">
			<div class="popup-pc-buttons">
				<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
			</div>

			<div class="popup-mobile-buttons">
				<a href="javascript:;" class="close-popup">< back</a>
			</div>
			<h2>
				<img src="{{ url('img-trp/pin-gray.png') }}">
				<!-- {!! nl2br(trans('trp.popup.add-team-popup.title')) !!} -->
				City of Residence
			</h2>

			<h4 class="popup-title tac">
				<!-- {!! nl2br(trans('trp.popup.add-team-popup.subtitle')) !!} -->
				Do you want to see dentists in another city?
			</h4>

			{!! Form::open(array('method' => 'post', 'id' => 'search-dentists-city') ) !!}
				{!! csrf_field() !!}
				<div class="address-suggester-wrapper">
					<div class="modern-field alert-after">
						<input type="text" name="dentists-city" id="dentist-city" class="modern-input address-suggester city-dentist" autocomplete="off">
						<label for="dentist-city">
							<span>City:</span>
						</label>
					</div>

					@if(!empty($user))
						<label class="checkbox-label" for="change-city" >
							<input type="checkbox" class="special-checkbox" id="change-city" name="change-city"/>
							<i class="far fa-square"></i>
							Save this as my current location
						</label>
					@endif
				</div>

				<div class="alert alert-warning" style="display: none; margin-top: 20px;">
					The city field is required.
				</div>
				<div class="tac">
					<button type="submit" class="button">Search</button>
				</div>
			{!! Form::close() !!}
		</div>
	</div>

@endsection