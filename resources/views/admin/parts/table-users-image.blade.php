@if($item->user->hasimage)
	<a href="{{ $item->user->getImageUrl() }}" data-lightbox="banappeal{{ $item->user->id }}">
		<img src="{{ $item->user->getImageUrl(true) }}" style="max-width: 30px;">
	</a>
@else
	-
@endif