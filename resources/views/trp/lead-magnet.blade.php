@extends('trp')

@section('content')

	<div class="lead-magnet-wrapper">
		<div class="container">
			<div class="magnet-main-section">
				<div class="flex-1">
					<div class="flex-charts">
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
								<text x="18" y="20.35" class="percentage mont">{{ round(($total_points / 15) * 100) }}%</text>
								<text x="18" y="25.35" class="info">{{ nl2br(trans('trp.page.lead-magnet-results.total-score')) }}</text>
							</svg>
						</div>
						<div class="column-chart">
							<p>{{ nl2br(trans('trp.page.lead-magnet-results.review-collection-score')) }}</p>
							<div class="result-column"><div class="result-percentage" style="width: {{ round(($review_collection / 12) * 100) }}%;"></div></div>
							<p>{{ nl2br(trans('trp.page.lead-magnet-results.review-volume-score')) }}</p>
							<div class="result-column"><div class="result-percentage" style="width: {{ round(($review_volume / 9) * 100) }}%;"></div></div>
							<p>{{ nl2br(trans('trp.page.lead-magnet-results.impact-score')) }}</p>
							<div class="result-column"><div class="result-percentage" style="width: {{ round(($impact / 9) * 100) }}%;"></div></div>
						</div>
					</div>
				</div>

				<div class="flex-2">
					<h2 class="mont">Your Online Reputation <br/> Needs Major Improvements</h2>
					@if($total_points <= 5)
						<h4 class="red">
							{{-- {{ nl2br(trans('trp.page.lead-magnet-results.title.under-five-points')) }} --}}
							Currently your dental practice is not utilizing online reviews effectively. <br/> Let us help you attract new patients and boost your online reputation!
						</h4>
					@elseif($total_points <= 10)
						<h4 class="yellow">
							Currently your practice is not utilizing the full potential of online reviews. <br/> Let us help you attract new patients and boost your online reputation!
							{{-- {{ nl2br(trans('trp.page.lead-magnet-results.title.under-ten-points')) }} --}}
						</h4>
					@else 
						<h4 class="green">
							Congrats! You are on the right track. Still, there are always further ways to unleash the full power of patient feedback and attract new patients.
							{{-- {{ nl2br(trans('trp.page.lead-magnet-results.title.over-nine-points')) }} --}}
						</h4>
					@endif
					<h3>{{ nl2br(trans('trp.page.lead-magnet-results.pro-tips')) }}</h3>

					<div class="flex">
						<div>
							<img src="{{ url('img-trp/stimulate-patient-feedback.svg') }}"/>
						</div>
						<div>
							<h5>Stimulate patient feedback</h5>
							<p>Be more proactive and start inviting patients to share their feedback after a dental visit. Point them to an online reviews platform which clearly indicates genuine feedback from real patients. This will ensure you a sufficient number of verified reviews to substantiate your overall star ratings.</p>
						</div>
					</div>

					<div class="flex">
						<div>
							<img src="{{ url('img-trp/respond-to-patient.svg') }}"/>
						</div>
						<div>
							<h5>Respond to patient reviews</h5>
							<p>Regularly monitor and reply to patient feedback. A well-crafted response leaves a good impression both to current and prospective patients. It shows that you really value patient feedback and improve upon it. Moreover, it gives you a chance to turn unfavorable reviews into positive marketing opportunities.</p>
						</div>
					</div>
				</div>
			</div>

			<div class="button-wrapper tac">
				<a href="{!! !empty($user) ? $user->getLink().'?popup=popup-invite' : 'javascript:;' !!}" class="green-button {!! empty($user) ? 'get-started-button' : '' !!}">
					<img src="{{ url('img-trp/rocket.svg') }}"/>
					{{-- {{ nl2br(trans('trp.page.lead-magnet-results.button-improve-results')) }} --}}
					Boost Your Online Presence
				</a>
			</div>
		</div>
	
	</div>

	<div class="country-dentist-rating">
		<div class="container">
			@if(!empty($country_id))
				<h2 class="mont">
					{{ nl2br(trans('trp.page.lead-magnet-results.dentists-in')) }}: 
					<nl>
						<img src="{{ url('img-trp/pin-green.svg') }}"/>
						<span class="country mont">{{ App\Models\Country::find($country_id)->name }}</span>
					<nl>
				</h2>
			@else
				<h2 class="mont">
					{{ nl2br(trans('trp.page.lead-magnet-results.dentists-in')) }} 
					<nl>
						<span class="country mont">{{ nl2br(trans('trp.page.lead-magnet-results.your-area')) }}</span>
					<nl>
				</h2>
			@endif
			<div class="rating-wrapper">
				<div class="country-flex avg-rating flex">
					<div>
						<img src="{{ url('img-trp/lead-magnet-rating.svg') }}">
					</div>
					<div>
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.avg-rating')) }}</h3>
						<div class="flex flex-mobile flex-center">
							<div class="ratings big">
								<div class="stars">
									<div class="bar" style="width: {{ $avg_country_rating/5*100 }}%;"></div>
								</div>
							</div>
							<p>{{ $avg_country_rating }}</p>
						</div>
					</div>
				</div>
				<div class="country-flex avg-reviews flex">
					<div>
						<img src="{{ url('img-trp/lead-magnet-results.svg') }}">
					</div>
					<div>
						{{-- <h3>{{ nl2br(trans('trp.page.lead-magnet-results.recommended-reviews')) }}</h3> --}}
						<h3>Recommended minimum</h3>
						<p>
							{{ nl2br(trans('trp.page.lead-magnet-results.reviews-monthly', [
								'reviews' => $country_reviews
							])) }}
						</p>
					</div>
				</div>
			</div>

			<p class="rating-info">{{ nl2br(trans('trp.page.lead-magnet-results.based')) }}</p>
		</div>
		<img class="dentist-image" src="{{ url('img-trp/launch-listing-dentist.png') }}"/>
	</div>

	<div class="container">
		<div class="lead-magnet-info container tac">
			<div class="info-container">

				<h2 class="mont">
					Improve Your Reputation Score With Trusted Reviews
					{{-- {{ nl2br(trans('trp.page.lead-magnet-results.improve-title')) }} --}}
				</h2>
				
				<div class="flex info-box-wrapper">
					<div class="info-box">
						<div class="info-icon">
							<img src="{{ url('img-trp/lead-magnet-1.png') }}" alt="{{ trans('trp.alt-tags.more-reviews') }}">
						</div>
						<div class="info-text">
							<h3>Get More Real Reviews With Less Efforts</h3>
							{{-- <h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-first-title')) }}</h3> --}}
							<p>Send personal feedback invites to all your patients in a minute. Email or WhatsApp - it’s your choice!</p>
							{{-- <p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-first-subtitle')) }}</p> --}}
						</div>
					</div>

					<div class="info-box">
						<div class="info-icon">
							<img src="{{ url('img-trp/lead-magnet-2.png') }}" alt="{{ trans('trp.alt-tags.whatsapp-invites') }}">
						</div>
						<div class="info-text">
							{{-- <h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-second-title')) }}</h3> --}}
							<h3>Motivate Patient Reviews At No Cost</h3>
							{{-- <p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-second-subtitle')) }}</p> --}}
							<p>Patients are rewarded for their genuine feedback in DCN at no charge for your dental practice.</p>
						</div>
					</div>

					<div class="info-box">
						<div class="info-icon">
							<img src="{{ url('img-trp/lead-magnet-3.png') }}" alt="{{ trans('trp.alt-tags.free-for-dentists') }}">
						</div>
						<div class="info-text">
							{{-- <h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-third-title')) }}</h3> --}}
							<h3>Receive Rewards for Each Active User</h3>
							{{-- <p>{{ nl2br(tratrans('trp.page.lead-magnet-results.improve-third-title')) }}</h3> --}}
							<p>Get rewarded in DCN for actively referring fellow dentists and inviting patients to leave a review.</p>
						</div>
					</div>

					<div class="info-box">
						<div class="info-icon">
							<img src="{{ url('img-trp/lead-magnet-4.png') }}" alt="{{ trans('trp.alt-tags.free-for-dentists') }}">
						</div>
						<div class="info-text">
							<h3>Access Other Patient Loyalty Apps</h3>
							<p>By signing up on Trusted Reviews, you get FREE access to all other Dentacoin tools.</p>
						</div>
					</div>
				</div>
			</div>
			<a href="{!! !empty($user) ? $user->getLink().'?popup=popup-invite' : 'javascript:;' !!}" class="blue-button get-started {!! empty($user) ? 'get-started-button' : '' !!}">
				{{-- {{ nl2br(trans('trp.page.lead-magnet-results.button-get-started')) }} --}}
				List Your Practice Now
			</a>
		</div>

	</div>

@endsection