@if(!empty($item->user))
	<a target="_blank" href="{{ url('cms/users/users/edit/'.$item->user->id) }}">
		{{ $item->user->name }}
	</a>
@else
	unregistered
@endif