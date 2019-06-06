@foreach( config('platforms') as $k => $platform )
	<img src="//{{ $platform['url'] }}/custom-cookie?logout-token=token={{ urlencode($markLogout) }}" class="hide"/>
@endforeach