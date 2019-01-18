<span style="font-size: 0px;">{{ intval($item->has_stats) }}</span>
<input type="checkbox" class="toggler" field="has_stats" id="{{ $item->id }}" {!! $item->has_stats ? 'checked="checked"' : '' !!} />