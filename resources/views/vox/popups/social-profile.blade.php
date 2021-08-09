<div class="popup fixed-popup popup-with-background social-profile-popup close-on-shield active" id="social-profile-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
		</a>
		<div class="flex flex-mobile flex-center break-tablet">
			<div class="content">
				<p class="h1">
					{!! nl2br(trans('vox.popup.social-profile.title')) !!}
				</p>

				<p>{!! nl2br(trans('vox.popup.social-profile.description')) !!}</p>

				<form class="form" action="{{ getLangUrl('social-profile') }}" method="post" id="social-profile-form">
					{!! csrf_field() !!}

					@if(empty($user->hasimage))
						<label for="add-avatar-patient" class="image-label">
							<div class="centered-hack">
				    			<img src="{{ url('img/camera.svg') }}">
								<p>
									{!! nl2br(trans('vox.popup.social-profile.add-photo')) !!}
					    		</p>
							</div>
				    		<div class="loader">
				    			<i></i>
				    		</div>
							<input type="file" name="image" id="add-avatar-patient" upload-url="{{ getLangUrl('profile/info/upload') }}" accept="image/png,image/jpeg,image/jpg">
						</label>
						<input type="hidden" class="photo-name" name="photo" >
						<input type="hidden" class="photo-thumb" name="photo-thumb" >

						<div class="tac max-size">
							<span>{!! nl2br(trans('vox.popup.social-profile.max-size')) !!}</span>
						</div>
					@endif

					<div class="modern-field alert-after">
						<input type="text" name="link" id="link" class="modern-input link" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
						<label for="link">
							<span>{!! nl2br(trans('vox.popup.social-profile.link')) !!}:</span>
						</label>
					</div>

					<div class="flex flex-center flex-text-center break-mobile">
						<a href="javascript:;" class="closer-pop cancel white-button">{!! nl2br(trans('vox.popup.social-profile.cancel')) !!}</a>
						<button type="submit" class="blue-button">{!! nl2br(trans('vox.popup.social-profile.update-profile')) !!}</button>
					</div>
					<div class="alert alert-warning without-image mobile" style="display: none; margin-top: 20px;">{!! nl2br(trans('vox.popup.social-profile.image-error')) !!}</div>
				</form>
			</div>
		</div>
	</div>
</div>