@if(!empty($item->phone))
	{{ $item->country ? $item->country->phone_code.' / ' : '' }} {{ $item->phone }}
@else
	-
@endif