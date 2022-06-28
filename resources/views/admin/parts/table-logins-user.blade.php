<a href="{{ url('cms/users?search-ip-address='.$item->ip.'&search=Search') }}">{{ $item->ip }}</a>
@php($usersCount = $item->getUsersCount())
@if( $usersCount > 1 )
	<b>( {{ $usersCount }} users from this IP )</b>
@endif