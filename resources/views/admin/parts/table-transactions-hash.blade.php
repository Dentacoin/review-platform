@if($item->is_paid_by_the_user)
	Approval: {{ $item->allowance_hash ? '<a href="https://etherscan.io/tx/'.$item->allowance_hash.'" target="_blank">'.$item->allowance_hash.'</a>' : '' }} <br/>
	Funds sent: {{ $item->tx_hash ? '<a href="https://etherscan.io/tx/'.$item->tx_hash.'" target="_blank">'.$item->tx_hash.'</a>' : '' }}
@else
	@if($item->tx_hash)
	<a href="https://etherscan.io/tx/{{ $item->tx_hash }}" target="_blank">
		{{ $item->tx_hash }}
	</a>
	@else
	-
	@endif
@endif