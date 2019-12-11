<div class="popup fixed-popup popup-with-background request-survey-popup close-on-shield" id="request-survey-patient-popup">
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
					Share your survey idea
				</p>

				<form class="form" action="{{ getLangUrl('request-survey-patients') }}" method="post" id="request-survey-form">
					{!! csrf_field() !!}

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
					<div class="alert alert-success" style="display: none;">Thank you for helping us improve DentaVox</div>
				</form>
			</div>
		</div>
	</div>
</div>