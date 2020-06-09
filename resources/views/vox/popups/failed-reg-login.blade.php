<div class="popup fixed-popup popup-with-background failed-popup close-on-shield" id="failed-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<i class="fas fa-times"></i>
		</a>
		<div class="failed-content">
			<h2>{!! nl2br(trans('vox.popup.failed-login-reg.title')) !!}</h2>
			<div class="flex flex-center">
				<div class="col">
					<img src="{{ url('img/system-update.png') }}">
				</div>
				<div class="col">
					<p>
						{!! nl2br(trans('vox.popup.failed-login-reg.text')) !!}
					</p>
				</div>
			</div>
		</div>
	</div>
</div>