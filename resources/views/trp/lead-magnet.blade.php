@extends('trp')

@section('content')

	@if(empty(session('lead_magnet')) || empty(session('lead_magnet')['points']))

		<div class="lead-magnet-questions-wrapper">
			<div class="lead-magnet-image">
				<img src="{{ url('img-trp/popup-images/lead-magnet.png') }}"/>
			</div>
			<div class="lead-magnet-survey-box">
				<h2 class="mont">
					{!! nl2br(trans('trp.popup.popup-lead-magnet.title')) !!}
				</h2>

				<div class="step-tabs flex flex-center flex-text-center flex-mobile">
					<div class="step active">
						<span class="border">
							<span>1</span>
						</span>
						<p>{{ trans('trp.popup.popup-lead-magnet.step1') }}</p>
					</div>
					<div class="step">
						<span class="border">
							<span>2</span>
						</span>
						<p>{{ trans('trp.popup.popup-lead-magnet.step2') }}</p>
					</div>
					<div class="step">
						<span class="border">
							<span>3</span>
						</span>
						<p>{{ trans('trp.popup.popup-lead-magnet.step3') }}</p>
					</div>
				</div>

				{!! Form::open([
					'method' => 'post', 
					'class' => 'lead-magnet-form-survey', 
					'id' => 'lead-magnet-form-survey', 
					'url' => getLangUrl('lead-magnet-step2') 
				]) !!}
					{!! csrf_field() !!}

					<div class="loader-lead-magnet" style="display: none;">
						<video
						type="video/mp4" 
						src="{{ url('img-trp/trp-score-loading-animation.mp4') }}" 
						playsinline 
						autoplay 
						muted 
						loop
						controls=""></video>
						<p>{!! nl2br(trans('trp.popup.popup-lead-magnet.loader')) !!}</p>
					</div>
					<div class="magnet-content">

						<div class="first-form">
							<div class="modern-field alert-after">
								<input type="text" name="firstname" id="magnet-name" class="modern-input magnet-name" autocomplete="off">
								<label for="magnet-name">
									<span>{!! trans('trp.popup.popup-lead-magnet.name') !!}</span>
								</label>
							</div>
							<div class="modern-field alert-after">
								<input type="text" name="website" id="magnet-website" class="modern-input magnet-website" autocomplete="off">
								<label for="magnet-website">
									<span>{!! trans('trp.popup.popup-lead-magnet.website') !!}</span>
								</label>
							</div>
							<div class="modern-field alert-after">
								<select name="country" id="magnet-country" class="modern-input">
									@if(!$country_id)
										<option>-</option>
									@endif
									@if(!empty($countries))
										@foreach( $countries as $country )
											<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!}>
												{{ $country->name }}
											</option>
										@endforeach
									@endif
								</select>
							</div>
							<div class="modern-field alert-after">
								<input type="email" name="email" id="magnet-email" class="modern-input magnet-email" autocomplete="off">
								<label for="magnet-email">
									<span>{!! trans('trp.popup.popup-lead-magnet.email') !!}</span>
								</label>
							</div>

							<label class="checkbox-label agree-label" for="magnet-agree">
								<input type="checkbox" class="special-checkbox" id="magnet-agree" name="agree" value="1">
								<div class="checkbox-square">✓</div>
								<div>
									{!! trans('trp.popup.popup-lead-magnet.privacy', [
										'link' => '<a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">',
										'endlink' => '</a>',
									]) !!}
								</div>
							</label>

							<div class="alert magnet-alert" style="display: none; margin-top: 20px;">
							</div>
							<div class="tac">
								<a href="javascript:;" class="blue-button magnet-user-info-button" data-validator="{{ getLangUrl('lead-magnet-step1') }}">
									{!! trans('trp.popup.popup-lead-magnet.first-step-submit') !!}
								</a>
							</div>
						</div>
					</div>

					<div class="magnet-content" style="display: none;">

						<div class="flickity-magnet">
							<div class="answer-radios-magnet clearfix">
								<div class="answer-question">
									<h4>1. {!! trans('trp.popup.popup-lead-magnet.question1') !!}</h4>
								</div>
								<div class="buttons-list clearfix"> 
									<label class="magnet-label" for="answer-1-1">
										<input id="answer-1-1" type="radio" name="answer-1" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question1.answer1') !!}" value="1">
										{!! trans('trp.popup.popup-lead-magnet.question1.answer1') !!}
									</label>
									<label class="magnet-label" for="answer-1-2">
										<input id="answer-1-2" type="radio" name="answer-1" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question1.answer2') !!}" value="2">
										{!! trans('trp.popup.popup-lead-magnet.question1.answer2') !!}
									</label>
									<label class="magnet-label" for="answer-1-3">
										<input id="answer-1-3" type="radio" name="answer-1" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question1.answer3') !!}" value="3">
										{!! trans('trp.popup.popup-lead-magnet.question1.answer3') !!}
									</label>
								</div>
							</div>
							<div class="answer-radios-magnet clearfix">
								<div class="answer-question">
									<h4>2. {!! trans('trp.popup.popup-lead-magnet.question2') !!}</h4>
								</div>
								<div class="buttons-list clearfix"> 
									<label class="magnet-label" for="answer-2-1">
										<input id="answer-2-1" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question2.answer1') !!}" value="1">
										{!! trans('trp.popup.popup-lead-magnet.question2.answer1') !!}
									</label>
									<label class="magnet-label" for="answer-2-2">
										<input id="answer-2-2" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question2.answer2') !!}" value="2">
										{!! trans('trp.popup.popup-lead-magnet.question2.answer2') !!}
									</label>
									<label class="magnet-label" for="answer-2-3">
										<input id="answer-2-3" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question2.answer3') !!}" value="3">
										{!! trans('trp.popup.popup-lead-magnet.question2.answer3') !!}
									</label>
									<label class="magnet-label" for="answer-2-4">
										<input id="answer-2-4" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question2.answer4') !!}" value="4">
										{!! trans('trp.popup.popup-lead-magnet.question2.answer4') !!}
									</label>
									<label class="magnet-label" for="answer-2-5">
										<input id="answer-2-5" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question2.answer5') !!}" value="5">
										{!! trans('trp.popup.popup-lead-magnet.question2.answer5') !!}
									</label>
									<label class="magnet-label" for="answer-2-6">
										<input id="answer-2-6" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question2.answer6') !!}" value="6">
										{!! trans('trp.popup.popup-lead-magnet.question2.answer6') !!}
									</label>
								</div>
							</div>
							<div class="answer-radios-magnet clearfix">
								<div class="answer-question">
									<h4>3. {!! trans('trp.popup.popup-lead-magnet.question3') !!}</h4>
								</div>
								<div class="buttons-list clearfix"> 
									<p>{!! trans('trp.popup.popup-lead-magnet.select-all') !!}</p>

									<label class="magnet-label green-checkbox" for="answer-3-1">
										{!! trans('trp.popup.popup-lead-magnet.question3.asnwer1') !!}
										<span>✓</span>
										<input id="answer-3-1" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" ans-text="{!! trans('trp.popup.popup-lead-magnet.question3.asnwer1') !!}" value="1">
									</label>
									<label class="magnet-label green-checkbox" for="answer-3-2">
										{!! trans('trp.popup.popup-lead-magnet.question3.asnwer2') !!}
										<span>✓</span>
										<input id="answer-3-2" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" ans-text="{!! trans('trp.popup.popup-lead-magnet.question3.asnwer2') !!}" value="2">
									</label>
									<label class="magnet-label green-checkbox" for="answer-3-3">
										{!! trans('trp.popup.popup-lead-magnet.question3.asnwer3') !!}
										<span>✓</span>
										<input id="answer-3-3" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" ans-text="{!! trans('trp.popup.popup-lead-magnet.question3.asnwer3') !!}" value="3">
									</label>
									<label class="magnet-label green-checkbox disabler-label" for="answer-3-4">
										{!! trans('trp.popup.popup-lead-magnet.question3.asnwer4') !!}
										<span>✓</span>
										<input id="answer-3-4" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox disabler" ans-text="{!! trans('trp.popup.popup-lead-magnet.question3.asnwer4') !!}" value="4">
									</label>
								</div> 
								<div class="alert alert-warning" style="display: none;">{!! trans('trp.popup.popup-lead-magnet.select-answer-error') !!}</div>
								<div class="tac">
									<a href="javascript:;" class="blue-button magnet-validator validator-skip" id="q-three-magnet">
										{!! trans('trp.popup.popup-lead-magnet.next') !!}
									</a>
								</div>
							</div>
							<div class="answer-radios-magnet clearfix">
								<div class="answer-question">
									<h4>4. {!! trans('trp.popup.popup-lead-magnet.question4') !!}</h4>
								</div>
								<div class="buttons-list clearfix"> 
									<label class="magnet-label" for="answer-4-1">
										<input id="answer-4-1" type="radio" name="answer-4" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question4.asnwer1') !!}" value="1">
										{!! trans('trp.popup.popup-lead-magnet.question4.asnwer1') !!}
									</label>
									<label class="magnet-label" for="answer-4-2">
										<input id="answer-4-2" type="radio" name="answer-4" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question4.asnwer2') !!}" value="2">
										{!! trans('trp.popup.popup-lead-magnet.question4.asnwer2') !!}
									</label>
									<label class="magnet-label" for="answer-4-3">
										<input id="answer-4-3" type="radio" name="answer-4" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question4.asnwer3') !!}" value="3">
										{!! trans('trp.popup.popup-lead-magnet.question4.asnwer3') !!}
									</label>
								</div>
							</div>
							<div class="answer-radios-magnet clearfix">
								<div class="answer-question">
									<h4>5. {!! trans('trp.popup.popup-lead-magnet.question5') !!}</h4>
								</div>
								<div class="buttons-list clearfix"> 
									<label class="magnet-label" for="answer-5-1">
										<input id="answer-5-1" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question5.asnwer1') !!}" value="1">
										{!! trans('trp.popup.popup-lead-magnet.question5.asnwer1') !!}
									</label>
									<label class="magnet-label" for="answer-5-2">
										<input id="answer-5-2" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question5.asnwer2') !!}" value="2">
										{!! trans('trp.popup.popup-lead-magnet.question5.asnwer2') !!}
									</label>
									<label class="magnet-label" for="answer-5-3">
										<input id="answer-5-3" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question5.asnwer3') !!}" value="3">
										{!! trans('trp.popup.popup-lead-magnet.question5.asnwer3') !!}
									</label>
									<label class="magnet-label" for="answer-5-4">
										<input id="answer-5-4" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="{!! trans('trp.popup.popup-lead-magnet.question5.asnwer4') !!}" value="4">
										{!! trans('trp.popup.popup-lead-magnet.question5.asnwer4') !!}
									</label>
								</div>
							</div>
						</div>
					</div>

				{!! Form::close() !!}
			</div>
		</div>

		<script type="text/javascript">
			gtag('event', 'Open', {
				'event_category': 'LeadMagnet',
				'event_label': 'Popup',
			});
		</script>

	@else
	
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
						<h2 class="mont">{!! nl2br(trans('trp.page.lead-magnet-results.title')) !!}</h2>
						@if($total_points <= 5)
							<h4 class="red">
								{!! nl2br(trans('trp.page.lead-magnet-results.title.under-five-points')) !!}
							</h4>
						@elseif($total_points <= 10)
							<h4 class="yellow">
								{!! nl2br(trans('trp.page.lead-magnet-results.title.under-ten-points')) !!}
							</h4>
						@else 
							<h4 class="green">
								{!! nl2br(trans('trp.page.lead-magnet-results.title.over-nine-points')) !!}
							</h4>
						@endif
						<h3>{{ nl2br(trans('trp.page.lead-magnet-results.pro-tips')) }}</h3>

						<div class="flex">
							<div>
								<img src="{{ url('img-trp/stimulate-patient-feedback.svg') }}"/>
							</div>
							<div>
								<h5>{!! nl2br(trans('trp.page.lead-magnet-results.info-title.1')) !!}</h5>
								<p>{!! nl2br(trans('trp.page.lead-magnet-results.info-description.1')) !!}</p>
							</div>
						</div>

						<div class="flex">
							<div>
								<img src="{{ url('img-trp/respond-to-patient.svg') }}"/>
							</div>
							<div>
								<h5>{!! nl2br(trans('trp.page.lead-magnet-results.info-title.2')) !!}</h5>
								<p>{!! nl2br(trans('trp.page.lead-magnet-results.info-description.2')) !!}</p>
							</div>
						</div>
					</div>
				</div>

				<div class="button-wrapper tac">
					<a href="{!! !empty($user) ? $user->getLink().'?popup=popup-invite' : 'javascript:;' !!}" class="green-button {!! empty($user) ? 'get-started-button' : '' !!}">
						<img src="{{ url('img-trp/rocket.svg') }}"/>
						{{ nl2br(trans('trp.page.lead-magnet-results.button-improve-results')) }}
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
							<h3>{{ nl2br(trans('trp.page.lead-magnet-results.recommended-reviews')) }}</h3>
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
						{{ nl2br(trans('trp.page.lead-magnet-results.improve-title')) }}
					</h2>
					
					<div class="flex info-box-wrapper">
						<div class="info-box">
							<div class="info-icon">
								<img src="{{ url('img-trp/lead-magnet-1.png') }}" alt="{{ trans('trp.alt-tags.more-reviews') }}">
							</div>
							<div class="info-text">
								<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-first-title')) }}</h3>
								<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-first-subtitle')) }}</p>
							</div>
						</div>

						<div class="info-box">
							<div class="info-icon">
								<img src="{{ url('img-trp/lead-magnet-2.png') }}" alt="{{ trans('trp.alt-tags.whatsapp-invites') }}">
							</div>
							<div class="info-text">
								<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-second-title')) }}</h3>
								<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-second-subtitle')) }}</p>
							</div>
						</div>

						<div class="info-box">
							<div class="info-icon">
								<img src="{{ url('img-trp/lead-magnet-3.png') }}" alt="{{ trans('trp.alt-tags.free-for-dentists') }}">
							</div>
							<div class="info-text">
								<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-third-title')) }}</h3>
								<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-third-subtitle')) }}</p>
							</div>
						</div>

						<div class="info-box">
							<div class="info-icon">
								<img src="{{ url('img-trp/lead-magnet-4.png') }}" alt="{{ trans('trp.alt-tags.free-for-dentists') }}">
							</div>
							<div class="info-text">
								<h3>{{ nl2br(trans('trp.page.lead-magnet-results.improve-forth-title')) }}</h3>
								<p>{{ nl2br(trans('trp.page.lead-magnet-results.improve-forth-subtitle')) }}</p>
							</div>
						</div>
					</div>
				</div>
				<a href="{!! !empty($user) ? $user->getLink().'?popup=popup-invite' : 'javascript:;' !!}" class="blue-button get-started {!! empty($user) ? 'get-started-button' : '' !!}">
					{{ nl2br(trans('trp.page.lead-magnet-results.button-get-started')) }}
				</a>
			</div>
		</div>
	@endif

@endsection