@if( $item->ban_for_id )
	<a href="{{ url('cms/vox/edit/'.$item->ban_for_id.'/') }}">{{ $item->vox ? $item->vox->title : '' }}</a>
@endif