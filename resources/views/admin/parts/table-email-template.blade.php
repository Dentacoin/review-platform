@if(!empty($item->user) && !empty($item->platform))
	{{ $item->prepareContent()[1] }}
@elseif(!empty($item->template))
	{{ $item->template->title }}
@endif

