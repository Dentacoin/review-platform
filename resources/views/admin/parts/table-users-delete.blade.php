@if($item->id == 113928)
@else
	@if(!empty($item->deleted_at))
	    <a class="btn btn-sm btn-deafult" href="{{ url('cms/users/users/restore/'.$item->id) }}">{{ trans('admin.table.restore') }}</a>
	@else
	    <a class="btn btn-sm btn-deafult deletion-button" user-id="{{ $item->id }}" href="javascript:;" data-toggle="modal" data-target="#deleteModal">Delete</a>
	@endif
@endif