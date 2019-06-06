@php($token = (new \App\Http\Controllers\SSOController())->encrypt($markLogout))
@foreach( config('platforms') as $k => $platform )
	<img src="//{{ $platform['url'] }}/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>
@endforeach