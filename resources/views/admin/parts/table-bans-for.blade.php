@if( $item->ban_for_id )
	{{ App\Models\Vox::find($item->ban_for_id) ? App\Models\Vox::find($item->ban_for_id)->title : '' }}
@endif