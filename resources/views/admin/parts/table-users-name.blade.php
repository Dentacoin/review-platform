@if($item->id == 113928)
	Email sender for unregistered users	
@else
	@if(property_exists($item, 'user_id'))
		<a href="{{ url('/cms/users/users/edit/'.$item->user_id) }}">
			{{ !empty($item->user) ? $item->user->name : 'Deleted user' }}
		</a>
	@else
		<a href="{{ url('/cms/users/users/edit/'.$item->id) }}">
			{{ !empty($item) ? $item->name : 'Deleted user' }}
		</a>

	@endif
	
@endif