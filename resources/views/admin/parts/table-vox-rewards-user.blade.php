<a href="{{ url('cms/vox/edit/'.$item->reference_id) }}" target="_blank">
	{{ $item->vox ? $item->vox->title : 'Deleted' }}
</a>