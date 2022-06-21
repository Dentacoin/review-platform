<div class="popup add-wallet-address-popup no-image" id="add-wallet-address" scss-load="trp-popup-add-wallet-address" js-load="wallet-address">
	<div class="popup-inner">
		<h2 class="mont">
			Add your wallet address
		</h2>

		<p class="popup-desc">In order to receive DCN payments from your patients, you need to enter your wallet address below.</p>			

		<form class="wallet-address-form" id="wallet-address-form" method="post" action="{{ getLangUrl('add-wallet-address') }}">
			{!! csrf_field() !!}

			<div class="modern-field alert-after">
				<input type="text" name="wallet-address" id="wallet-address" class="modern-input" autocomplete="off">
				<label for="wallet-address">
					{{-- <span>{!! nl2br(trans('trp.popup.popup-claim-profile.name')) !!}</span> --}}
					<span>Enter your wallet address:</span>
				</label>
			</div>

			<label class="checkbox-label" for="recieve-address">
				<input type="checkbox" class="special-checkbox" id="recieve-address" name="recieve-address" value="1">
				<div class="checkbox-square">✓</div>
				I want to receive DCN rewards on this wallet address.
			</label>

			<div class="modern-field alert-after receive-wallet-address-wrapper">
				<input type="text" name="receive-wallet-address" id="receive-wallet-address" class="modern-input" autocomplete="off">
				<label for="receive-wallet-address">
					{{-- <span>{!! nl2br(trans('trp.popup.popup-claim-profile.name')) !!}</span> --}}
					<span>Enter withdraw address for DCN rewards:</span>
				</label>
			</div>

			<div class="tac">
				<button type="submit" class="blue-button submit-wallet-address" single-address="Save wallet address" multiple-address="Save wallet addresses">
					<div class="loader"><i></i></div>
					<span>Save wallet address</span>
				</button>

				<p class="without-wallet">
					Don’t have a Dentacoin Wallet yet? <a href="http://wallet.dentacoin.com" target="_blank">Create one now!</a>
				</p>
				
				<a href="javascript:;" class="white-button close-popup remind-later">REMIND ME LATER</a>
			</div>

			<div class="alert alert-warning" style="display: none;"></div>
		</form>
	</div>
</div>