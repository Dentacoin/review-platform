<div class="popup fixed-popup" id="add-team-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {{ trans('trp.common.back') }}</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.add-team-popup.title')) !!}
		</h2>

		<h4 class="popup-title">
			{!! nl2br(trans('trp.popup.add-team-popup.subtitle')) !!}
		</h4>

		<p class="popup-desc">
			{!! nl2br(trans('trp.popup.add-team-popup.hint')) !!}
		</p>

		<div class="search-dentist dentist-suggester-wrapper suggester-wrapper iconn">
			<div class="modern-field">
				<input type="text" name="search-dentist" class="modern-input dentist-suggester suggester-input" value="" autocomplete="off">
				<label for="invitedentist">
					<span>{!! nl2br(trans('trp.popup.add-team-popup.search')) !!}</span>
				</label>
				<p>{!! nl2br(trans('trp.popup.verification-popup.add-dentist.hint')) !!}</p>

				<div class="suggest-results">
				</div>
				<input type="hidden" class="suggester-hidden" name="clinic_id" value="">
				<i class="search-icon fas fa-search"></i>
			</div>
		</div>

		<div class="alert" id="dentist-add-result" style="display: none; margin-bottom: 20px;">
		</div>

		<div id="invite-option-email" class="invite-content add-t">
			<p class="info">
				<img src="img/info.png"/>
				{!! nl2br(trans('trp.popup.add-team-popup.invite')) !!}
			</p>

			{!! Form::open(array('method' => 'post', 'files'=> true, 'class' => 'search-dentist-form add-team-member-form', 'url' => getLangUrl('profile/invite-new') )) !!}
				{!! csrf_field() !!}
				<input type="hidden" name="check-for-same" class="check-for-same">
				<div class="flex">
					<div style="margin-bottom: 20px;">
						<label for="add-avatar-member-login" class="image-label">
							<div class="centered-hack">
								<i class="fas fa-plus"></i>
								<p>
									{!! nl2br(trans('trp.popup.popup-register.add-photo')) !!}													
								</p>
							</div>
				    		<div class="loader">
				    			<i class="fas fa-circle-notch fa-spin"></i>
				    		</div>
							<input type="file" name="image" class="add-avatar-member" id="add-avatar-member-login" upload-url="{{ getLangUrl('register/upload') }}">
							
						</label>
						<input type="hidden" class="photo-name-team" name="photo" >
						<input type="hidden" class="photo-thumb-team" name="photo-thumb" >
					</div>
					<div class="col">
						<div class="modern-field">
							<input type="text" class="modern-input team-member-name" id="team-member-name-logged" name="name"></textarea>
							<label for="team-member-name-logged">
								<span>{!! nl2br(trans('trp.popup.add-team-popup.name')) !!}</span>
							</label>
						</div>
					</div>
					<div class="col">
						<div class="modern-field alert-after">
				  			<select name="team-job" id="team-member-job-logged" class="modern-input team-member-job">
				  				@foreach(config('trp.team_jobs') as $k => $v)
				  					<option value="{{ $k }}">{{ $v }}</option>
				  				@endforeach
				  			</select>
							<label for="team-member-job-logged">
								<span>{!! nl2br(trans('trp.popup.add-team-popup.position')) !!}:</span>
							</label>
						</div>
					</div>
					<div class="col mail-col" style="display: none;">
						<div class="modern-field">
							<input type="email" class="modern-input team-member-email" id="team-member-email-logged" name="email" placeholder="{{ trans('trp.common.optional') }}"></textarea>
							<label for="team-member-email-logged">
								<span>{!! nl2br(trans('trp.popup.add-team-popup.email')) !!}</span>
							</label>
						</div>
					</div>
				</div>
				<div class="more-d">

				</div>

				<div class="alert member-alert" style="display: none; margin-top: 20px;">
				</div>
				<div class="tac">
					<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.add-team-popup.submit')) !!}">
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="popup fixed-popup popup-existing-dentist" id="popup-existing-dentist">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
            {!! nl2br(trans('trp.popup.add-team-popup.existing-team-title')) !!}
        </h2>

        <div class="existing-dentists">
			
		</div>

	</div>
</div>