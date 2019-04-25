@if($item->ratings)
	<b>{{ $item->avg_rating }}</b>
@else
	-
@endif