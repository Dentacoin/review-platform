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
			    		{{ !empty($city_cookie) ? $city_cookie['city_name'] : (!empty($city_id) ? $current_city : (!empty($user) && !empty($user->city_name) ? $user->city_name : $current_country)) }}
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
										<!-- @if($dentist->status == 'added_approved' || $dentist->status == 'admin_imported')
											<div class="invited-dentist">{!! nl2br(trans('trp.page.user.added-by-patient')) !!}</div>
										@endif -->
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
						<h3>{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3>
						<a href="javascript:;" data-popup="popup-register" class="button button-yellow button-sign-up-patient button-want-to-add-dentist">{!! nl2br(trans('trp.page.invite.add-dentist')) !!}</a>
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
						<h3>{!! nl2br(trans('trp.page.index.intro-title-1')) !!}</h3>
						<p>{!! nl2br(trans('trp.page.index.intro-description-1')) !!}</p>
					</div>
				</div>
				<div class="info-box flex flex-mobile">
					<div class="info-icon">
						<img src="{{ url('img-trp/index-icon-2.png') }}">
					</div>
					<div class="info-text">
						<h3>{!! nl2br(trans('trp.page.index.intro-title-2')) !!}</h3>
						<p>{!! nl2br(trans('trp.page.index.intro-description-2')) !!}</p>
					</div>
				</div>
				<div class="info-box flex flex-mobile">
					<div class="info-icon">
						<img src="{{ url('img-trp/index-icon-3.png') }}">
					</div>
					<div class="info-text">
						<h3>{!! nl2br(trans('trp.page.index.intro-title-3')) !!}</h3>
						<p>{!! nl2br(trans('trp.page.index.intro-description-3')) !!}</p>
					</div>
				</div>
				<div class="tac">
					<a href="javascript:;" data-popup="popup-register" class="button button-sign-up-patient">{!! nl2br(trans('trp.page.index.intro-button')) !!}</a>
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
						<h2>{!! nl2br(trans('trp.page.index.first-rated-dentist.title')) !!}</h2>
						<div class="mobile-practice-img">
							<img src="{{ url('img-trp/index-rated-dentist.png') }}">
						</div>
						<p class="practice-subtitle">{!! nl2br(trans('trp.page.index.first-rated-dentist.subtitle')) !!}</p>
						<p>
							{!! nl2br(trans('trp.page.index.first-rated-dentist.description')) !!}
						</p>
						<div class="tac-tablet">
							<a href="{{ getLangUrl('welcome-dentist') }}" class="button button-yellow">
								{!! nl2br(trans('trp.page.index.first-rated-dentist.button')) !!}
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
								{!! nl2br(trans('trp.page.index.usp-3-content')) !!}
							</p>
							<div class="tac">
								<a href="javascript:;" class="button button-white button-sign-up-patient" data-popup="popup-register">
									{!! nl2br(trans('trp.page.index.usp.join-now')) !!}
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
				<h3 class="gbb">{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3>
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
				{!! nl2br(trans('trp.popup.change-dentist-popup.title')) !!}
			</h2>

			<h4 class="popup-title tac">
				{!! nl2br(trans('trp.popup.change-dentist-popup.subtitle')) !!}
			</h4>

			{!! Form::open(array('method' => 'post', 'id' => 'search-dentists-city') ) !!}
				{!! csrf_field() !!}
				<div class="address-suggester-wrapper">
					<div class="modern-field alert-after">
						<input type="text" name="dentists-city" id="dentist-city" class="modern-input address-suggester city-dentist" autocomplete="off">
						<label for="dentist-city">
							<span>{!! nl2br(trans('trp.popup.change-dentist-popup.city')) !!}:</span>
						</label>
					</div>

					@if(!empty($user))
						<label class="checkbox-label" for="change-city" >
							<input type="checkbox" class="special-checkbox" id="change-city" name="change-city"/>
							<i class="far fa-square"></i>
							{!! nl2br(trans('trp.popup.change-dentist-popup.save-location')) !!}
						</label>
					@endif
				</div>

				<div class="alert alert-warning" style="display: none; margin-top: 20px;">
					{!! nl2br(trans('trp.popup.change-dentist-popup.error')) !!}
				</div>
				<div class="tac">
					<button type="submit" class="button">{!! nl2br(trans('trp.popup.change-dentist-popup.search')) !!}</button>
				</div>
			{!! Form::close() !!}
		</div>
	</div>

@endsection