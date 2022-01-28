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

	    @if(!empty($_COOKIE['functionality_cookies']) && !empty($country_id))
		    <div class="container">
			    <div class="flickity-dentists-form">
			    	<img class="black-filter" src="{{ url('img-trp/map-pin.png') }}"> {{ !empty($city_cookie) || !empty($city_id) || (!empty($user) && !empty($user->city_name)) ? trans('trp.page.index.dentists.near') : trans('trp.page.index.dentists.in') }}: 
			    	<a href="javascript:;" data-popup="change-dentist-popup" class="current-city">
			    		{{ $current_city }}
			    		<img class="caret-down" src="{{ url('img/caret-white-down.png') }}"/>
			    	</a>
			    </div>
			</div>
		@endif

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
												'location' => ($dentist->city_name ? $dentist->city_name.', ' : '').($dentist->state_name ? $dentist->state_name.', ' : '').($dentist->country->name) 
											]) }}" 
											width="180" 
											height="180"
										/> 
										@if($dentist->is_partner)
											<img 
												class="tooltip-text" 
												src="{{ url('img-trp/mini-logo.png') }}" 
												text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? 'Clinic' : 'Dentist' }}"
											/>
										@endif
									</div>
								    <div class="slider-container">
								    	<h4>{{ $dentist->getNames() }}</h4>
								    	<div class="p">
								    		<div class="img">
								    			<img src="img-trp/map-pin.png" width="10" height="14">
								    		</div>
											{{ $dentist->city_name ? $dentist->city_name.', ' : '' }}
											{{ $dentist->state_name ? $dentist->state_name.', ' : '' }} 
											{{ $dentist->country->name }} 
								    		<!-- <span>(2 km away)</span> -->
								    	</div>
								    	@if( $time = $dentist->getWorkHoursText() )
								    		<div class="p">
								    			<div class="img">
									    			<img src="{{ url('img-trp/open.png') }}" width="13" height="14">
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

	@if(empty($user))

		<div class="index-invite-dentist" id="index-invite-dentist">
			<div class="container">
				<div class="flex flex-mobile">
					<div class="col to-append-image" data-src="{{ url('img-trp/dentacoin-dentist-icon.png') }}">
						{{-- <img class="lazy-load-image" alt="{{ trans('trp.alt-tags.dentist-icon') }}"> --}}
					</div>
					<div class="col">
						<h2>{!! nl2br(trans('trp.page.invite.title')) !!}</h2>
						<h3>{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3>
						<a href="javascript:;" class="button button-yellow" data-popup="invite-new-dentist-popup">{!! nl2br(trans('trp.page.invite.add-dentist')) !!}</a>
					</div>
				</div>
			</div>
		</div>

	@elseif(!empty($user) && !$user->is_dentist)

		<div class="index-invite-dentist patient-invite">
			<div class="container">
				<img src="{{ url('img-trp/dentacoin-dentist-icon.png') }}" alt="{{ trans('trp.alt-tags.dentist-icon') }}">
			</div>
		</div>

		<div class="invite-new-dentist-wrapper white-invite" id="index-invite-dentist">

			<div class="invite-new-dentist-titles">
				<h2>{!! nl2br(trans('trp.page.invite.title')) !!}</h2>
				<h3 class="gbb">{!! nl2br(trans('trp.page.invite.subtitle')) !!}</h3>
			</div>

			<div class="colorfull-wrapper">
				@include('trp.parts.invite-new-dentist-form')
			</div>
		</div>
	@endif

	<div id="to-append"></div>

	@if(!empty($user))
		<div class="strength-parent fixed">
			@include('trp.parts.strength-scale')
		</div>
	@endif

	@if(!empty($country_id) && !empty($_COOKIE['functionality_cookies']))
		<div class="popup fixed-popup" id="change-dentist-popup">
			<div class="popup-inner inner-white">
				<div class="popup-pc-buttons">
					<a href="javascript:;" class="close-popup">
						<img src="{{ url('img/close-icon.png') }}"/>
					</a>
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
					<div class="address-suggester-wrapper-input">
						<div class="modern-field alert-after">
							<input type="text" name="dentists-city" id="dentist-city" class="modern-input address-suggester-input city-dentist" autocomplete="off">
							<label for="dentist-city">
								<span>{!! nl2br(trans('trp.popup.change-dentist-popup.city')) !!}:</span>
							</label>
						</div>

						@if(!empty($user))
							<label class="checkbox-label" for="change-city" >
								<input type="checkbox" class="special-checkbox" id="change-city" name="change-city"/>
								<div class="checkbox-square">✓</div>
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
	@endif

@endsection