<div class="popup fixed-popup" id="popup-lead-magnet">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			Quiz: How effectively do you use patient online reviews?
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
						<input type="text" name="name" id="magnet-name" class="modern-input magnet-name" autocomplete="off">
						<label for="magnet-name">
							<span>Enter your practice name:</span>
						</label>
					</div>
					<div class="modern-field alert-after">
						<input type="text" name="website" id="magnet-website" class="modern-input magnet-website" autocomplete="off">
						<label for="magnet-website">
							<span>Website/ FB page:</span>
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
							<span>Your email:</span>
						</label>
					</div>

					<label class="checkbox-label agree-label" for="magnet-agree">
						<input type="checkbox" class="special-checkbox" id="magnet-agree" name="agree" value="1">
						<i class="far fa-square"></i>
						By submitting the form, you agree to our <a class="read-privacy" href="https://dentacoin.com/privacy-policy/" target="_blank">Privacy Policy</a>.
					</label>

					<div class="alert magnet-alert" style="display: none; margin-top: 20px;">
					</div>
					<div class="tac">
						<a href="javascript:;" class="button first-form-button" data-validator="{{ getLangUrl('lead-magnet-step1') }}">Get Started"</a>
					</div>
				</div>
			</div>

			<div class="magnet-content" style="display: none;">

				<div class="flickity-magnet">
					<div class="answer-radios-magnet clearfix">
						<div class="answer-question">
							<h4>1. What is the main priority for your practice management?</h4>
						</div>
						<div class="buttons-list clearfix"> 
							<label class="magnet-label" for="answer-1-1">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-1-1" type="radio" name="answer-1" class="lead-magnet-radio" value="1">
								To acquire new patients
							</label>
							<label class="magnet-label" for="answer-1-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-1-2" type="radio" name="answer-1" class="lead-magnet-radio" value="2">
								To keep existing patients
							</label>
							<label class="magnet-label" for="answer-1-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-1-3" type="radio" name="answer-1" class="lead-magnet-radio" value="3">
								Both
							</label>
						</div>
						<div class="alert alert-warning" style="display: none;">Please, select an answer.</div>
						<div class="tac">
							<a href="javascript:;" class="button magnet-validator">Next</a>
						</div>
					</div>
					<div class="answer-radios-magnet clearfix">
						<div class="answer-question">
							<h4>2. What is your primary online tool for collecting patient reviews?</h4>
						</div>
						<div class="buttons-list clearfix"> 
							<label class="magnet-label" for="answer-2-1">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-1" type="radio" name="answer-2" class="lead-magnet-radio" value="1">
								Your website
							</label>
							<label class="magnet-label" for="answer-2-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-2" type="radio" name="answer-2" class="lead-magnet-radio" value="2">
								Google
							</label>
							<label class="magnet-label" for="answer-2-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-3" type="radio" name="answer-2" class="lead-magnet-radio" value="3">
								Facebook or other social media
							</label>
							<label class="magnet-label" for="answer-2-4">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-4" type="radio" name="answer-2" class="lead-magnet-radio" value="4">
								General review platform (e.g. Trustpilot)
							</label>
							<label class="magnet-label" for="answer-2-5">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-5" type="radio" name="answer-2" class="lead-magnet-radio" value="5">
								Specialized review platform (e.g. Dentacoin Trusted Reviews, Zocdoc.)
							</label>
							<label class="magnet-label" for="answer-2-6">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-2-6" type="radio" name="answer-2" class="lead-magnet-radio" value="6">
								I don’t use one
							</label>
						</div> 
						<div class="alert alert-warning" style="display: none;">Please, select an answer.</div>
						<div class="tac">
							<a href="javascript:;" class="button magnet-validator">Next</a>
						</div>
					</div>
					<div class="answer-radios-magnet clearfix">
						<div class="answer-question">
							<h4>3. Do you typically ask your patients to leave an online review?</h4>
						</div>
						<div class="buttons-list clearfix"> 
							<p>(Select all that apply)</p>
							<label class="magnet-label" for="answer-3-1">
								<i class="far fa-square"></i>
								<input id="answer-3-1" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" value="1">
								Yes, in person
							</label>
							<label class="magnet-label" for="answer-3-2">
								<i class="far fa-square"></i>
								<input id="answer-3-2" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" value="2">
								Yes, by email
							</label>
							<label class="magnet-label" for="answer-3-3">
								<i class="far fa-square"></i>
								<input id="answer-3-3" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" value="3">
								Yes, by SMS
							</label>
							<label class="magnet-label" for="answer-3-4">
								<i class="far fa-square"></i>
								<input id="answer-3-4" type="checkbox" name="answer-3[]" class="lead-magnet-checkbox" value="4">
								No
							</label>
						</div> 
						<div class="alert alert-warning" style="display: none;">Please, select at least one answer.</div>
						<div class="tac">
							<a href="javascript:;" class="button magnet-validator validator-skip">Next</a>
						</div>
					</div>
					<div class="answer-radios-magnet clearfix">
						<div class="answer-question">
							<h4>4. How frequently do you invite patients to leave a review?</h4>
						</div>
						<div class="buttons-list clearfix"> 
							<label class="magnet-label" for="answer-4-1">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-4-1" type="radio" name="answer-4" class="lead-magnet-radio" value="1">
								Every day
							</label>
							<label class="magnet-label" for="answer-4-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-4-2" type="radio" name="answer-4" class="lead-magnet-radio" value="2">
								Occasionally
							</label>
							<label class="magnet-label" for="answer-4-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-4-3" type="radio" name="answer-4" class="lead-magnet-radio" value="3">
								It happened a few times only
							</label>
						</div> 
						<div class="alert alert-warning" style="display: none;">Please, select an answer.</div>
						<div class="tac">
							<a href="javascript:;" class="button magnet-validator">Next</a>
						</div>
					</div>
					<div class="answer-radios-magnet clearfix">
						<div class="answer-question">
							<h4>5. Do you reply to online reviews?</h4>
						</div>
						<div class="buttons-list clearfix"> 
							<label class="magnet-label" for="answer-5-1">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-1" type="radio" name="answer-5" class="lead-magnet-radio" value="1">
								Yes, to all reviews
							</label>
							<label class="magnet-label" for="answer-5-2">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-2" type="radio" name="answer-5" class="lead-magnet-radio" value="2">
								Yes, only to negative reviews
							</label>
							<label class="magnet-label" for="answer-5-3">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-3" type="radio" name="answer-5" class="lead-magnet-radio" value="3">
								Yes, from time to time
							</label>
							<label class="magnet-label" for="answer-5-4">
								<span class="modern-radio">
									<span></span>
								</span>
								<input id="answer-5-4" type="radio" name="answer-5" class="lead-magnet-radio" value="4">
								No
							</label>
						</div> 
						<div class="alert alert-warning" style="display: none;">Please, select an answer.</div>
						<div class="tac">
							<button class="button" id="magnet-submit" onclick="LeadMagenet()" type="submit">Calculate</button>
						</div>
					</div>
				</div>
			</div>

		{!! Form::close() !!}

		<div class="magnet-content" style="display: none;">
			
		</div>
	</div>
</div>