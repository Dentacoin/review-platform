@if (!empty($item->translation_langs))
	@foreach($item->translation_langs as $lang)
        {{ strtoupper($lang) }}{{ !$loop->last ? ', ' : '' }}
    @endforeach

    @if($item->processingForTranslations->isNotEmpty())
        <br/><br/>
    @endif
@endif

@if($item->processingForTranslations->isNotEmpty())
    <b>Translating to:</b>
    @foreach($item->processingForTranslations as $transl)
        {{ strtoupper($transl->lang_code) }}{{ !$loop->last ? ', ' : '' }}
    @endforeach
@endif