@php($token = App\Helpers\GeneralHelper::encrypt($markLogout))
<div style="display: none;">
	@foreach( config('platforms') as $k => $platform )
		@if( !empty($platform['url']) && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  )
			<img src="//{{ $platform['url'] }}/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>
		@endif
	@endforeach
	<img src="//vox.dentacoin.com/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>
	<img src="//hub.dentacoin.com/custom-cookie?logout-token={{ urlencode($token) }}" class="hide"/>
</div>