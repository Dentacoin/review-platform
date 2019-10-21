<div class="popup fixed-popup popup-with-background request-survey-popup close-on-shield" id="request-survey-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
			<div class="back-home">
				{!! nl2br(trans('vox.daily-polls.popup.back')) !!}
			</div>
		</a>
		<div class="flex flex-mobile flex-center break-tablet">
			<div class="content">
				<p class="h1">
					REQUEST CUSTOM SURVEY
				</p>
				<h4>
					Please describe your survey request in detail. <br/> Our team will gladly assess it, get in touch and compile it for you! 
				</h4>

				<form class="form" action="{{ getLangUrl('request-survey') }}" method="post" id="request-survey-form">
					{!! csrf_field() !!}
					
					<div class="request-row alert-after">
						<div class="modern-field">
							<input type="text" name="title" id="title" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="title">
								<span>Survey topic:</span>
							</label>
						</div>
					</div>

					<div class="request-row radios-row alert-after">
						<div class="target-label">
							Target group location/s: 
						</div>
						<div class="modern-radios">
							<div class="radio-label">
							  	<label for="target-worldwide">
									<span class="modern-radio">
										<span></span>
									</span>
							    	<input class="type-radio" type="radio" name="target" id="target-worldwide" value="worldwide">
							    	Worldwide
							  	</label>
							</div>
							<div class="radio-label">
							  	<label for="target-specific">
									<span class="modern-radio">
										<span></span>
									</span>
							    	<input class="type-radio" type="radio" name="target" id="target-specific" value="specific">
							    	Specific countries							    	
							  	</label>
							</div>
						</div>
					</div>

					<div class="request-row target-row alert-after" style="display: none;">
						<div class="target-countries-label">
							Please select your target country/ies:
						</div>
						<div class="col">
				  			<select name="target-countries[]" class="modern-input select2">
				  				@foreach( $countries as $country )
				  					<option value="{{ $country->id }}" code="{{ $country->code }}" {!! !empty($user->country_id) && $user->country_id == $country->id ? 'selected="selected"' : '' !!}>{{ $country->name }}</option>
				  				@endforeach
				  			</select>
						</div>
					</div>

					<div class="request-row alert-after">
						<div class="modern-field">
							<input type="text" name="other-specifics" id="other-specifics" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="other-specifics">
								<span>Any other specifics of your target group?</span>
							</label>
						</div>
					</div>

					<div class="request-row alert-after">
						<div class="modern-field">
							<textarea name="topics" id="topics" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></textarea>
							<label for="topics">
								<span>Describe the topics and the questions you'd like us to ask</span>
							</label>
						</div>
					</div>

					<div class="tac">
						<button type="submit" class="blue-button">SEND REQUEST</button>
					</div>
					<div class="alert alert-success" style="display: none;">Thank you for helping us improve DentaVox. We'll check your suggestion and get back to you in case we have any questions</div>
				</form>
			</div>
		</div>
	</div>
</div>