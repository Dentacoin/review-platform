<div class="popup with-image popup-invite-patients" scss-load="trp-popup-invite-patient" js-load="invite-patient" id="popup-invite">
	<img class="popup-image" src="{{ url('img-trp/popup-images/invite-patient.png') }}"/>
	<div class="popup-inner new-invite">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2 class="mont">
			{!! nl2br(trans('trp.popup.popup-invite.title')) !!}
		</h2>

		<div class="invite-tabs popup-tabs">
			<div class="popup-tabs-inner">
				<a class="active popup-tab" href="javascript:;" data-invite="copypaste" style="z-index: 3">
					{!! nl2br(trans('trp.popup.popup-invite.copypaste.title')) !!}
				</a>
				<a class="popup-tab" href="javascript:;" data-invite="whatsapp" style="z-index: 1">
					{!! nl2br(trans('trp.popup.popup-invite.whatsapp.title')) !!}
				</a>
				<a class="popup-tab" href="javascript:;" data-invite="file">
					{!! nl2br(trans('trp.popup.popup-invite.file.title')) !!}
				</a>
			</div>
		</div>

		<div id="invite-option-copypaste" class="invite-content" radio-id="copypasteid" >

			<div class="copypaste-wrapper step1" style="display: block;">
				<p class="popup-desc">
					{!! nl2br(trans('trp.popup.popup-invite.copypaste.instructions')) !!}
				</p>
				<br/>
				<br/>

				<h5>
					{!! nl2br(trans('trp.popup.popup-invite.copypaste.step1-title')) !!}:
				</h5>

				{!! Form::open([
					'method' => 'post', 
					'class' => 'invite-patient-copy-paste-form', 
					'url' => getLangUrl('profile/invite-copypaste') 
				]) !!}
					{!! csrf_field() !!}

					<textarea class="copypaste" id="copypaste" name="copypaste" placeholder="{!! trans('trp.popup.popup-invite.paste-file-placeholder') !!}"></textarea>

					<div class="alert invite-alert error-on-first-step" style="display: none; margin-bottom: 20px;"></div>

					<div class="tac">
						<input type="submit" class="blue-button" value="{!! nl2br(trans('trp.popup.popup-invite.next')) !!}">
					</div>
				{!! Form::close() !!}
			</div>

			@include('trp.parts.patient-invites-steps', [
				'number' => 1,
			])
		</div>

		<div id="invite-option-whatsapp" class="invite-content" style="display: none;">

			<p class="popup-desc">
				{!! nl2br(trans('trp.popup.popup-invite.whatsapp.description')) !!}
			</p>
			<br/>
			<br/>

			<img class="whatsapp-image" src="{{ url('img-trp/whatsapp-image.svg') }}"/>

			<div class="tac">
				<a href="javascript:;" data-url="{!! getLangUrl('profile/invite-whatsapp') !!}" class="whatsapp-button">
					<img src="{{ url('img-trp/whatsapp-icon.svg') }}"/>
					{!! nl2br(trans('trp.popup.popup-invite.whatsapp-send')) !!}
				</a>
			</div>

			<div class="alert invite-alert" style="display: none; margin-top: 20px;"></div>
		</div>

		<div id="invite-option-file" class="invite-content" radio-id="fileid" style="display: none;">

			<div class="copypaste-wrapper step1" style="display: block;">
				<p class="popup-desc">
					{!! nl2br(trans('trp.popup.popup-invite.file.instructions')) !!}
				</p>
				<br/>
				<br/>

				{!! Form::open([
					'method' => 'post', 
					'class' => 'invite-patient-file-form', 
					'url' => getLangUrl('profile/invite-file'), 
					'files' => 'true'
				]) !!}
					{!! csrf_field() !!}

					<label for="invite-file" class="label-file clearfix">
						<span></span>
						<div class="browse">{!! nl2br(trans('trp.popup.popup-invite.file.browse')) !!}</div>
						<input type="file" name="invite-file" id="invite-file" accept=".csv,.txt">
					</label>
					<div class="flex flex-mobile file-info">
						<div class="col">
							<a href="{{ url('sample-import-file.csv') }}" class="download-sample">
								<img src="{{ url('img-trp/download.png') }}"/>{!! nl2br(trans('trp.popup.popup-invite.file.download')) !!}
							</a>
						</div>
						<div class="col">
							<span>{!! nl2br(trans('trp.popup.popup-invite.file.acceptable')) !!}</span>
						</div>
					</div>

					<div class="alert invite-alert first-alert" style="display: none; margin-bottom: 20px;">
					</div>

					<div class="tac">
						<input type="submit" class="blue-button" value="{!! nl2br(trans('trp.popup.popup-invite.next')) !!}">
					</div>
				{!! Form::close() !!}
			</div>

			@include('trp.parts.patient-invites-steps', [
				'number' => 2,
			])
		</div>
	</div>

	<div class="popup-inner success-invite" style="display: none">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>

		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>

		<div class="tac">
			<img src="{{ url('img-trp/check.png') }}" class="check-image"/>
		</div>
		<h2 class="mont">
			{!! nl2br(trans('trp.popup.popup-invite.success')) !!}
		</h2>
		<p class="step-info">
			{!! nl2br(trans('trp.popup.popup-invite.success-description')) !!}
			<br/><br/>
		</p>
		<div class="alert invite-alert" style="display: none;"></div>
		<p class="step-info last">
			{!! nl2br(trans('trp.popup.popup-invite.invite-again')) !!}
		</p>

		<div class="tac">
			<a href="javascript:;" class="close-popup white-button">{{ trans('trp.common.close') }}</a>
			<a href="javascript:;" class="try-invite-again blue-button">{!! nl2br(trans('trp.popup.popup-invite.start-over')) !!}</a>
		</div>
	</div>
</div>

@include('trp.popups.invite-sample')