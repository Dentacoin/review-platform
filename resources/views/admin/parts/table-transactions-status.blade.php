{{ config('transaction-statuses')[$item->status] }}

@if($item->is_paid_by_the_user)
	<br/>
	<b>Paid by the user</b>
@endif