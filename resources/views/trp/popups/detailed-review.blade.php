<div class="popup fixed-popup" id="view-review-popup">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
			<a href="javascript:;" class="share-popup" data-popup="popup-share"><img src="{{ url('img-trp/share-big.png') }}"/></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
			<a href="javascript:;" class="share-popup" data-popup="popup-share">
				<img src="{{ url('img/share-blue.svg') }}" style="display: inline-block; width: 14px; vertical-align: middle;"/> {!! nl2br(trans('trp.common.share')) !!}
			</a>
		</div>
		<h2>
			{!! nl2br(trans('trp.popup.view-review-popup.title', [ 'name' => $item->getNames() ])) !!}
		</h2>

		<div id="the-detailed-review">
			
		</div>
	</div>
</div>