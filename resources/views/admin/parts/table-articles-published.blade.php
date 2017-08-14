<h4 style="margin: 0px;">
@if( $item->published )
	<span class="label label-success">
@elseif( $item->publish_on->lt( Carbon\Carbon::now() ) )
	<span class="label label-warning">
@else
	<span class="label label-danger">
@endif

	@if(!empty($item->publish_on))
		{{ $item->publish_on->toFormattedDateString() }}
		{{ $item->publish_on->toTimeString() }}
	@else
		{{ trans('admin.common.no') }}
	@endif

</span>
</h4>