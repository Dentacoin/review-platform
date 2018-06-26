@if($item->categories)
	@foreach($item->categories as $cat)
		{{ $cat->category->name }} 
	@endforeach
@endif