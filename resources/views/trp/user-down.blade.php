@if($showTeamSection)
    @include('trp.user.team')
@endif

@if($regularReviewsCount || $videoReviewsCount )
    @include('trp.user.reviews')
@endif

@if( $showLocationsSection )
    @include('trp.user.location')
@endif

@if($showMoreInfoSection)
    @include('trp.user.more')
@endif

@if($item->highlights->isNotEmpty())
    <div class="tab-container">
        <h2 class="mont">
            Highlights
        </h2>

        <div class="tab-inner-section">
            <div class="hightlights-wrapper {{ $item->highlights->count() > 1 ? 'highlights-mobile-flickity' : '' }} {{ $item->highlights->count() > 3 ? 'highlights-flickity' : 'flex' }}">
                @foreach($item->highlights as $highlight)
                    <a href="{{ $highlight->link }}" target="_blank" class="hightlight">
                        <div class="hightlight-image">
                            <img src="{{ $highlight->getImageUrl() }}"/>
                        </div>
                        <p>{{ $highlight->title }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif