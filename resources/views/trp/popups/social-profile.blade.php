<div class="popup fixed-popup popup-with-background social-profile-popup close-on-shield" id="social-profile-popup" scss-load="trp-popup-social-profile">
	<div class="popup-inner inner-white">
		
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-cur-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-cur-popup">< {{ trans('trp.common.back') }}</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.social-profile.title')) !!}
		</h2>

		<div class="content">
			<p>{!! nl2br(trans('trp.popup.social-profile.description')) !!}</p>

			<form class="form" action="{{ getLangUrl('social-profile') }}" method="post" id="social-profile-form">
				{!! csrf_field() !!}

				@if(empty($user->hasimage))
					<div class="upload-image-wrapper">
						<label for="add-avatar-patient" class="image-label">
							<div class="centered-hack">
								<img src="{{ url('img/camera.svg') }}"/>
								<p>
									{!! nl2br(trans('trp.popup.social-profile.add-photo')) !!}
								</p>
							</div>
							<div class="loader">
								<i></i>
							</div>
							<input type="file" name="image" id="add-avatar-patient" upload-url="{{ getLangUrl('register/upload') }}" accept="image/png,image/jpeg,image/jpg"/>
							<input type="hidden" name="avatar" class="avatar">
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
				@endif
				
				<div class="modern-field alert-after">
					<input type="text" name="link" id="link" class="modern-input link" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/>
					<label for="link">
						<span>{!! nl2br(trans('trp.popup.social-profile.social-link')) !!}</span>
					</label>
				</div>

				<div class="flex flex-center flex-text-center break-mobile">
					<a href="javascript:;" class="closer-pop cancel button button-white">
						{!! nl2br(trans('trp.popup.social-profile.cancel')) !!}
					</a>
					<button type="submit" class="button">
						{!! nl2br(trans('trp.popup.social-profile.update-profile')) !!}
					</button>
				</div>
				<div class="alert alert-warning without-image mobile" style="display: none; margin-top: 20px;">
					{!! nl2br(trans('trp.popup.social-profile.error-missing-image')) !!}
				</div>
			</form>
		</div>
	</div>
</div>