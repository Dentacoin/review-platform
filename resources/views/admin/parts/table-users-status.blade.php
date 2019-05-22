@if($item->is_dentist)
	<span class="label label-{{ config('user-statuses-classes')[$item->status] }}">{{ config('user-statuses')[$item->status] }}</span>
@else
@endif