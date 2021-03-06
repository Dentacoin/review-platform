<div class="popup fixed-popup invite-new-dentist-success-popup active removable" id="invite-new-dentist-success-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<i class="fas fa-times"></i>
		</a>
		<img src="{{ url('img-trp/verification-check.png') }}">
		<h2>
			{!! trans('trp.page.invite.popup.success.title') !!}
		</h2>
		<p>
			@if(!empty($user))
				{!! nl2br(trans('trp.page.invite.popup.success.text', [ 'name' => '<span id="inv_dent_name">[Clinic/Dentist Name]</span>' ,'amount' => '<span>'.App\Models\Reward::getReward('patient_add_dentist').' DCN</span>'])) !!}
			@else
				{!! nl2br(trans('trp.page.invite.popup.success.text.no-user', [ 'name' => '<span id="inv_dent_name">[Clinic/Dentist Name]</span>' ,'amount' => '<span>'.App\Models\Reward::getReward('patient_add_dentist').' DCN</span>'])) !!}
			@endif
		</p>
		@if(!empty($user))
			<a href="javascript:;" class="button close-popup">{!! trans('trp.page.invite.popup.success.button') !!}</a>
		@else
			<a href="javascript:;" class="button close-popup open-dentacoin-gateway patient-login">{!! trans('trp.page.invite.popup.success.sign-up') !!}</a>
		@endif
	</div>
</div>