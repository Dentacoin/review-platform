@if($item->is_dentist)
	@if($item->status=='new')
		<span class="label label-info">New</span>
	@elseif($item->status=='pending')
		<span class="label label-warning">Suspicious</span>
	@elseif($item->status=='approved')
		<span class="label label-success">Approved</span>
	@else
		<span class="label label-danger">Rejected</span>
	@endif
@else
@endif