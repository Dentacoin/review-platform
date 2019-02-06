@if(!empty($item->user))
	{{ $item->prepareContent()[1] }}
@else
	{{ $item->template->title }}
@endif

