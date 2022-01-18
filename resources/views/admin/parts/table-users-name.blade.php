@if($item->id == 113928)
	Email sender for unregistered users	
@else
	@if(!empty($item))
		@if(!empty($item->user))
			<a href="{{ url('/cms/users/users/edit/'.$item->user_id) }}">
				{{ !empty($item->user) ? $item->user->getNames() : 'Deleted user' }}
			</a>
		@else
			<a href="{{ url('/cms/users/users/edit/'.$item->id) }}">
				{{ !empty($item) ? $item->getNames() : 'Deleted user' }}
			</a>

		@endif
	@endif
	
@endif