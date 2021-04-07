@if($item->is_paid_by_the_user)
	<div class="normal-mode">
		Approval: {!! $item->allowance_hash ? '<a href="https://etherscan.io/tx/'.$item->allowance_hash.'" target="_blank">'.$item->allowance_hash.'</a>' : '-' !!} <br/>
		Funds sent: {!! $item->tx_hash ? '<a href="https://etherscan.io/tx/'.$item->tx_hash.'" target="_blank">'.$item->tx_hash.'</a>' : '-' !!}
	</div>
@else
	@if($item->tx_hash)
		<div class="normal-mode">
			<a href="https://etherscan.io/tx/{{ $item->tx_hash }}" target="_blank">
				{{ $item->tx_hash }}
			</a>
		</div>
	@else
		<div class="normal-mode">
			-
		</div>
	@endif
@endif