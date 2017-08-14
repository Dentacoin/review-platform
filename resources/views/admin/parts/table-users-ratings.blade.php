@if($item->ratings)
	<b>{{ $item->avg_rating }}</b> {{ trans('admin.page.'.$current_page.'.ratings-count', ['count' => $item->ratings]) }}
@else
	-
@endif