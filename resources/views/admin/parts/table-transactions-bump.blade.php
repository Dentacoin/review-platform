@if($item->status == 'first')
	<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
		Approve
	</a>
	<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}">
		Reject
	</a>
@else
	@if($item->status == 'unconfirmed' || $item->status == 'failed' || $item->status == 'stopped' || $item->status == 'dont_retry' )
		<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
			Bump
		</a>
	@endif
	@if($item->status != 'completed' && $item->status != 'stopped' && $item->status != 'dont_retry')
		<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}">
			Stop
		</a>
	@endif
@endif