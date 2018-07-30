<a href="{{ url('cms/users?search-ip-address='.$item->ip.'&search=Search') }}">{{ $item->ip }}</a>
@if( $item->getUsersCount() > 1 )
	<b>( {{ $item->getUsersCount() }} users from this IP )</b>
@endif