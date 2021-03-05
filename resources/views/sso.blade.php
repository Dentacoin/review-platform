@if($user)
	@php($slug = App\Models\User::encrypt($user->id))
	@php($type = App\Models\User::encrypt($user->is_dentist ? 'dentist' : 'patient'))
	@php($token = (new \App\Http\Controllers\SSOController())->getLoginToken())
	<div class="sso" style="display: none;">
		@foreach( config('platforms') as $k => $platform )
			@if( !empty($platform['url']) && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  )
		 		<img src="//{{ $platform['url'] }}/custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }}" class="hide"/>
		 	@endif
		@endforeach
		<img src="//vox.dentacoin.com/custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }}" class="hide"/>
		<!-- <img src="//urgent.dentavox.dentacoin.com/custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }}" class="hide"/>
		<img src="//urgent.reviews.dentacoin.com/custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }}" class="hide"/> -->
	</div>
@endif