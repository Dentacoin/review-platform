<div class="copypaste-wrapper step2" style="display: none;">
	<p class="popup-desc">
		• Now let's match the information with patient details. Choose the box that is matching the titles.
	</p>
	<br/>
	<br/>

	<h4 class="step-title"><span>Step 2:</span> Select patient emails</h4>

	{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-emails', 'url' => getLangUrl('profile/invite-copypaste-emails') )) !!}
		{!! csrf_field() !!}

		<div class="checkboxes-wrapper">
			<div class="checkboxes-inner">
			</div>
		</div>

		<div class="tac">
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="1" >
				Back
			</a>
			<input type="submit" class="button" disabled="disabled" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite">
		</div>
	{!! Form::close() !!}
</div>

<div class="copypaste-wrapper step3" style="display: none;">
	<p class="popup-desc">
		• Now let's match the information with patient details. Choose the box that is matching the titles.
	</p>
	<br/>
	<br/>

	<h4 class="step-title"><span>Step 3:</span> Select patient names</h4>

	{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-names', 'url' => getLangUrl('profile/invite-copypaste-names') )) !!}
		{!! csrf_field() !!}

		<div class="checkboxes-wrapper">
			<div class="checkboxes-inner">
			</div>
		</div>

		<div class="chosen-patient-info flex flex-center">
			<div class="patient-info-label">
				Patient emails:
			</div>
			<div class="patient-info-value for-email"></div>
		</div>

		<div class="tac">
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="2" >
				Back
			</a>
			<input type="submit" class="button" disabled="disabled" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite">
		</div>
	{!! Form::close() !!}
</div>

<div class="copypaste-wrapper step4" style="display: none;">
	<p class="popup-desc">
		• Now let's match the information with patient details.
	</p>
	<br/>
	<br/>

	<h4 class="step-title"><span>Step 4:</span> Last check if it is matching</h4>

	{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-final', 'url' => getLangUrl('profile/invite-copypaste-final') )) !!}
		{!! csrf_field() !!}

		<div class="chosen-patient-info flex flex-center">
			<div class="patient-info-label">
				Patient emails:
			</div>
			<div class="patient-info-value for-email"></div>
		</div>

		<div class="chosen-patient-info flex flex-center">
			<div class="patient-info-label">
				Patient names:
			</div>
			<div class="patient-info-value for-name"></div>
		</div>

		<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>

		<div class="tac">
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="3" >
				Back
			</a>
			<button type="submit" class="button final-button"><div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite</button>
			<a href="javascript:;" class="button try-invite-again" style="display: none;">Try again</a>
		</div>
	{!! Form::close() !!}
</div>