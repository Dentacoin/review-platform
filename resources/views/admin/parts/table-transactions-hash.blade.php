@if($item->tx_hash)
<a href="https://etherscan.io/tx/{{ $item->tx_hash }}" target="_blank">
	{{ $item->tx_hash }}
</a>
@else
-
@endif