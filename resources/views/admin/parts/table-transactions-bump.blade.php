@if($item->status == 'first')
	<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}" style="margin-block: 2px;">
		Approve
	</a>
	<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}" style="margin-block: 2px;">
		Reject
	</a>
	@if($item->user && !$item->user->is_dentist && $item->user->patient_status != 'suspicious_admin' && $item->user->patient_status != 'suspicious_badip')
		<a class="btn btn-warning make-user-suspicious" href="javascript:;" data-toggle="modal" data-target="#suspiciousUserModal" user-id="{{ $item->user_id }}" style="margin-block: 2px;">
			Suspicious user
		</a>
	@endif
@else
	@if($admin->role!='support')
		@if($item->status == 'stopped' || $item->status == 'dont_retry' || $item->status == 'pending' )
			<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}" style="margin-block: 2px;">
				Bump
			</a>
		@endif
		@if($item->status != 'completed' && $item->status != 'unconfirmed' && $item->status != 'stopped' && $item->status != 'dont_retry' && $item->status != 'failed')
			<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}" style="margin-block: 2px;">
				Stop
			</a>
		@endif
		@if($item->status == 'unconfirmed')
			<a class="btn btn-warning" href="{{ url('cms/transactions/pending/'.$item->id) }}" style="margin-block: 2px;">
				Pending
			</a>	
		@endif
	@endif
@endif
@if($admin->role='super_admin')
	@if($item->status != 'completed' && $item->status != 'unconfirmed' && $item->status != 'pending' && $item->status != 'failed')
		<a class="btn btn-info" onclick="return confirm('Are you sure you want to DELETE this?');" href="{{ url('cms/transactions/delete/'.$item->id) }}" style="background: black;border-color: black;margin-block: 2px;">
			Delete
		</a>
	@endif
	<a class="btn btn-info" href="{{ url('cms/transactions/edit/'.$item->id) }}" style="margin-block: 2px;">
		Edit
	</a>
@endif