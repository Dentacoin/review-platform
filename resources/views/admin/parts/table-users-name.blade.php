<a href="{{ url('cms/'.$current_page.( !empty($table_subpage) ? '/'.$table_subpage : '' ).'/edit/'.$item->id) }}">
	{{ $item->name }}
</a>