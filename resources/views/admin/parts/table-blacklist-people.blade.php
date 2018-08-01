@if(!empty($item->blacklistBlock))
	@foreach($item->blacklistBlock as $list)
		@if($loop->iteration == 3)
			<a href="javascript:;" onclick="$(this).next().show();$(this).remove();">Show all ({{ $item->blacklistBlock->count() }})</a>
			<div style="display: none;">
		@endif
		<p class="blacklist">
			@if(!empty($list->name))
				<i class="fa fa-user fa-fw"></i> {{ $list->name }}
			@endif
			@if(!empty($list->email))
				<span class="pull-right">
					<i class="fa fa-envelope fa-fw"></i> {{ $list->email }}
				</span>
			@endif
		</p>
	@endforeach
	@if($item->blacklistBlock->count() >= 3)
		</div>
	@endif
@endif