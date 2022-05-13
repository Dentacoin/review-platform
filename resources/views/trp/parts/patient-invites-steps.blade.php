<div class="copypaste-wrapper step2" style="display: none;">
	<p class="popup-desc">
		Now let's match the patient names and emails.
		{{-- • {!! nl2br(trans('trp.popup.popup-invite.step2.instructions')) !!} --}}
	</p>
	<br/>
	<br/>

	{!! Form::open([
		'method' => 'post', 
		'class' => 'invite-patient-copy-paste-form-final', 
		'url' => getLangUrl('profile/invite-copypaste-final') 
	]) !!}
		{!! csrf_field() !!}

		<div class="checkboxes-wrapper">
			<div class="checkboxes-inner">
			</div>
		</div>

		<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>

		<div class="flex invite-more-options">
			@if($user->is_partner)
				<label class="checkbox-label invite-hubapp active" for="invite-hubapp-{{ $number }}" >
					<input type="checkbox" class="special-checkbox" id="invite-hubapp-{{ $number }}" name="invite_hubapp" checked="checked"/>
					<div class="checkbox-square">✓</div>
					Invite them to Dentacoin HubApp, too?
				</label>
			@endif
			@include('trp.parts.sample-invite')
		</div>

		<div class="tac">
			<a href="javascript:;" class="white-button bulk-invite-back" step="1">
				<
			</a>
			<button type="submit" class="blue-button final-button">
				<div class="loader"><i></i></div>
				{!! nl2br(trans('trp.popup.popup-invite.send')) !!}
			</button>
		</div>
	{!! Form::close() !!}
</div>

{{-- <div class="copypaste-wrapper step4" style="display: none;">
	<p class="popup-desc">
		• {!! nl2br(trans('trp.popup.popup-invite.step4.instructions')) !!}
	</p>
	<br/>
	<br/>

	<h4 class="step-title">{!! nl2br(trans('trp.popup.popup-invite.step4-title')) !!}</h4>

	{!! Form::open([
		'method' => 'post', 
		'class' => 'invite-patient-copy-paste-form-final', 
		'url' => getLangUrl('profile/invite-copypaste-final') 
	]) !!}
		{!! csrf_field() !!}

		<div class="chosen-patient-info flex flex-center">
			<div class="patient-info-label">
				{!! nl2br(trans('trp.popup.popup-invite.patient-emails')) !!}
			</div>
			<div class="patient-info-value for-email"></div>
		</div>

		<div class="chosen-patient-info flex flex-center">
			<div class="patient-info-label">
				{!! nl2br(trans('trp.popup.popup-invite.patient-names')) !!}
			</div>
			<div class="patient-info-value for-name"></div>
		</div>

		@if($user->is_partner)
			<label class="checkbox-label invite-hubapp" for="invite-hubapp-{{ $number }}" >
				<input type="checkbox" class="special-checkbox" id="invite-hubapp-{{ $number }}" name="invite_hubapp"/>
				<div class="checkbox-square">✓</div>
				Invite to Dentacoin HubApp
			</label>
		@endif

		<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>

		<div class="tac">
			<br/>
			@include('trp.parts.sample-invite')
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="3">
				{!! nl2br(trans('trp.popup.popup-invite.back')) !!}
			</a>
			<button type="submit" class="button final-button copypaste-fourth"><div class="loader"><i></i></div>{!! nl2br(trans('trp.popup.popup-invite.send')) !!}</button>
			<a href="javascript:;" class="button try-invite-again" style="display: none;">{!! nl2br(trans('trp.popup.popup-invite.invite-again')) !!}</a>
		</div>
	{!! Form::close() !!}
</div> --}}