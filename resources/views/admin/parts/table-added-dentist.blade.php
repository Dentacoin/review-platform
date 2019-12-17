<a href="{{ url('/cms/users/edit/'.$item->id) }}">
	{{ !empty($item) ? $item->getName() : 'Deleted user' }}
</a>