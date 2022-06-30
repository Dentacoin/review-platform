<div class="popup add-wallet-address-popup no-image" id="add-wallet-address" scss-load="trp-popup-add-wallet-address" js-load="wallet-address">
	<div class="popup-inner">
		<h2 class="mont">
			{!! nl2br(trans('trp.popup.add-wallet-address.title')) !!}
		</h2>

		<p class="popup-desc">{!! nl2br(trans('trp.popup.add-wallet-address.description')) !!}</p>

		<form class="wallet-address-form" id="wallet-address-form" method="post" action="{{ getLangUrl('add-wallet-address') }}">
			{!! csrf_field() !!}

			<div class="modern-field alert-after">
				<input type="text" name="wallet-address" id="wallet-address" class="modern-input" autocomplete="off">
				<label for="wallet-address">
					<span>{!! nl2br(trans('trp.popup.add-wallet-address.address')) !!}:</span>
				</label>
			</div>

			<label class="checkbox-label" for="recieve-address">
				<input type="checkbox" class="special-checkbox" id="recieve-address" name="recieve-address" value="1">
				<div class="checkbox-square">✓</div>
				{!! nl2br(trans('trp.popup.add-wallet-address.receive-rewards')) !!}
			</label>

			<div class="modern-field alert-after receive-wallet-address-wrapper">
				<input type="text" name="receive-wallet-address" id="receive-wallet-address" class="modern-input" autocomplete="off">
				<label for="receive-wallet-address">
					<span>{!! nl2br(trans('trp.popup.add-wallet-address.address-rewards')) !!}:</span>
				</label>
			</div>

			<div class="tac">
				<button type="submit" class="blue-button submit-wallet-address" single-address="{!! nl2br(trans('trp.popup.add-wallet-address.save-address')) !!}" multiple-address="{!! nl2br(trans('trp.popup.add-wallet-address.save-addresses')) !!}">
					<div class="loader"><i></i></div>
					<span>{!! nl2br(trans('trp.popup.add-wallet-address.save-address')) !!}</span>
				</button>

				<p class="without-wallet">
					{!! nl2br(trans('trp.popup.add-wallet-address.no-wallet')) !!}
				</p>
				
				<a href="javascript:;" class="white-button close-popup remind-later">{!! nl2br(trans('trp.popup.add-wallet-address.remind-later')) !!}</a>
			</div>

			<div class="alert alert-warning" style="display: none;"></div>
		</form>
	</div>
</div>