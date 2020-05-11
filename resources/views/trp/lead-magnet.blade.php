@extends('trp')

@section('content')

	<div class="lead-magnet-wrapper">
		<div class="lead-magnet-top"></div>

		<div class="lead-magnet-overall container">
			@if($total_points <= 5)
				<img class="sign" src="{{ url('img-trp/attention-sign-icon.svg') }}">
			@elseif($total_points <= 10)
				<img class="sign" src="{{ url('img-trp/ok-result-icon.svg') }}">
			@else 
				<img class="sign" src="{{ url('img-trp/perfect-result-icon.svg') }}">
			@endif

			@if($total_points <= 5)
				<h1 class="red">{{ nl2br(trans('trp.page.lead-magnet-results.title.under-five-points')) }}</h1>
				<h4>
					{{ nl2br(trans('trp.page.lead-magnet-results.subtitle.under-five-points', [
						'first_part' => $first_answer == '3' ? trans('trp.page.lead-magnet-results.subtitle.under-five-points.first') : trans('trp.page.lead-magnet-results.subtitle.under-five-points.second')
					])) }}
				</h4>
			@elseif($total_points <= 10)
				<h1 class="yellow">{{ nl2br(trans('trp.page.lead-magnet-results.title.under-ten-points')) }}</h1>
				<h4>
					{{ nl2br(trans('trp.page.lead-magnet-results.subtitle.under-ten-points', [
						'first_part' => $first_answer == '3' ? trans('trp.page.lead-magnet-results.subtitle.under-ten-points.first') : trans('trp.page.lead-magnet-results.subtitle.under-ten-points.second')
					])) }}
				</h4>
			@else 
				<h1 class="green">{{ nl2br(trans('trp.page.lead-magnet-results.title.over-nine-points')) }}</h1>
			@endif

			<div class="flex flex-charts">
				<div class="pie-chart">
				    <svg viewBox="0 0 36 36" class="circular-chart green">
				      	<path class="circle-bg"
				        d="M18 2.0845
				          a 15.9155 15.9155 0 0 1 0 31.831
				          a 15.9155 15.9155 0 0 1 0 -31.831"
				      	/>
				      	<path class="circle"
				        stroke-dasharray="{{ round(($total_points / 15) * 100) }}, 100"
				        d="M18 2.0845
				          a 15.9155 15.9155 0 0 1 0 31.831
				          a 15.9155 15.9155 0 0 1 0 -31.831"
				      	/>
				      	<text x="18" y="20.35" class="percentage">{{ round(($total_points / 15) * 100) }}%</text>
				      	<text x="18" y="25.35" class="info">{{ nl2br(trans('trp.page.lead-magnet-results.total-score')) }}</text>
				    </svg>
				</div>
				<div class="column-chart">
					<h4>{{ nl2br(trans('trp.page.lead-magnet-results.review-collection-score')) }}</h4>
					<div class="result-column"><div class="result-percentage" style="width: {{ round(($review_collection / 12) * 100) }}%;"></div></div>
					<h4>{{ nl2br(trans('trp.page.lead-magnet-results.review-volume-score')) }}</h4>
					<div class="result-column"><div class="result-percentage" style="width: {{ round(($review_volume / 9) * 100) }}%;"></div></div>
					<h4>{{ nl2br(trans('trp.page.lead-magnet-results.impact-score')) }}</h4>
					<div class="result-column"><div class="result-percentage" style="width: {{ round(($impact / 9) * 100) }}%;"></div></div>
				</div>
			</div>

			<div class="lead-magnet-tips flex">
				<div class="tips-image">
					<img src="{{ url('img-trp/dentist-image.png') }}">
				</div>
				<div class="tips-content">
					<h2><img src="{{ url('img-trp/bulb.png') }}">{{ nl2br(trans('trp.page.lead-magnet-results.pro-tips')) }}</h2>
					@if($total_points <= 5)
						{!! nl2br(trans('trp.page.lead-magnet-results.pro-tips.under-five-points')) !!}
					@elseif($total_points <= 10)
						{!! nl2br(trans('trp.page.lead-magnet-results.pro-tips.under-ten-points')) !!}
					@else
						{!! nl2br(trans('trp.page.lead-magnet-results.pro-tips.over-nine-points', [
							'last_part' => $first_answer == '3' ? trans('trp.page.lead-magnet-results.pro-tips.over-nine-points.first') : trans('trp.page.lead-magnet-results.pro-tips.over-nine-points.second')
						])) !!}
					@endif
				</div>
			</div>

			<a href="{!! !empty($user) ? $user->getLink().'?popup=popup-invite' : 'javascript:;' !!}" class="button button-yellow {!! empty($user) ? 'get-started-button' : '' !!}">{{ nl2br(trans('trp.page.lead-magnet-results.button-improve-results')) }}</a>
		</div>

		<div class="country-dentist-rating">
			<div class="container">
				@if(!empty($country_id))
					<h2>{{ nl2br(trans('trp.page.lead-magnet-results.dentists-in')) }}: <nl><img src="{{ url('img-trp/white-pin.png') }}"><span class="country">{{ App\Models\Country::find($country_id)->name }}</span><nl></h2>
				@else
					<h2>{{ nl2br(trans('trp.page.lead-magnet-results.dentists-in')) }} <nl><span class="country">{{ nl2br(trans('trp.page.lead-magnet-results.your-area')) }}</span><nl></h2>
				@endif
				<div class="rating-wrapper">
					<div class="avg-rating block">
						<img src="{{ url('img-trp/lead-magnet-rating.png') }}">
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.avg-rating')) }}</h3>
						<div class="ratings big">
							<div class="stars">
								<div class="bar" style="width: {{ $avg_country_rating/5*100 }}%;"></div>
							</div>
						</div>
						<p>{{ $avg_country_rating }} stars</p>
					</div>
					<div class="avg-reviews block">
						<img src="{{ url('img-trp/lead-magnet-reviews.png') }}">
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.recommended-reviews')) }}</h3>
						<p>
							{{ nl2br(trans('trp.page.lead-magnet-results.reviews-monthly', [
								'reviews' => $country_reviews
							])) }}
						</p>
					</div>
				</div>

				<p class="rating-info">{{ nl2br(trans('trp.page.lead-magnet-results.based')) }}</p>
			</div>
		</div>

		<div class="lead-magnet-info container tac">
			<div class="info-container">
				<img class="trp-logo" src="{{ url('img-trp/logo-blue.png') }}" alt="{{ trans('trp.alt-tags.logo') }}">

				<h2>{{ nl2br(trans('trp.page.lead-magnet-results.improve-title')) }}</h2>

				<div class="info-box flex">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-get-more-reviews-icon.png') }}" alt="{{ trans('trp.alt-tags.more-reviews') }}">
					</div>
					<div class="info-text">
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-first-title')) }}</h3>
						<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-first-subtitle')) }}</p>
					</div>
				</div>

				<div class="info-box flex">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-whatsapp-invites-icon.png') }}" alt="{{ trans('trp.alt-tags.whatsapp-invites') }}">
					</div>
					<div class="info-text">
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-second-title')) }}</h3>
						<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-second-subtitle')) }}</p>
					</div>
				</div>

				<div class="info-box flex">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-free-for-dentists-icon.png') }}" alt="{{ trans('trp.alt-tags.free-for-dentists') }}">
					</div>
					<div class="info-text">
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-third-title')) }}</h3>
						<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-third-subtitle')) }}</p>
					</div>
				</div>
			</div>
			<a href="{!! !empty($user) ? $user->getLink().'?popup=popup-invite' : 'javascript:;' !!}" class="get-started {!! empty($user) ? 'get-started-button' : '' !!}">{{ nl2br(trans('trp.page.lead-magnet-results.button-get-started')) }}</a>
		</div>

	</div>

@endsection