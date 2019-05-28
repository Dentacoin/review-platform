@if($item->login)
	{{ $item->login->getDeviceName() }}
@endif