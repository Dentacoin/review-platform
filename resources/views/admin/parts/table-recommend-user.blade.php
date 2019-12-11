<a href="{{ url('/cms/users/edit/'.$item->user_id) }}">
	{{ !empty($item->user) ? $item->user->name : 'Deleted user' }}
</a>