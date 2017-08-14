<i class="fa fa-user"></i> {{ $item->author->username }}
@if( !empty($item->deleted_at) )
	<br/>
	<i class="fa fa-trash"></i> {{ $item->deletor->username }} / 
	{{ $item->deleted_at->toFormattedDateString() }}
	{{ $item->deleted_at->toTimeString() }}
@endif