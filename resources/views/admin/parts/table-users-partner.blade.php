@if($item->is_dentist)
	{!! !empty($item->is_partner) && $item->is_partner == 1 ? '<span class="label label-success">'.trans('admin.common.yes').'</span>' : '<span class="label label-warning">'.trans('admin.common.no').'</span>' !!}
@else
-
@endif