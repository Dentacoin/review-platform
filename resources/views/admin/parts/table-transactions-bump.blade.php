@if($admin->role!='support')
	@if(in_array($item->status, ['stopped','dont_retry','pending']) )
		<a class="btn btn-primary" href="{{ url('cms/transactions/bump/'.$item->id) }}" style="margin-top: 2px;">
			Bump
		</a>
	@endif
	@if(!in_array($item->status, ['completed','unconfirmed','stopped','dont_retry','failed','first']))
		<a class="btn btn-danger" href="{{ url('cms/transactions/stop/'.$item->id) }}" style="margin-top: 2px;">
			Stop
		</a>
	@endif
	@if($item->status == 'unconfirmed')
		<a class="btn btn-warning" href="{{ url('cms/transactions/pending/'.$item->id) }}" style="margin-top: 2px;">
			Pending
		</a>	
	@endif
@endif
@if($admin->role='super_admin')
	@if(!in_array($item->status, ['completed','unconfirmed','pending','failed']))
		<a class="btn btn-info" onclick="return confirm('Are you sure you want to DELETE this?');" href="{{ url('cms/transactions/delete/'.$item->id) }}" style="background: black;border-color: black;margin-top: 2px;">
			Delete
		</a>
	@endif
	<a class="btn btn-info" href="{{ url('cms/transactions/edit/'.$item->id) }}" style="margin-top: 2px;">
		Edit
	</a>
	@if($item->manual_check_admin)
		<a class="btn btn-success" href="{{ url('cms/transactions/checked-by-admin/'.$item->id) }}" style="margin-top: 2px;">
			Checked
		</a>
	@endif
@endif