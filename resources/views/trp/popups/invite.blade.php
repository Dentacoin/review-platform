<div class="popup fixed-popup" id="popup-invite">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.popup-invite.title')) !!}
			
		</h2>

		<div class="popup-tabs invite-tabs flex flex-mobile">
			<a class="active col" href="javascript:;" data-invite="copypaste" style="z-index: 3">
				Copy/Paste from file
			</a>
			<a class="col" href="javascript:;" data-invite="email" style="z-index: 2">
				Add manually
			</a>
			<a class="col" href="javascript:;" data-invite="whatsapp" style="z-index: 1">
				Send via WhatsApp
			</a>
			<a class="col" href="javascript:;" data-invite="file">
				Import from file
			</a>
			@if(false)
				<a class="col" href="javascript:;" data-invite="link">
					Get a referral link
				</a>
			@endif
		</div>

		<div id="invite-option-copypaste" class="invite-content" style="">

			<h4 class="popup-title">
				<!-- {!! nl2br(trans('trp.popup.popup-invite.subtitle')) !!} -->
				COPY / PASTE FROM FILE
			</h4>

			<div class="copypaste-wrapper step1" style="display: block;">
				<p class="popup-desc">
					• Paste patient info.
				</p>
				<br/>
				<br/>

				<h4 class="step-title"><span>Step 1:</span> Paste patient email and name</h4>

				{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form', 'url' => getLangUrl('profile/invite-copypaste'), 'radio-id' => 'asd' )) !!}
					{!! csrf_field() !!}

					<textarea class="copypaste" id="copypaste" name="copypaste" placeholder="{!! trans('trp.popup.popup-invite.paste-file-placeholder') !!}"></textarea>

					<div class="alert invite-alert" style="display: none; margin-top: 20px;">
					</div>

					<div class="tac">
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite">
					</div>
				{!! Form::close() !!}
			</div>

			<div class="copypaste-wrapper step2" style="display: none;">
				<p class="popup-desc">
					• Now let's match the information with patient details. Choose the box that is matching the titles.
				</p>
				<br/>
				<br/>

				<h4 class="step-title"><span>Step 2:</span> Select patient emails</h4>

				{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-emails', 'url' => getLangUrl('profile/invite-copypaste-emails'), 'radio-id' => 'sdf' )) !!}
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
					</div>
				{!! Form::close() !!}
			</div>
		</div>

		<div id="invite-option-email" class="invite-content" style="display: none;">

			<h4 class="popup-title">
				<!-- {!! nl2br(trans('trp.popup.popup-invite.subtitle')) !!} -->
				Add manually
			</h4>

			<p class="popup-desc">
				• {!! nl2br(trans('trp.popup.popup-invite.instructions')) !!}
			</p>
			<br/>
			<br/>

			{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-form', 'url' => getLangUrl('profile/invite') )) !!}
				{!! csrf_field() !!}
				<div class="flex">
					<div class="col">
						<div class="modern-field">
							<input type="text" name="name" id="invite-name" class="modern-input invite-name" autocomplete="off">
							<label for="invite-name">
								<span>{!! nl2br(trans('trp.popup.popup-invite.name')) !!}:</span>
							</label>
						</div>
					</div>
					<div class="col">
						<div class="modern-field">
							<input type="email" name="email" id="invite-name" class="modern-input invite-email" autocomplete="off">
							<label for="invite-name">
								<span>{!! nl2br(trans('trp.popup.popup-invite.email')) !!}:</span>
							</label>
						</div>
					</div>
				</div>

				<div class="alert invite-alert" style="display: none; margin-top: 20px;">
				</div>
				<!--
					<a href="javascript:;" class="add-patient">+ Add another patient</a>
				-->

				<div class="tac">
					<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite">
				</div>
			{!! Form::close() !!}
		</div>

		<div id="invite-option-whatsapp" class="invite-content" style="display: none;">
			<h4 class="popup-title">
				<!-- {!! nl2br(trans('trp.popup.popup-invite.subtitle')) !!} -->
				Send via WhatsApp
			</h4>

			<p class="popup-desc">
				• Send invitation link to your patient via WhatsApp.
			</p>
			<br/>
			<br/>

			<div class="tac">
				<a href="javascript:;" data-url="{!! getLangUrl('profile/invite-whatsapp') !!}" class="whatsapp-button">{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite<i class="fab fa-whatsapp"></i></a>
			</div>

			<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>
		</div>

		<div id="invite-option-file" class="invite-content" style="display: none;">
			<h4 class="popup-title">
				<!-- {!! nl2br(trans('trp.popup.popup-invite.subtitle')) !!} -->
				Import from file
			</h4>

			<div class="copypaste-wrapper step1" style="display: block;">
				<p class="popup-desc">
					• Upload a .csv or .txt file with multiple patient emails and names.
				</p>
				<br/>
				<br/>

				<h4 class="step-title"><span>Step 1:</span> UPLOAD FILE WITH PATIENT NAMES AND EMAILS</h4>

				{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-file-form', 'url' => getLangUrl('profile/invite-file'), 'files' => 'true', 'radio-id' => 'dfg')) !!}
					{!! csrf_field() !!}

					<label for="invite-file" class="label-file clearfix">
						<span></span>
						<div class="browse">Browse</div>
						<input type="file" name="invite-file" id="invite-file" accept=".csv,.txt">
					</label>
					<div class="flex file-info">
						<div class="col">
							<a href="{{ url('sample-import-file.csv') }}" class="download-sample"><img src="{{ url('img-trp/download.png') }}"/>Download sample</a>
						</div>
						<div class="col">
							<span>Acceptable file types: .csv or .txt files</span>
						</div>
					</div>

					<div class="alert invite-alert" style="display: none; margin-top: 20px;">
					</div>

					<div class="tac">
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!} Invite">
					</div>
				{!! Form::close() !!}
			</div>

			<div class="copypaste-wrapper step2" style="display: none;">
				<p class="popup-desc">
					• Now let's match the information with patient details. Choose the box that is matching the titles.
				</p>
				<br/>
				<br/>

				<h4 class="step-title"><span>Step 2:</span> Select patient emails</h4>

				{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form-emails', 'url' => getLangUrl('profile/invite-copypaste-emails'), 'radio-id' => 'fgh' )) !!}
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
					</div>
				{!! Form::close() !!}
			</div>
		</div>

		@if(false)
			<div id="invite-option-link" class="invite-content" style="display: none;">
				<h4 class="popup-title">
					{!! nl2br(trans('trp.popup.popup-invite.subtitle')) !!}
				</h4>

				<p class="popup-desc">
					{!! nl2br(trans('trp.popup.popup-invite.hint')) !!}
				</p>
					
				<br/>
				<br/>
				<p class="info">
					<img src="img/info.png"/>
					Below you’ll find your invitation link. Copy it and send it using your favorite instant messanger or social network.
				</p>

				<div class="flex">
					<div class="flex-10">
						<input type="text" id="invite-url" class="input select-me" name="link" value="{{ getLangUrl('invite/'.$user->id.'/'.$user->get_invite_token()) }}">
					</div>
					<div class="flex-2">
						<a class="copy-link button" href="javascript:;">
							Copy
						</a>
					</div>
				</div>
			</div>
		@endif
	</div>
</div>