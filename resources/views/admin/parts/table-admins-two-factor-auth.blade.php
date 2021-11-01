@if( !empty($item->two_factor_auth) )
	<a href="{{ url('cms/admins/reset-auth/'.$item->id) }}" class="btn btn-primary">Reset</a>
@endif