<a href="{{ url('cms/vox/edit/'.$item->vox_id) }}" target="_blank">
	{{ $item->vox ? $item->vox->title : 'Deleted' }}
</a>