@if(!empty($item->user) && !empty($item->platform))
	{{ $item->prepareContent()[1] }}
@else
	{{ $item->template->title }}
@endif

