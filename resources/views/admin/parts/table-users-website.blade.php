@if($item->user->website)
	@if(filter_var($item->user->website, FILTER_VALIDATE_URL) === FALSE)
	    {{ $item->user->website }}
	@else
	    <a style="word-break: break-word;" href="{{ $item->user->website }}" target="_blank">{{ $item->user->website }}</a>
	@endif
@else
	-
@endif