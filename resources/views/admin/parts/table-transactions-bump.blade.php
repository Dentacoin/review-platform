@if($item->status == 'first')
	<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
		Approve
	</a>
	<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}">
		Reject
	</a>
@else
	@if($item->status == 'failed' || $item->status == 'stopped' || $item->status == 'dont_retry' || $item->status == 'pending' || $item->status == 'first' )
		<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
			Bump
		</a>
	@endif
	@if($item->status != 'completed' && $item->status != 'unconfirmed' && $item->status != 'stopped' && $item->status != 'dont_retry')
		<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}">
			Stop
		</a>
	@endif
	@if($item->status == 'unconfirmed')
		<a class="btn btn-warning" href="{{ url('cms/transactions/pending/'.$item->id) }}">
			Pending
		</a>	
	@endif
@endif