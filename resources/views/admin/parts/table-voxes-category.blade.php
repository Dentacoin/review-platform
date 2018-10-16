@if($item->categories)
	@foreach($item->categories as $cat)
		{{ !empty($cat->category) ? $cat->category->name : 'Deleted' }} 
	@endforeach
@endif