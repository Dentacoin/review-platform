@extends('vox')

@section('content')


    <div class="main-section single-main-section">
        <img class="blue-circle" src="{{ url('new-vox-img/blue-circle-corner.png') }}"/>
        <div class="paid-container"> <!-- 800px -->
            <a class="back-home" href="{{ getLangUrl('dental-industry-reports/') }}">
                Back
            </a>
        </div>
            
        <div class="flex paid-container">
            <div class="main-image-wrapper col">
                <img class="main-image" src="{{ $item->getImageUrl() }}"/>
                <img class="small-bubble" src="{{ url('new-vox-img/small-bubble.png') }}" />

                <div class="paid-tags">
                    @if(!empty($item->languages))
                        @foreach($item->languages as $lang)
                            <div class="tag">
                                <img src="{{ url('new-vox-img/en.svg') }}" width="36" height="36"/>
                                English
                            </div>
                        @endforeach
                    @endif
                    <div class="tag">
                        <img src="{{ url('new-vox-img/page.svg') }}" width="36" height="36"/>
                        {{ $item->pages_count }} pages
                    </div>
                    @if(!empty($item->download_format))
                        @foreach($item->download_format as $format)
                            <div class="tag">
                                <img src="{{ url('new-vox-img/pdf.svg') }}" width="36" height="36"/>
                                Download as .{{ $format }}
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="col">
                <div class="main-inner">
                    <h4>{{ $item->main_title }}</h4>
                    <h4 class="gray-title">{{ $item->title }}</h4>
                    <p class="date">Pub Month: {{ date('F Y', $item->launched_at->timestamp) }}</p>
                    
                    @if(!empty($item->checklists))
                        <div class="main-checklists">
                            @foreach(json_decode($item->checklists, true) as $checklist)
                                <p class="checklist flex flex-center">
                                    <img class="check" src="{{ url('new-vox-img/green-check.png') }}"/>
                                    {{ $checklist }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <div class="paid-tags mobile">
                        @if(!empty($item->languages))
                            @foreach($item->languages as $lang)
                                <div class="tag">
                                    <img src="{{ url('new-vox-img/en.svg') }}" width="36" height="36"/>
                                    English
                                </div>
                            @endforeach
                        @endif
                        <div class="tag">
                            <img src="{{ url('new-vox-img/page.svg') }}" width="36" height="36"/>
                            {{ $item->pages_count }} pages
                        </div>
                        @if(!empty($item->download_format))
                            @foreach($item->download_format as $format)
                                <div class="tag">
                                    <img src="{{ url('new-vox-img/pdf.svg') }}" width="36" height="36"/>
                                    Download as .{{ $format }}
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="flex flex-center price-main-wrapper">
                        <div class="price-wrapper">
                            <p class="real-price">($1000)</p>
                            <p class="discount-price">$ {{ $item->price }}</p>
                        </div>
                        <div class="order-button-wrap">
                            <a href="{{ getLangUrl('dental-industry-reports/'.$item->slug.'/checkout/') }}" class="blue-button new-style red-button order-button"><img src="{{ url('new-vox-img/cart.svg') }}"/>Order</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="paid-container">

        <div class="report-description">
            {!! nl2br($item->short_description) !!}
        </div>
        <div class="accordions">
            <div class="accordion">
                <a href="javascript:;" class="accordion-title">
                    TABLE OF CONTENTS

                    <div class="accordion-buttons">
                        <img class="plus" src="{{ url('new-vox-img/plus-image.png') }}" width="46" height="46"/>
                        <img class="minus" src="{{ url('new-vox-img/minus-image.png') }}" width="46" height="46"/>
                    </div>
                </a>
                <div class="accordion-description">
                    @if(!empty($item->table_contents))
                        @foreach(json_decode($item->table_contents, true) as $content)
                            <div class="flex table-content {!! $content['is_main'] ? 'chapter' : '' !!}">
                                <p>{{ $content['content'] }}</p>
                                @if(!empty($content['page']))
                                    <p class="page">{{ $content['page'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="accordion">
                <a href="javascript:;" class="accordion-title">
                    METHODOLOGY

                    <div class="accordion-buttons">
                        <img class="plus" src="{{ url('new-vox-img/plus-image.png') }}" width="46" height="46"/>
                        <img class="minus" src="{{ url('new-vox-img/minus-image.png') }}" width="46" height="46"/>
                    </div>
                </a>
                <div class="accordion-description">
                    {!! $item->methodology !!}
                </div>
            </div>
            <div class="accordion">
                <a href="javascript:;" class="accordion-title">
                    SUMMARY

                    <div class="accordion-buttons">
                        <img class="plus" src="{{ url('new-vox-img/plus-image.png') }}" width="46" height="46"/>
                        <img class="minus" src="{{ url('new-vox-img/minus-image.png') }}" width="46" height="46"/>
                    </div>
                </a>
                <div class="accordion-description summary-content">
                    {!! $item->summary !!}
                </div>
            </div>
        </div>

        @if($item->photos->isNotEmpty())
            <div class="sample-pages-wrapper">
                <h3>SAMPLE PAGES</h3>

                <div class="sample-pages">
                    <div class="flex">
                        @foreach($item->photos as $photo)
                            <div class="col">
                                <a data-lightbox="gallery" href="{{ $photo->getImageUrl() }}">
                                    {{-- <div class="gallery-image" style="background-image: url('{{ $photo->getImageUrl(true) }}')"></div> --}}
                                    <img src="{{ $photo->getImageUrl(true) }}"/>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="save-banner">
        <div class="index-container flex flex-center">
            <div class="column">
                <h2>SAVE UP TO</h2>
                <p>The most affordable prices<br/>compared to average<br/>industry costs</p>
            </div>
            <div class="column">
                <img class="perc-off" src="{{ url('new-vox-img/80perc-off.png') }}"/>
            </div>
            <div class="column">
                <p class="checklist">
                    <img class="check" src="{{ url('new-vox-img/check-white.png') }}"/>
                    Payable via PayPal or crypto
                </p>
                <p class="checklist">
                    <img class="check" src="{{ url('new-vox-img/check-white.png') }}"/>
                    Accepted cryptocurrencies: DCN, BTC and ETH
                </p>
                
                <div class="tac">
                    <a href="{{ getLangUrl('dental-industry-reports/'.$item->slug.'/checkout/') }}" class="blue-button new-style white-button with-cart"><img src="{{ url('new-vox-img/cart-red.svg') }}"/>Order</a>
                </div>
            </div>
        </div>
    </div>

@endsection