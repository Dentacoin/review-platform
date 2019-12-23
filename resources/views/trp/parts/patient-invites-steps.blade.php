<div class="copypaste-wrapper step2" style="display: none;">
	<p class="popup-desc">
		• {!! nl2br(trans('trp.popup.popup-invite.step2.instructions')) !!}
	</p>
	<br/>
	<br/>

	<h4 class="step-title">{!! nl2br(trans('trp.popup.popup-invite.step2-title')) !!}</h4>

	{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-emails', 'url' => getLangUrl('profile/invite-copypaste-emails') )) !!}
		{!! csrf_field() !!}

		<div class="checkboxes-wrapper">
			<div class="checkboxes-inner">
			</div>
		</div>

		<div class="tac">
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="1">
				{!! nl2br(trans('trp.popup.popup-invite.back')) !!}
			</a>
			<input type="submit" class="button" disabled="disabled" value="{!! nl2br(trans('trp.popup.popup-invite.next')) !!}">
		</div>
	{!! Form::close() !!}
</div>

<div class="copypaste-wrapper step3" style="display: none;">
	<p class="popup-desc">
		• {!! nl2br(trans('trp.popup.popup-invite.step3.instructions')) !!}
	</p>
	<br/>
	<br/>

	<h4 class="step-title">{!! nl2br(trans('trp.popup.popup-invite.step3-title')) !!}</h4>

	{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-names', 'url' => getLangUrl('profile/invite-copypaste-names') )) !!}
		{!! csrf_field() !!}

		<div class="checkboxes-wrapper">
			<div class="checkboxes-inner">
			</div>
		</div>

		<div class="chosen-patient-info flex flex-center">
			<div class="patient-info-label">
				{!! nl2br(trans('trp.popup.popup-invite.patient-emails')) !!}
			</div>
			<div class="patient-info-value for-email"></div>
		</div>

		<div class="tac">
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="2">
				{!! nl2br(trans('trp.popup.popup-invite.back')) !!}
			</a>
			<input type="submit" class="button" disabled="disabled" value="{!! nl2br(trans('trp.popup.popup-invite.next')) !!}">
		</div>
	{!! Form::close() !!}
</div>

<div class="copypaste-wrapper step4" style="display: none;">
	<p class="popup-desc">
		• {!! nl2br(trans('trp.popup.popup-invite.step4.instructions')) !!}
	</p>
	<br/>
	<br/>

	<h4 class="step-title">{!! nl2br(trans('trp.popup.popup-invite.step4-title')) !!}</h4>

	{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-final', 'url' => getLangUrl('profile/invite-copypaste-final') )) !!}
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

		<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>

		<div class="tac">
			<a href="javascript:;" class="button button-inner-white bulk-invite-back" step="3">
				{!! nl2br(trans('trp.popup.popup-invite.back')) !!}
			</a>
			<button type="submit" class="button final-button copypaste-fourth"><div class="loader"><i class="fas fa-circle-notch fa-spin fa-3x fa-fw"></i></div>{!! nl2br(trans('trp.popup.popup-invite.send')) !!}</button>
			<a href="javascript:;" class="button try-invite-again" style="display: none;">{!! nl2br(trans('trp.popup.popup-invite.invite-again')) !!}</a>
		</div>
	{!! Form::close() !!}
</div>