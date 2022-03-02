@if(session('vox-show-all-results'))
	~{{ ceil($item->questionsCount()/6) }}min
@else
	<div>
		<a href="javascript:;" class="show-duration" vox-id="{{ $item->id }}">show</a>
	</div>
@endif