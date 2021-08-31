<div class="popup fixed-popup" id="popup-ask-dentist">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup">
				<img src="{{ url('img/close-icon.png') }}"/>
			</a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>

		<div class="alert alert-info">
			{!! nl2br(trans('trp.popup.popup-ask-dentist.hint', [ 'name' => $item->getNames() ])) !!}
			
			<br/>
			<br/>
			<a href="{{ $item->getLink().'ask' }}" original-href="{{ $item->getLink().'ask' }}" class="button ask-dentist">
				{!! nl2br(trans('trp.popup.popup-ask-dentist.send')) !!}
			</a>
		</div>
		<div class="alert alert-success ask-success" style="display: none;">
			{!! nl2br(trans('trp.popup.popup-ask-dentist.sent', [ 'name' => $item->getNames() ])) !!}
		</div>

	</div>
</div>