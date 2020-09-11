<div class="popup fixed-popup" id="popup-lead-magnet">
	<div class="popup-inner inner-white">
		<div class="loader-mask">
		    <div class="loader">
		      	"Loading..."
		    </div>
		    <div class="loader-text">
		    	{!! nl2br(trans('trp.popup.popup-lead-magnet.loader')) !!}
		    </div>
		</div>
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.popup-lead-magnet.title')) !!}
		</h2>

		<div class="popup-tabs colorful-tabs flex flex-mobile">
			<span class="active col" style="z-index: 3">
				1
			</span>
			<span class="col second-step" style="z-index: 2">
				2
			</span>
			<span class="col" style="z-index: 1">
				3
			</span>
		</div>

		{!! Form::open(array('method' => 'post', 'class' => 'lead-magnet-form-step2', 'id' => 'lead-magnet-form-step2', 'url' => getLangUrl('lead-magnet-step2') )) !!}
			{!! csrf_field() !!}
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
						<select name="country" id="magnet-country" class="modern-input country-select">
							@if(!$country_id)
								<option>-</option>
							@endif
							@if(!empty($countries))
								@foreach( $countries as $country )
									<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
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
						<i class="far fa-square"></i>
						{!! trans('trp.popup.popup-lead-magnet.privacy', [
							'link' => '<a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">',
							'endlink' => '</a>',
						]) !!}
					</label>

					<div class="alert alert-warning agree-cookies" style="display: none; margin-bottom: 20px;">
						{!! trans('trp.popup.popup-lead-magnet.accept-cookies') !!}
					</div>

					<div class="alert magnet-alert" style="display: none; margin-top: 20px;">
					</div>
					<div class="tac">
						<a href="javascript:;" class="button first-form-button" data-validator="{{ getLangUrl('lead-magnet-step1') }}">{!! trans('trp.popup.popup-lead-magnet.first-step-submit') !!}</a>
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
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-1-1" type="radio" name="answer-1" class="lead-magnet-radio" ans-text="To acquire new patients" value="1">
								{!! trans('trp.popup.popup-lead-magnet.question1.answer1') !!}
							</label>
							<label class="magnet-label" for="answer-1-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-1-2" type="radio" name="answer-1" class="lead-magnet-radio" ans-text="To keep existing patients" value="2">
								{!! trans('trp.popup.popup-lead-magnet.question1.answer2') !!}
							</label>
							<label class="magnet-label" for="answer-1-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-1-3" type="radio" name="answer-1" class="lead-magnet-radio" ans-text="Both" value="3">
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
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-1" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="My website" value="1">
								{!! trans('trp.popup.popup-lead-magnet.question2.answer1') !!}
							</label>
							<label class="magnet-label" for="answer-2-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-2" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="Google" value="2">
								{!! trans('trp.popup.popup-lead-magnet.question2.answer2') !!}
							</label>
							<label class="magnet-label" for="answer-2-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-3" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="Facebook or other social media" value="3">
								{!! trans('trp.popup.popup-lead-magnet.question2.answer3') !!}
							</label>
							<label class="magnet-label" for="answer-2-4">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-4" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="General review platform (e.g. Trustpilot)" value="4">
								{!! trans('trp.popup.popup-lead-magnet.question2.answer4') !!}
							</label>
							<label class="magnet-label" for="answer-2-5">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-5" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="Specialized review platform (e.g. Dentacoin Trusted Reviews, Zocdoc.)" value="5">
								{!! trans('trp.popup.popup-lead-magnet.question2.answer5') !!}
							</label>
							<label class="magnet-label" for="answer-2-6">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-6" type="radio" name="answer-2" class="lead-magnet-radio" ans-text="I don’t use one" value="6">
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
							<label class="magnet-label" for="answer-3-1">
								<i class="far fa-square"></i>
								<input id="answer-3-1" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" ans-text="Yes, in person" value="1">
								{!! trans('trp.popup.popup-lead-magnet.question3.asnwer1') !!}
							</label>
							<label class="magnet-label" for="answer-3-2">
								<i class="far fa-square"></i>
								<input id="answer-3-2" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" ans-text="Yes, by email" value="2">
								{!! trans('trp.popup.popup-lead-magnet.question3.asnwer2') !!}
							</label>
							<label class="magnet-label" for="answer-3-3">
								<i class="far fa-square"></i>
								<input id="answer-3-3" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" ans-text="Yes, by SMS" value="3">
								{!! trans('trp.popup.popup-lead-magnet.question3.asnwer3') !!}
							</label>
							<label class="magnet-label disabler-label" for="answer-3-4">
								<i class="far fa-square"></i>
								<input id="answer-3-4" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox disabler" ans-text="No" value="4">
								{!! trans('trp.popup.popup-lead-magnet.question3.asnwer4') !!}
							</label>
						</div> 
						<div class="alert alert-warning" style="display: none;">{!! trans('trp.popup.popup-lead-magnet.select-answer-error') !!}</div>
						<div class="tac">
							<a href="javascript:;" class="button magnet-validator validator-skip" id="q-three-magnet">{!! trans('trp.popup.popup-lead-magnet.next') !!}</a>
						</div>
					</div>
					<div class="answer-radios-magnet clearfix">
						<div class="answer-question">
							<h4>4. {!! trans('trp.popup.popup-lead-magnet.question4') !!}</h4>
						</div>
						<div class="buttons-list clearfix"> 
							<label class="magnet-label" for="answer-4-1">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-4-1" type="radio" name="answer-4" class="lead-magnet-radio" ans-text="Every day" value="1">
								{!! trans('trp.popup.popup-lead-magnet.question4.asnwer1') !!}
							</label>
							<label class="magnet-label" for="answer-4-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-4-2" type="radio" name="answer-4" class="lead-magnet-radio" ans-text="Occasionally" value="2">
								{!! trans('trp.popup.popup-lead-magnet.question4.asnwer2') !!}
							</label>
							<label class="magnet-label" for="answer-4-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-4-3" type="radio" name="answer-4" class="lead-magnet-radio" ans-text="It happened a few times only" value="3">
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
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-1" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="Yes, to all reviews" value="1">
								{!! trans('trp.popup.popup-lead-magnet.question5.asnwer1') !!}
							</label>
							<label class="magnet-label" for="answer-5-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-2" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="Yes, only to negative reviews" value="2">
								{!! trans('trp.popup.popup-lead-magnet.question5.asnwer2') !!}
							</label>
							<label class="magnet-label" for="answer-5-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-3" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="Yes, from time to time" value="3">
								{!! trans('trp.popup.popup-lead-magnet.question5.asnwer3') !!}
							</label>
							<label class="magnet-label" for="answer-5-4">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-4" type="radio" name="answer-5" class="lead-magnet-radio" ans-text="No" value="4">
								{!! trans('trp.popup.popup-lead-magnet.question5.asnwer4') !!}
							</label>
						</div> 
						<div class="tac" style="display: none;">
							<button class="button" id="magnet-submit" type="submit">{!! trans('trp.popup.popup-lead-magnet.second-step-submit') !!}<div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div></button>
						</div>
					</div>
				</div>
			</div>

		{!! Form::close() !!}

		<div class="magnet-content" style="display: none;">
			
		</div>
	</div>
</div>