<div class="popup fixed-popup" id="popup-share">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< back</a>
		</div>
		<h2>Share</h2>

		<h4 class="popup-title">You can share this with others.</h4>

		<p class="popup-desc">
			Even if they don’t have Trusted Reviews account, they will still be able to see it.<br/>
			You can directly copy the link below and send it or you can choose from the other options.
		</p>

		<div class="flex copy-wrapper">
			<div class="col share-buttons flex flex-mobile" data-href="">
				<div class="col fb tac">
					<a class="share" network="fb" href="javascript:;">
						<i class="fab fa-facebook-f"></i>
					</a>
				</div>
				<div class="col twt tac">
					<a class="share" network="twt" href="javascript:;">
						<i class="fab fa-twitter"></i>
					</a>
				</div>
			</div>
			<div class="col flex flex-mobile share-link">
				<input type="text" id="share-url" class="input select-me" name="link" value="">
				<a class="copy-link button" href="javascript:;">
					<i class="far fa-copy"></i>
				</a>
			</div>
		</div>

		@if(!empty($user))
			<form method="post" class="copy-wrapper" action="{{ getLangUrl('share') }}" id="share-link-form">
				{!! csrf_field() !!}
				<p>Or send it to a friends email.</p>
				<div class="flex">
					<div class="flex-9">
						<input type="email" class="input" name="email" placeholder="Email address...">
					</div>
					<div class="flex-3">
						<input type="submit" class="button" value="Send by email">
					</div>
				</div>
				<p class="small-info">They won’t receive any other emails from us besides this one. </p>
				<input type="hidden" name="address" id="share-address">
				<div class="alert alert-success" style="display: none;">
					Link shared. If you want to share it with someone else - just enter their email address above
				</div>
				<div class="alert alert-warning" style="display: none;">
					Please enter a valid email address
				</div>
			</form>
		@endif

	</div>
</div>