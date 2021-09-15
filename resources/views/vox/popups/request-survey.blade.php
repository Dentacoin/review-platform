<div class="popup fixed-popup popup-with-background request-survey-popup close-on-shield active" id="request-survey-popup" scss-load="vox-popup-request-survey">
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
					{{ trans('vox.page.home.request-survey.popup.title') }}
				</p>
				<h4>
					{{ trans('vox.page.home.request-survey.popup.description') }} 
				</h4>

				<form class="form" action="{{ getLangUrl('request-survey') }}" method="post" id="request-survey-form">
					{!! csrf_field() !!}
					
					<div class="request-row alert-after">
						<div class="modern-field">
							<input type="text" name="title" id="title" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
							<label for="title">
								<span>{{ trans('vox.page.home.request-survey.popup.topic') }}</span>
							</label>
						</div>
					</div>

					<div class="request-row radios-row alert-after">
						<div class="target-label">
							{{ trans('vox.page.home.request-survey.popup.target') }}
						</div>
						<div class="modern-radios">
							<div class="radio-label">
							  	<label for="target-worldwide">
									<span class="modern-radio">
										<span></span>
									</span>
							    	<input class="type-radio" type="radio" name="target" id="target-worldwide" value="worldwide">
							    	{{ trans('vox.page.home.request-survey.popup.target.worldwide') }}
							  	</label>
							</div>
							<div class="radio-label">
							  	<label for="target-specific">
									<span class="modern-radio">
										<span></span>
									</span>
							    	<input class="type-radio" type="radio" name="target" id="target-specific" value="specific">
							    	{{ trans('vox.page.home.request-survey.popup.target.specific-countries') }}							    	
							  	</label>
							</div>
						</div>
					</div>

					<div class="request-row target-row alert-after" style="display: none;">
						<div class="target-countries-label">
							{{ trans('vox.page.home.request-survey.popup.target-countries') }}
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
								<span>{{ trans('vox.page.home.request-survey.popup.target-group') }}</span>
							</label>
						</div>
					</div>

					<div class="request-row alert-after">
						<div class="modern-field">
							<textarea name="topics" id="topics" class="modern-input" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></textarea>
							<label for="topics">
								<span>{{ trans('vox.page.home.request-survey.popup.describe') }}</span>
							</label>
						</div>
					</div>

					<div class="tac">
						<button type="submit" class="blue-button">{{ trans('vox.page.home.request-survey.popup.send') }}</button>
					</div>
					<div class="alert alert-success" style="display: none;">{{ trans('vox.page.home.request-survey.popup.success') }}</div>
				</form>
			</div>
		</div>
	</div>
</div>