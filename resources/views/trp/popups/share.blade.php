<div class="popup fixed-popup active no-image removable" id="popup-share" scss-load="trp-popup-share">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<h2 class="mont">
			{{-- {!! nl2br(trans('trp.popup.popup-share.title')) !!} --}}
			Share on social media
		</h2>

		<p class="popup-desc">
			{!! nl2br(trans('trp.popup.popup-share.hint')) !!}
		</p>

		<div class="copy-wrapper">
			<div class="share-buttons" data-href="" data-title="{!! $seo_title !!}">
				<div class="fb tac">
					<a class="share" network="fb" href="javascript:;">
						<img src="{{ url('img/social-network/facebook.svg') }}" width="27"/>
						Share on Facebook
					</a>
				</div>
				<div class="twt tac">
					<a class="share" network="twt" href="javascript:;">
						<img src="{{ url('img/social-network/twitter.svg') }}" width="27"/>
						Share on Twitter
					</a>
				</div>
			</div>
			<div class="col flex flex-mobile share-link">
				<input type="text" id="share-url" class="input select-me" name="link" value="">
				<a class="copy-link button" href="javascript:;">
					<img src="{{ url('img/copy-files.svg') }}" width="25"/>
				</a>
			</div>
		</div>

		<div class="tac">
			<a href="javascript:;" class="white-button close-popup">Close</a>
		</div>
	</div>
</div>