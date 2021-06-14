@if(session('vox-show-all-results'))
	~{{ ceil($item->questions()->count()/6) }}min
@else
	<div>
		<a href="javascript:;" class="show-duration" vox-id="{{ $item->id }}">show</a>
	</div>
@endif