<div class="popup fixed-popup popup-with-background social-profile-popup close-on-shield active" id="social-profile-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="closer">
			<img src="{{ url('new-vox-img/close-popup.png') }}">
		</a>
		<div class="flex flex-mobile flex-center break-tablet">
			<div class="content">
				<p class="h1">
					GET VERIFIED FASTER
				</p>

				<p>Let's get to know each other better! If you complete your profile now, you will get verified much faster upon requesting your first rewards.</p>

				<form class="form" action="{{ getLangUrl('social-profile') }}" method="post" id="social-profile-form">
					{!! csrf_field() !!}

					@if(empty($user->hasimage))
						<label for="add-avatar-patient" class="image-label">
							<div class="centered-hack">
				    			<img src="{{ url('img/camera.svg') }}">
								<p>
			    					+ Add profile photo
					    		</p>
							</div>
				    		<div class="loader">
				    			<i class="fas fa-circle-notch fa-spin"></i>
				    		</div>
							<input type="file" name="image" id="add-avatar-patient" upload-url="{{ getLangUrl('profile/info/upload') }}" accept="image/png,image/jpeg,image/jpg">
						</label>
						<input type="hidden" class="photo-name" name="photo" >
						<input type="hidden" class="photo-thumb" name="photo-thumb" >

						<div class="tac max-size">
							<span>Max size: 2MB</span>
						</div>
					@endif

					<div class="modern-field alert-after">
						<input type="text" name="link" id="link" class="modern-input link" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
						<label for="link">
							<span>Link to your social media profile:</span>
						</label>
					</div>

					<div class="flex flex-center flex-text-center break-mobile">
						<a href="javascript:;" class="closer-pop cancel white-button">Cancel</a>
						<button type="submit" class="blue-button">Update profile</button>
					</div>
					<div class="alert alert-warning without-image mobile" style="display: none; margin-top: 20px;">The image is required.</div>
				</form>
			</div>
		</div>
	</div>
</div>