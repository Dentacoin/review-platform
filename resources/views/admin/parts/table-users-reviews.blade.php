@if($item->ratings)
	<!-- {{ trans('admin.page.'.$current_page.'.ratings-count', ['count' => $item->ratings]) }} -->
	{{ $item->ratings }}
@else
	-
@endif