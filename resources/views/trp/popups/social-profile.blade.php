<div class="popup fixed-popup popup-with-background social-profile-popup close-on-shield" id="social-profile-popup">
	<div class="popup-inner inner-white">
		
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-cur-popup"><i class="fas fa-times"></i></a>
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
					<label for="add-avatar-patient" class="image-label">
						<div class="centered-hack">
			    			<img src="{{ url('img/camera.svg') }}">
							<p>
		    					{!! nl2br(trans('trp.popup.social-profile.add-photo')) !!}
				    		</p>
						</div>
			    		<div class="loader">
			    			<i class="fas fa-circle-notch fa-spin"></i>
			    		</div>
						<input type="file" name="image" id="add-avatar-patient" upload-url="{{ getLangUrl('register/upload') }}" accept="image/png,image/jpeg,image/jpg">
					</label>
					<input type="hidden" class="photo-name" name="photo" >
					<input type="hidden" class="photo-thumb" name="photo-thumb" >

					<div class="tac max-size">
						<span>{!! nl2br(trans('trp.popup.social-profile.max-photo-size')) !!}</span>
					</div>
				@endif

				<div class="modern-field alert-after">
					<input type="text" name="link" id="link" class="modern-input link" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
					<label for="link">
						<span>{!! nl2br(trans('trp.popup.social-profile.social-link')) !!}</span>
					</label>
				</div>

				<div class="flex flex-center flex-text-center break-mobile">
					<a href="javascript:;" class="closer-pop cancel button button-white">{!! nl2br(trans('trp.popup.social-profile.cancel')) !!}</a>
					<button type="submit" class="button">{!! nl2br(trans('trp.popup.social-profile.update-profile')) !!}</button>
				</div>
				<div class="alert alert-warning without-image mobile" style="display: none; margin-top: 20px;">{!! nl2br(trans('trp.popup.social-profile.error-missing-image')) !!}</div>
			</form>
		</div>
	</div>
</div>