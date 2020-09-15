<a href="{{ url('/cms/users/edit/'.$item->id) }}">
	{{ !empty($item) ? $item->getNames() : 'Deleted user' }}
</a>