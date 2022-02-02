<div class="popup fixed-popup" id="add-team-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
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
				<input type="text" name="search-dentist" class="modern-input dentist-suggester suggester-input" value="" autocomplete="off"/>
				<label for="invitedentist">
					<span>{!! nl2br(trans('trp.popup.add-team-popup.search')) !!}</span>
				</label>
				<p>{!! nl2br(trans('trp.popup.verification-popup.add-dentist.hint')) !!}</p>

				<div class="suggest-results"></div>
				<input type="hidden" class="suggester-hidden" name="clinic_id" value=""/>
				<div class="search-wrap">
					<img src="{{ url('img/white-search.png') }}"/>
				</div>
			</div>
		</div>

		<div class="alert" id="dentist-add-result" style="display: none; margin-bottom: 20px;"></div>

		<div id="invite-option-email" class="invite-content add-t">
			<p class="info">
				<img src="img/info.png"/>
				{!! nl2br(trans('trp.popup.add-team-popup.invite')) !!}
			</p>

			{!! Form::open(array('method' => 'post', 'files'=> true, 'class' => 'search-dentist-form add-team-member-form', 'url' => getLangUrl('profile/invite-new') )) !!}
				{!! csrf_field() !!}
				<input type="hidden" name="check-for-same" class="check-for-same"/>
				
				<div class="flex">
					<div class="upload-image-wrapper image-team-wrapper" style="margin-bottom: 20px;">
						<label for="add-avatar-member-login" class="image-label team-label-image">
							<div class="centered-hack">
								<img src="{{ url('img/plus.svg') }}"/>
								<p>
									{!! nl2br(trans('trp.popup.popup-register.add-photo')) !!}													
								</p>
							</div>
				    		<div class="loader">
				    			<i></i>
				    		</div>
							<input type="file" name="image" class="add-avatar-member" accept="image/png,image/jpeg,image/jpg" id="add-avatar-member-login" upload-url="{{ getLangUrl('register/upload') }}"/>
							<input type="hidden" name="avatar" class="avatar"/>
						</label>
						
						<div class="cropper-container add-team-cropper"></div>
						<div class="avatar-name-wrapper">
							<span class="avatar-name"></span>
							<button class="destroy-croppie" type="button">×</button>
						</div>

						<div class="max-size-label">
							<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="width-100">
								<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z" class=""></path>
							</svg>
							{{ trans('trp.popup.add-branch.image-max-size') }}
						</div>
						<div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
					</div>

					<div class="col">
						<div class="modern-field">
							<input type="text" class="modern-input team-member-name" id="team-member-name-logged" name="name"/>
							<label for="team-member-name-logged">
								<span>{!! nl2br(trans('trp.popup.add-team-popup.name')) !!}</span>
							</label>
						</div>
					</div>
					<div class="col">
						<div class="modern-field alert-after">
				  			<select name="team-job" id="team-member-job-logged" class="modern-input team-member-job">
				  				@foreach(config('trp.team_jobs') as $k => $v)
				  					<option value="{{ $k }}">{{ trans('trp.team-jobs.'.$k) }}</option>
				  				@endforeach
				  			</select>
							<label for="team-member-job-logged">
								<span>{!! nl2br(trans('trp.popup.add-team-popup.position')) !!}:</span>
							</label>
						</div>
					</div>
					<div class="col mail-col" style="display: none;">
						<div class="modern-field">
							<input type="email" class="modern-input team-member-email" id="team-member-email-logged" name="email" placeholder="{{ trans('trp.common.optional') }}"/>
							<label for="team-member-email-logged">
								<span>{!! nl2br(trans('trp.popup.add-team-popup.email')) !!}</span>
							</label>
						</div>
					</div>
				</div>
				<div class="more-d"></div>

				<div class="alert member-alert" style="display: none; margin-top: 20px;"></div>
				<div class="tac">
					<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.add-team-popup.submit')) !!}"/>
				</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="popup fixed-popup popup-existing-dentist" id="popup-existing-dentist">
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
            {!! nl2br(trans('trp.popup.add-team-popup.existing-team-title')) !!}
        </h2>

        <div class="existing-dentists"></div>
	</div>
</div>