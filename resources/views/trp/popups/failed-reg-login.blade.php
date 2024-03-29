<div class="popup failed-popup no-image active removable" id="failed-popup" scss-load="trp-popup-failed">
	<div class="popup-inner">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>
		<div class="failed-content">
			<h2 class="mont">{!! nl2br(trans('trp.popup.failed-login-reg.title')) !!}</h2>
			<div class="flex flex-center">
				<div class="col">
					<img class="failed-image" src="{{ url('img/system-update.png') }}">
				</div>
				<div class="col">
					<p>
						{!! nl2br(trans('trp.popup.failed-login-reg.text')) !!}
					</p>
				</div>
			</div>
		</div>
	</div>
</div>