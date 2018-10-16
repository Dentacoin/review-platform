@if($item->type=='normal' || $item->type=='hidden')
	<input type="checkbox" class="toggler" field="type" id="{{ $item->id }}" {!! $item->type=='normal' ? 'checked="checked"' : '' !!} />
@else
	-
@endif