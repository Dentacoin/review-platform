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
			@if(false)
				<a class="col" href="javascript:;" data-invite="link">
					Get a referral link
				</a>
			@endif
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
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!}">
					</div>
				{!! Form::close() !!}
			</div>

			@include('trp.parts.patient-invites-steps')
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
							<input type="email" name="email" id="invite-name" class="modern-input invite-email" autocomplete="off">
							<label for="invite-name">
								<span>{!! nl2br(trans('trp.popup.popup-invite.email')) !!}:</span>
							</label>
						</div>
					</div>
				</div>

				<div class="alert invite-alert" style="display: none; margin-top: 20px;">
				</div>
				@if(false)
					<!--
						<a href="javascript:;" class="add-patient">+ Add another patient</a>
					-->
				@endif
				<div class="tac">
					<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!}">
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
				<a href="javascript:;" data-url="{!! getLangUrl('profile/invite-whatsapp') !!}" class="whatsapp-button">{!! nl2br(trans('trp.popup.popup-invite.send')) !!}<i class="fab fa-whatsapp"></i></a>
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
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-invite.send')) !!}">
					</div>
				{!! Form::close() !!}
			</div>

			@include('trp.parts.patient-invites-steps')
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