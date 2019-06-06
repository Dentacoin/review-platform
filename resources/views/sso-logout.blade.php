@php($token = (new \App\Http\Controllers\SSOController())->encrypt($markLogout))
@foreach( config('platforms') as $k => $platform )
	@if( $platform['url'] && $platform['url']!=request()->getHttpHost() )
		<img src="//{{ $platform['url'] }}/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>
	@endif
@endforeach