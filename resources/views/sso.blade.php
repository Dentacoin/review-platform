@php($slug = (new \App\Http\Controllers\SSOController())->encrypt($user->id))
@php($type = (new \App\Http\Controllers\SSOController())->encrypt($user->is_dentist ? 'dentist' : 'patient'))
@php($token = (new \App\Http\Controllers\SSOController())->getLoginToken())
@foreach( config('platforms') as $k => $platform )
	@if( $platform['url'] && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  )
 		<img src="//{{ $platform['url'] }}/custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }}" class="hide"/>
 	@endif
@endforeach