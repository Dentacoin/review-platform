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
				<h1 class="red">Currently your practice is not utilizing online reviews effectively.</h1>
				<h4>{!! $first_answer == '3' ? 'То keep current and attract new patients' : 'To attract new patients' !!} successfully it needs major improvements in almost all areas.</h4>
			@elseif($total_points <= 10)
				<h1 class="yellow">Currently your practice is not utilizing the full potential of online reviews.</h1>
				<h4>{!! $first_answer == '3' ? 'То keep current and attract new patients' : 'To attract new patients' !!} successfully it needs improvements in some areas.</h4>
			@else 
				<h1 class="green">Congrats! You are on the right track.</h1>
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
				      	<text x="18" y="25.35" class="info">Overall score</text>
				    </svg>
				</div>
				<div class="column-chart">
					<h4>Review collection</h4>
					<div class="result-column"><div class="result-percentage" style="width: {{ round(($review_collection / 12) * 100) }}%;"></div></div>
					<h4>Review volume</h4>
					<div class="result-column"><div class="result-percentage" style="width: {{ round(($review_volume / 9) * 100) }}%;"></div></div>
					<h4>Impact score</h4>
					<div class="result-column"><div class="result-percentage" style="width: {{ round(($impact / 9) * 100) }}%;"></div></div>
				</div>
			</div>

			<div class="lead-magnet-tips flex">
				<div class="tips-image">
					<img src="{{ url('img-trp/dentist-image.png') }}">
				</div>
				<div class="tips-content">
					<h2><img src="{{ url('img-trp/bulb.png') }}">Pro tips</h2>
					@if($total_points <= 5)
						<b>Start with a more proactive approach by regularly inviting patients to leave a review.</b> Point them to a platform, which clearly indicates feedback from invited patients as ‘verified’. This will help prospective patients distinguish genuine reviews. Also, it will guarantee you have sufficient number of reviews to substantiate your overall star ratings. <b> <br/>Another area you have to work on is replying to reviews.</b> A well-crafted response leaves a good impression both to current and prospective patients. It shows you appreciate and take into consideration patient feedback. <b>Moreover, it gives you a chance to turn unfavourable reviews into positive marketing opportunities.</b>
					@elseif($total_points <= 10)
						<b>Strengthen your online reputation with constant flow of fresh reviews.</b> By regularly inviting patients to leave a review you can get more recent reviews to substantiate your overall star rating. Point patients to a platform, which clearly indicates feedback from invited patients as ‘verified’. This will help highlight genuine feedback. <br/> <b>Dedicate time to respond to reviews.</b> It leaves a good impression both to current and prospective patients. Also, it shows that patient feedback is appreciated and taken into account. <b>Moreover, it gives you a chance to turn unfavourable feedback into positive marketing opportunities.</b>
					@else
						However, there are some things, which you can help you unleash the full power of patient feedback and {!! $first_answer == '3' ? 'tо keep current and attract new patients' : 'to attract new patients' !!}.
					@endif
				</div>
			</div>

			<a href="javascript:;" class="get-started get-started-button button-sign-up-dentist">Get started</a>
		</div>

		@if(!empty($country_id))
			<div class="country-dentist-rating">
				<div class="container">
					<h2>Other dentists in: <nl><img src="{{ url('img-trp/white-pin.png') }}"><span class="country">{{ App\Models\Country::find($country_id)->name }}</span><nl></h2>
					<div class="rating-wrapper">
						<div class="avg-rating block">
							<img src="{{ url('img-trp/lead-magnet-rating.png') }}">
							<h3>Average Rating</h3>
							<div class="ratings big">
								<div class="stars">
									<div class="bar" style="width: {{ $avg_country_rating/5*100 }}%;"></div>
								</div>
							</div>
							<p>{{ $avg_country_rating }} stars</p>
						</div>
						<div class="avg-reviews block">
							<img src="{{ url('img-trp/lead-magnet-reviews.png') }}">
							<h3>Recommended Min. Number of Reviews</h3>
							<p>{{ $country_reviews }} reviews monthly</p>
						</div>
					</div>

					<p class="rating-info">based on insights from Dentacoin Trusted Reviews</p>
				</div>
			</div>
		@endif

		<div class="lead-magnet-info container tac">
			<div class="info-container">
				<img class="trp-logo" src="{{ url('img-trp/logo-blue.png') }}" alt="Dentacoin trusted reviews logo">

				<h2>UNLEASH THE POWER OF GENUINE DENTACOIN TRUSTEDREVIEWS</h2>

				<div class="info-box flex">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-get-more-reviews-icon.png') }}" alt="Dentacoin get more reviews icon">
					</div>
					<div class="info-text">
						<h3>Get more genuine reviews with less efforts</h3>
						<p>Upload or copy/paste patient file and send personal review invites to all your patients in a minute. </p>
					</div>
				</div>

				<div class="info-box flex">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-whatsapp-invites-icon.png') }}" alt="Dentacoin trusted reviews whatsapp invites icon">
					</div>
					<div class="info-text">
						<h3>NEW WhatsApp integration</h3>
						<p>Don’t have patient email address? No worries, send a personal invite via WhatsApp for FREE.</p>
					</div>
				</div>

				<div class="info-box flex">
					<div class="info-icon">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-free-for-dentists-icon.png') }}" alt="Dentacoin trusted reviews free for dentists icon">
					</div>
					<div class="info-text">
						<h3>Motivate patients to contribute at no cost</h3>
						<p>Patients are rewarded for their genuine feedback in DCN tokens at no cost for your dental practice.</p>
					</div>
				</div>
			</div>
			<a href="javascript:;" class="get-started get-started-button button-sign-up-dentist">Get started</a>
		</div>

	</div>

@endsection