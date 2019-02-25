@if(!empty($item->user) && !empty($item->platform) && !empty($item->clinic))
	{{ $item->prepareContent()[1] }}
@else
	{{ $item->template->title }}
@endif

