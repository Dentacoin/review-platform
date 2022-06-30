<div class="copypaste-wrapper step2" style="display: none;">
	<p class="popup-desc">
		{!! nl2br(trans('trp.popup.popup-invite.step2.instructions')) !!}
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
					{!! nl2br(trans('trp.popup.popup-invite.invite-to-hubapp')) !!}
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