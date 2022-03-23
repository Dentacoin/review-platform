@extends('vox')

@section('content')

    <div class="main-title tac">
        <h1>{{ trans('vox.page.paid-reports.title') }}</h1>
        <p class="subtitle">{{ trans('vox.page.paid-reports.subtitle') }}</p>
        
		<img class="blue-circle" src="{{ url('new-vox-img/blue-circle-corner.png') }}"/>
    </div>

    @if(!empty($item))
        <div class="main-section flex">
            <div class="main-image-wrapper col">
                <p class="blue-background">{{ trans('vox.page.paid-reports.latest-report') }}</p>
                <img class="main-image" src="{{ $item->getImageUrl('all-reports') }}"/>
                <img class="small-bubble" src="{{ url('new-vox-img/small-bubble.png') }}" />
            </div>
            <div class="col">
                <p class="blue-background">{{ trans('vox.page.paid-reports.latest-report') }}</p>
                <div class="main-inner">
                    <h4>{{ $item->main_title }}</h4>
                    <h4 class="gray-title">{{ $item->title }}</h4>
                    <p>{{ Illuminate\Support\Str::words(strip_tags($item->short_description),20) }}</p>
                    
                    <div class="tags">
                        @foreach($item->languages as $lang)
                            <span>{{ App\Models\PaidReport::$langs[$lang] }} | </span>
                        @endforeach
                        <span>{{ date('F Y', $item->launched_at->timestamp) }} | </span>
                        @foreach($item->download_format as $format)
                            <span>{{ trans('vox.page.paid-reports.download-as') }} .{{ $format }} | </span>
                        @endforeach
                        <span>{{ trans('vox.page.paid-reports.pages-count', ['count' => $item->pages_count]) }}</span>
                    </div>
                    <br/>
                    <a href="{{ getLangUrl('dental-industry-reports/'.$item->slug) }}" class="blue-button new-style with-arrow red-button">
                        {{ trans('vox.page.paid-reports.more-info') }}<img src="{{ url('new-vox-img/white-arrow-right.svg') }}">
                    </a>
                </div>
            </div>
        </div>
    @endif
    
    @if($items->isNotEmpty())
        <div class="section-recent-surveys new-style-swiper">
            <div class="swiper-container {{ $items->count() <= 3 ? 'swiper-flex' : '' }}">
                <div class="swiper-wrapper">
                    @foreach($items as $report)
                        <div class="swiper-slide">
                            <div class="slider-inner">
                                <div class="slide-padding">
                                    <a href="{{ getLangUrl('dental-industry-reports/'.$report->slug) }}" class="cover" style="background-image: url('{{ $report->getImageUrl('all-reports') }}');">
                                        <img src="{{ $report->getImageUrl('all-reports') }}" alt="{{ $report->main_title }} {{ $report->title }}" style="display: none !important;"> 
                                    </a>
                                    <div class="vox-header clearfix">
                                        <h4 class="report-title bold">{{ $report->main_title }} {{ $report->title }}</h4>
                                        <div class="btns">
                                            <a class="opinion blue-button" href="{{ getLangUrl('dental-industry-reports/'.$report->slug) }}">
                                                {{ trans('vox.page.paid-reports.more-info') }}
                                            </a>
                                        </div>
                                    </div>
                                  </div>
                            </div>
                        </div>
                    @endforeach
                </div>
        
                <div class="swiper-pagination"></div>
            </div>
        </div>
    @endif

    <div class="about-dentavox">
        <img class="white-wave" src="{{ url('new-vox-img/white-wave-mirror.png') }}"/>
        <div class="index-container flex">
            <div class="about-image-wrapper">
                <img src="{{ url('new-vox-img/graphic.png') }}"/>
            </div>
            <div class="about-content">
                <h3>{{ trans('vox.page.paid-reports.about-dentavox-title') }}</h3>

                <p>
                    {{ trans('vox.page.paid-reports.about-dentavox-description') }}
                </p>                    
            </div>
        </div>
    </div>

    <div class="benefits-wrapper index-container tac">
        <h3>{{ trans('vox.page.paid-reports.benefits-title') }}</h3>
        <div class="flex">
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-1.png') }}"/>
                <h4>{!! trans('vox.page.paid-reports.benefits-subtitle-1') !!}</h4>
                <p>{{ trans('vox.page.paid-reports.benefits-description-1') }}</p>
            </div>
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-2.png') }}"/>
                <h4>{!! trans('vox.page.paid-reports.benefits-subtitle-2') !!}</h4>
                <p>{{ trans('vox.page.paid-reports.benefits-description-2') }}</p>
            </div>
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-3.png') }}"/>
                <h4>{!! trans('vox.page.paid-reports.benefits-subtitle-3') !!}</h4>
                <p>{{ trans('vox.page.paid-reports.benefits-description-3') }}</p>
            </div>
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-4.png') }}"/>
                <h4>{!! trans('vox.page.paid-reports.benefits-subtitle-4') !!}</h4>
                <p>{{ trans('vox.page.paid-reports.benefits-description-4') }}</p>
            </div>
        </div>
    </div>

    <div class="save-banner">
        <div class="index-container flex flex-center">
            <div class="column">
                <h2>{{ trans('vox.page.paid-reports.banner-title') }}</h2>
                <p>{!! trans('vox.page.paid-reports.banner-subtitle') !!}</p>
            </div>
            <div class="column">
                <img class="perc-off" src="{{ url('new-vox-img/80perc-off.png') }}"/>
            </div>
            <div class="column">
                <p class="checklist">
                    <img class="check" src="{{ url('new-vox-img/check-white.png') }}"/>
                    {{ trans('vox.page.paid-reports.banner-checklist-1') }}
                </p>
                <p class="checklist">
                    <img class="check" src="{{ url('new-vox-img/check-white.png') }}"/>
                    {{ trans('vox.page.paid-reports.banner-checklist-2') }}
                </p>
                <div class="tac">
                    <a href="{!! $items->count() > 1 ? 'javascript:;' : getLangUrl('dental-industry-reports/'.$item->slug) !!}" class="blue-button new-style with-arrow white-button {!! $items->count() > 1 ? 'go-to-reports' : '' !!}">
                        {{ trans('vox.page.paid-reports.see-reports') }}<img src="{{ url('new-vox-img/red-arrow-right.svg') }}">
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection