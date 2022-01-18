@if($item->user)
	<a href="{{ url('/cms/users/users/edit/'.$item->user_id) }}">
		{{ $item->user->name }}
	</a>
@endif
