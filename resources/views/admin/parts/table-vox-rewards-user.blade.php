@if($item->type == 'survey')
	<a href="{{ url('cms/vox/edit/'.$item->reference_id) }}" target="_blank">
		{{ $item->vox ? $item->vox->title : 'Deleted' }}
	</a>
@else
	Daily Poll
@endif