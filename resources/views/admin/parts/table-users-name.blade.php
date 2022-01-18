@if($item->id == 113928)
	Email sender for unregistered users	
@else
	<a href="{{ url('/cms/users/users/edit/'.$item->id) }}">
		{{ !empty($item) ? $item->getNames() : 'Deleted user' }}
	</a>
@endif