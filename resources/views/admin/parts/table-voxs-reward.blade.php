@if(session('vox-show-all-results'))
	{{ $item->getRewardTotal() }}
@else
	<div>
		<a href="javascript:;" class="show-reward" vox-id="{{ $item->id }}">show</a>
	</div>
@endif