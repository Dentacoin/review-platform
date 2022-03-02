@if(session('vox-show-all-results'))
	{{ $item->questionsCount() }}
@else
	<div>
		<a href="javascript:;" class="show-questions" vox-id="{{ $item->id }}">show</a>
	</div>
@endif