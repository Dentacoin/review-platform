@if($item->deleted_at)
	<a class="btn btn-primary" href="{{ url('cms/users/edit/'.$item->user_id.'/restoreban/'.$item->id) }}">
		Restore
	</a>
@else
	<a class="btn btn-primary" href="{{ url('cms/users/edit/'.$item->user_id.'/deleteban/'.$item->id) }}">
		<i class="fa fa-remove"></i>
	</a>
@endif