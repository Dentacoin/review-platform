<div class="popup fixed-popup active removable" id="popup-share" scss-load="trp-popup-share">
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
			{!! nl2br(trans('trp.popup.popup-share.title')) !!}
		</h2>

		<h4 class="popup-title">
			{!! nl2br(trans('trp.popup.popup-share.subtitle')) !!}
		</h4>

		<p class="popup-desc">
			{!! nl2br(trans('trp.popup.popup-share.hint')) !!}
		</p>

		<div class="flex copy-wrapper">
			<div class="col share-buttons flex flex-mobile" data-href="" data-title="{!! $seo_title !!}">
				<div class="col fb tac">
					<a class="share" network="fb" href="javascript:;">
						<img src="{{ url('img/social-network/facebook.svg') }}" width="27"/>
					</a>
				</div>
				<div class="col twt tac">
					<a class="share" network="twt" href="javascript:;">
						<img src="{{ url('img/social-network/twitter.svg') }}" width="27"/>
					</a>
				</div>
			</div>
			<div class="col flex flex-mobile share-link">
				<input type="text" id="share-url" class="input select-me" name="link" value="">
				<a class="copy-link button" href="javascript:;">
					<img src="{{ url('img/copy-files.svg') }}" width="20"/>
				</a>
			</div>
		</div>

		@if(!empty($user))
			<form method="post" class="copy-wrapper" action="{{ getLangUrl('share') }}" id="share-link-form">
				{!! csrf_field() !!}
				<p>
					{!! nl2br(trans('trp.popup.popup-share.email-title')) !!}
				</p>
				<div class="flex">
					<div class="flex-9">
						<input type="email" class="input" name="email" placeholder="{!! nl2br(trans('trp.popup.popup-share.email')) !!}">
					</div>
					<div class="flex-3">
						<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-share.send')) !!}">
					</div>
				</div>
				<p class="small-info">
					{!! nl2br(trans('trp.popup.popup-share.no-spam')) !!}
				</p>
				<input type="hidden" name="address" id="share-address">
				<div class="alert alert-success" style="display: none;">
					{!! nl2br(trans('trp.popup.popup-share.email-success')) !!}
				</div>
				<div class="alert alert-warning" style="display: none;">
					{!! nl2br(trans('trp.popup.popup-share.email-failure')) !!}
				</div>
			</form>
		@endif

	</div>
</div>