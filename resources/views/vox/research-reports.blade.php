@extends('vox')

@section('content')

    <div class="main-title tac">
        <h1>DENTAL MARKET RESEARCH REPORTS</h1>
        <p class="subtitle">based on genuine global data</p>
        
		<img class="blue-circle" src="{{ url('new-vox-img/blue-circle-corner.png') }}"/>
    </div>

    @if(!empty($item))
        <div class="main-section flex">
            <div class="main-image-wrapper col">
                <p class="blue-background">LATEST REPORT</p>
                <img class="main-image" src="{{ $item->getImageUrl('social') }}"/>
                <img class="small-bubble" src="{{ url('new-vox-img/small-bubble.png') }}" />
            </div>
            <div class="col">
                <p class="blue-background">LATEST REPORT</p>
                <div class="main-inner">
                    <h4>{{ $item->main_title }}</h4>
                    <h4 class="gray-title">{{ $item->title }}</h4>
                    <p>{{ Illuminate\Support\Str::words(strip_tags($item->short_description),20) }}</p>
                    
                    <div class="tags">
                        @foreach($item->languages as $lang)
                            <span>English | </span>
                        @endforeach
                        <span>{{ date('F Y', $item->launched_at->timestamp) }} | </span>
                        @foreach($item->download_format as $format)
                            <span>Download as .{{ $format }} | </span>
                        @endforeach
                        <span>{{ $item->pages_count }} pages</span>
                    </div>
                    <br/>
                    <a href="{{ getLangUrl('dental-industry-reports/'.$item->slug) }}" class="blue-button new-style with-arrow red-button">MORE INFO<img src="{{ url('new-vox-img/white-arrow-right.svg') }}"></a>
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
                                    <a href="{{ getLangUrl('dental-industry-reports/'.$report->slug) }}" class="cover" style="background-image: url('{{ $report->getImageUrl('social') }}');">
                                        <img src="{{ $report->getImageUrl('social') }}" alt="{{ $report->main_title }} {{ $report->title }}" style="display: none !important;"> 
                                    </a>
                                    <div class="vox-header clearfix">
                                        <h4 class="report-title bold">{{ $report->main_title }} {{ $report->title }}</h4>
                                        <div class="btns">
                                            <a class="opinion blue-button" href="{{ getLangUrl('dental-industry-reports/'.$report->slug) }}">
                                                More info
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
                <h3>ABOUT DENTAVOX:</h3>

                <p>DentaVox research has become the benchmark in modern dental industry research. Limited results from our global oral health surveys are and will remain available for free. The brand new detailed market research reports, however, go a big step further in helping researchers, media, dental professionals, suppliers and students to find detailed, trustworthy data and analysis on highly specific dental topics.</p>                    
            </div>
        </div>
    </div>

    <div class="benefits-wrapper index-container tac">
        <h3>YOUR BENEFITS: </h3>
        <div class="flex">
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-1.png') }}"/>
                <h4>Respondents <br/>with verified <br/>identities</h4>
                <p>by the trusted blockchain-based KYC provider Civic</p>
            </div>
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-2.png') }}"/>
                <h4>Answers given <br/>under full <br/>attention </h4>
                <p>thanks to a smart system of checks, warnings, and bans </p>
            </div>
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-3.png') }}"/>
                <h4>International <br/>respondent <br/>base</h4>
                <p>allowing for trustworthy all-sides view of the market</p>
            </div>
            <div class="col">
                <img src="{{ url('new-vox-img/Benefits-4.png') }}"/>
                <h4>In-depth analysis <br/>of the collected <br/>ANSWERS</h4>
                <p>for data-driven conclusions by all dental market participants</p>
            </div>
        </div>
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
                    <a href="javascript:;" class="blue-button new-style with-arrow white-button go-to-reports">SEE REPORTS<img src="{{ url('new-vox-img/red-arrow-right.svg') }}"></a>
                </div>
            </div>
        </div>
    </div>

@endsection