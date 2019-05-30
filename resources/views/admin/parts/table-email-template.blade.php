@if(!empty($item->user) && !empty($item->platform) && !empty($item->who_joined_name))
	{{ $item->prepareContent()[1] }}
@elseif(!empty($item->template))
	{{ $item->template->title }}
@endif

