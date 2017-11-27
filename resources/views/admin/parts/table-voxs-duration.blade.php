@if($item->duration>60)
	{{ ceil($item/60) }}h
@endif

{{ $item%60 }}min