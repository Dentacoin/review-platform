@if($item->status == 'unconfirmed' || $item->status == 'failed' || $item->status == 'stopped')
	<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
		Bump
	</a>
@endif
@if($item->status != 'completed' && $item->status != 'stopped')
	<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}">
		Stop
	</a>
@endif
