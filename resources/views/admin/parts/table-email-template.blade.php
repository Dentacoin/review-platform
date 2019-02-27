@if(!empty($item->user) && !empty($item->platform) && !empty($item->clinic))
	{{ $item->prepareContent()[1] }}
@elseif(!empty($item->template))
	{{ $item->template->title }}
@endif

