<span style="font-size: 0px;">{{ intval($item->featured) }}</span>
@if($item->type=='normal' || $item->type=='hidden')
	<input type="checkbox" class="toggler " field="featured" id="{{ $item->id }}" {!! $item->featured ? 'checked="checked"' : '' !!} >
@else
	-
@endif