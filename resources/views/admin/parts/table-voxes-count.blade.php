@if(session('vox-show-all-results'))
	{{ $item->questions->count() }}
@else
	<div>
		<a href="javascript:;" class="show-questions" vox-id="{{ $item->id }}">show</a>
	</div>
@endif