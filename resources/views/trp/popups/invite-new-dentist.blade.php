<div class="popup fixed-popup invite-new-dentist-popup" id="invite-new-dentist-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<i class="fas fa-times"></i>
		</a>
		<h2>
			{!! trans('trp.page.invite.popup.title') !!}
		</h2>
		<h4 class="popup-title">
			{!! trans('trp.page.invite.subtitle') !!}
		</h4>
		@include('trp.parts.invite-new-dentist-form')
	</div>
</div>