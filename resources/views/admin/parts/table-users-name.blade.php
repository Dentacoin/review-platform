@if($item->id == 113928)
	Email sender for unregistered users	
@else
	<a href="{{ url('cms/'.$current_page.( !empty($table_subpage) ? '/'.$table_subpage : '' ).'/edit/'.$item->id) }}">
		{{ $item->name }}
	</a>
@endif