@if (!empty($item->translation_langs))
	@foreach($item->translation_langs as $lang)
        {{ strtoupper($lang) }}{{ !$loop->last ? ', ' : '' }}
    @endforeach
@endif