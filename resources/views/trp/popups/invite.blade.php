<div class="popup fixed-popup popup-invite-patients" id="popup-invite">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.popup-invite.title')) !!}
		</h2>

		<div class="popup-tabs invite-tabs colorful-tabs flex flex-mobile">
			<a class="active col" href="javascript:;" data-invite="copypaste" style="z-index: 3">
				{!! nl2br(trans('trp.popup.popup-invite.copypaste.title')) !!}
			</a>
			<a class="col" href="javascript:;" data-invite="email" style="z-index: 2">
				{!! nl2br(trans('trp.popup.popup-invite.manually.title')) !!}
			</a>
			<a class="col" href="javascript:;" data-invite="whatsapp" style="z-index: 1">
				{!! nl2br(trans('trp.popup.popup-invite.whatsapp.title')) !!}
			</a>
			<a class="col" href="javascript:;" data-invite="file">
				{!! nl2br(trans('trp.popup.popup-invite.file.title')) !!}
			</a>
		</div>

		<div id="invite-option-copypaste" class="invite-content" radio-id="copypasteid" >

			<h4 class="popup-title">
				{!! nl2br(trans('trp.popup.popup-invite.copypaste.title')) !!}
			</h4>

			<div class="copypaste-wrapper step1" style="display: block;">
				<p class="popup-desc">
					• {!! nl2br(trans('trp.popup.popup-invite.copypaste.instructions')) !!}
				</p>
				<br/>
				<br/>

				<h4 class="step-title">{!! nl2br(trans('trp.popup.popup-invite.copypaste.step1-title')) !!}</h4>

				{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-copy-paste-form', 'url' => getLangUrl('profile/invite-copypaste') )) !!}
					{!! csrf_field() !!}

					<textarea class="copypaste" id="copypaste" name="copypaste" placeholder="{!! trans('trp.popup.popup-invite.paste-file-placeholder') !!}"></textarea>

					<div class="alert invite-alert" style="display: none; margin-top: 20px;">
					</div>

					<div class="tac">
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.next')) !!}">
					</div>
				{!! Form::close() !!}
			</div>

			@include('trp.parts.patient-invites-steps', [
				'number' => 1,
			])
		</div>

		<div id="invite-option-email" class="invite-content" style="display: none;">

			<h4 class="popup-title">
				{!! nl2br(trans('trp.popup.popup-invite.manually.title')) !!}
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
							<input type="email" name="email" id="invite-email" class="modern-input invite-email" autocomplete="off">
							<label for="invite-email">
								<span>{!! nl2br(trans('trp.popup.popup-invite.email')) !!}:</span>
							</label>
						</div>
					</div>
				</div>

				@if($user->is_partner)
					<label class="checkbox-label invite-hubapp manual-hubapp" for="invite-hubapp" >
						<input type="checkbox" class="special-checkbox" id="invite-hubapp" name="invite_hubapp"/>
						<div class="checkbox-square">✓</div>
						Invite to Dentacoin HubApp
					</label>
				@endif

				<div class="alert invite-alert" style="display: none; margin-top: 20px;">
				</div>
				<div class="tac">
					@include('trp.parts.sample-invite')
					<input type="submit" class="button manually-send" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!}">
				</div>
			{!! Form::close() !!}
		</div>

		<div id="invite-option-whatsapp" class="invite-content" style="display: none;">
			<h4 class="popup-title">
				{!! nl2br(trans('trp.popup.popup-invite.whatsapp.title')) !!}
			</h4>

			<p class="popup-desc">
				• {!! nl2br(trans('trp.popup.popup-invite.whatsapp.instructions')) !!}
			</p>
			<br/>
			<br/>

			<div class="tac">
				<a href="javascript:;" data-url="{!! getLangUrl('profile/invite-whatsapp') !!}" class="whatsapp-button">
					{!! nl2br(trans('trp.popup.popup-invite.whatsapp-send')) !!}
					<img src="{{ url('img/social-network/whatsapp.svg') }}"/>
				</a>
			</div>

			<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>
		</div>

		<div id="invite-option-file" class="invite-content" radio-id="fileid" style="display: none;">
			<h4 class="popup-title">
				{!! nl2br(trans('trp.popup.popup-invite.file.title')) !!}
			</h4>

			<div class="copypaste-wrapper step1" style="display: block;">
				<p class="popup-desc">
					• {!! nl2br(trans('trp.popup.popup-invite.file.instructions')) !!}
				</p>
				<br/>
				<br/>

				<h4 class="step-title">{!! nl2br(trans('trp.popup.popup-invite.file.step1-title')) !!}</h4>

				{!! Form::open(array('method' => 'post', 'class' => 'invite-patient-file-form', 'url' => getLangUrl('profile/invite-file'), 'files' => 'true' )) !!}
					{!! csrf_field() !!}

					<label for="invite-file" class="label-file clearfix">
						<span></span>
						<div class="browse">{!! nl2br(trans('trp.popup.popup-invite.file.browse')) !!}</div>
						<input type="file" name="invite-file" id="invite-file" accept=".csv,.txt">
					</label>
					<div class="flex file-info">
						<div class="col">
							<a href="{{ url('sample-import-file.csv') }}" class="download-sample"><img src="{{ url('img-trp/download.png') }}"/>{!! nl2br(trans('trp.popup.popup-invite.file.download')) !!}</a>
						</div>
						<div class="col">
							<span>{!! nl2br(trans('trp.popup.popup-invite.file.acceptable')) !!}</span>
						</div>
					</div>

					<div class="alert invite-alert" style="display: none; margin-top: 20px;">
					</div>

					<div class="tac">
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.next')) !!}">
					</div>
				{!! Form::close() !!}
			</div>

			@include('trp.parts.patient-invites-steps', [
				'number' => 2,
			])
		</div>
	</div>
</div>

@include('trp.popups.invite-sample')