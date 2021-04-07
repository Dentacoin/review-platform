@if($item->status == 'first')
	<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
		Approve
	</a>
	<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}">
		Reject
	</a>
@else
	@if($item->status == 'stopped' || $item->status == 'dont_retry' || $item->status == 'pending' )
		<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}">
			Bump
		</a>
	@endif
	@if($item->status != 'completed' && $item->status != 'unconfirmed' && $item->status != 'stopped' && $item->status != 'dont_retry' && $item->status != 'failed')
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
@if(($admin->id == 14 || $admin->id == 15 || $admin->id == 1) && ($item->status != 'completed' && $item->status != 'unconfirmed' && $item->status != 'pending' && $item->status != 'failed'))
	<a class="btn btn-info" onclick="return confirm('Are you sure you want to DELETE this?');" href="{{ url('cms/transactions/delete/'.$item->id) }}" style="background: black;border-color: black;">
		Delete
	</a>
@endif
@if($admin->id == 14 || $admin->id == 15 || $admin->id == 1)
	<a class="btn btn-info" href="{{ url('cms/transactions/edit/'.$item->id) }}">
		Edit
	</a>
@endif