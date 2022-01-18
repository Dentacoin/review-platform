<a href="{{ url('/cms/users/users/edit/'.$item->id) }}">
	{{ !empty($item) ? $item->getNames() : 'Deleted user' }}
</a>