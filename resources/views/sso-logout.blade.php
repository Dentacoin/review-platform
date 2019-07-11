@php($token = (new \App\Http\Controllers\SSOController())->encrypt($markLogout))
@foreach( config('platforms') as $k => $platform )
	@if( $platform['url'] && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  )
		<img src="//{{ $platform['url'] }}/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>
	@endif
@endforeach
<img src="//{{ vox.dentacoin.com }}/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>