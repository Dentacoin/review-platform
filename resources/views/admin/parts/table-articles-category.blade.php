@foreach($item->categories as $cat)
	{{ $cat->category->name }}<br/>
@endforeach