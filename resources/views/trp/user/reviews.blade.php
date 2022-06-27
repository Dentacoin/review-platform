<div class="tab-container" id="reviews">

    <h2 class="mont">
        {!! nl2br(trans('trp.page.user.reviews')) !!}
    </h2>

    @if($regularReviewsCount && $videoReviewsCount)
        <div class="reviews-type-buttons">
            <a href="javascript:;" class="show-written-reviews active">
                Written reviews
            </a>
            <a href="javascript:;" class="show-video-reviews">
                Video reviews
            </a>
        </div>
    @endif

    <div class="written-reviews-wrapper">
        <div class="aggregated-rating-wrapper flex">
            <div class="col">
                <div class="rating mont">
                    {{ number_format($item->avg_rating, 1) }}
                </div>
                <div class="ratings big">
                    <div class="stars">
                        <div class="bar" style="width: {{ $item->avg_rating/5*100 }}%;">
                        </div>
                    </div>
                </div>
                <div class="reviews-count">
                    ({{ trans('trp.common.reviews-count', [ 'count' => intval($item->ratings)]) }})
                </div>
            </div>
            <div class="flex flex-mobile">
                @foreach($aggregatedRating as $agg_rating)
                    <div class="overview-column">
                        @if($agg_rating['type'] == 'blue')
                            <div class="new-question">
                                new
                            </div>
                        @endif
                        <p>
                            {{ $agg_rating['label'] }}
                        </p>
                        <div class="ratings average">
                            <div class="stars">
                                <div class="bar" style="width: {{ $agg_rating['rating'] / 5 * 100 }}%;"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="reviews-filter regular-review-tab">
            <span>Filter by: </span>
            <span href="javascript:;" class="filter">
                <span class="label">Newest</span>
                <div class="caret-down"></div>
            
                <div class="filter-options">
                    <label class="checkbox-label active" for="filter-newest">
                        <input type="radio" class="special-checkbox filter-type" name="filter" id="filter-newest" value="newest" checked="checked" label="Newest">
                        <div class="checkbox-square">✓</div>
                        Newest
                    </label>
                    <label class="checkbox-label" for="filter-oldest">
                        <input type="radio" class="special-checkbox filter-type" name="filter" id="filter-oldest" value="oldest" label="Oldest">
                        <div class="checkbox-square">✓</div>
                        Oldest
                    </label>
                    <label class="checkbox-label" for="filter-highest">
                        <input type="radio" class="special-checkbox filter-type" name="filter" id="filter-highest" value="highest" label="Highest rated">
                        <div class="checkbox-square">✓</div>
                        Highest rated
                    </label>
                    <label class="checkbox-label" for="filter-lowest">
                        <input type="radio" class="special-checkbox filter-type" name="filter" id="filter-lowest" value="lowest" label="Lowest rated">
                        <div class="checkbox-square">✓</div>
                        Lowest rated
                    </label>
                </div>
            </span>
            <span href="javascript:;" class="filter">
                <span class="label">All reviews</span>
                <div class="caret-down"></div>
            
                <div class="filter-options">
                    <label class="checkbox-label active" for="type-all">
                        <input type="radio" class="special-checkbox filter-type" name="type" id="type-all" value="all" checked="checked" label="All reviews">
                        <div class="checkbox-square">✓</div>
                        All reviews
                    </label>
                    <label class="checkbox-label" for="type-trusted">
                        <input type="radio" class="special-checkbox filter-type" name="type" id="type-trusted" value="trusted" label="Trusted reviews">
                        <div class="checkbox-square">✓</div>
                        Trusted reviews
                    </label>
                </div>
            </span>
            <div class="search-reviews-wrapper">
                <img src="{{ url('img-trp/black-search.svg') }}" width="17" height="18">
                <input type="text" name="search-review" id="search-review" placeholder="Quick search">
            </div>

            <p class="reviews-count">
                {{ $item->ratings }} reviews
            </p>
        </div>

        {{-- <div id="append-section-reviews"></div> --}}

        @if($regularReviewsCount)
            <div class="written-reviews regular-review-tab">
                @foreach($dentistReviewsIn as $review)
                    @if($review->user)
                        @include('trp.parts.reviews', [
                            'review' => $review,
                            'hidden' => $loop->iteration > 10,
                            'is_dentist' => true,
                            'for_profile' => false,
                            'current_dentist' => $review->getDentist($item),
                        ])

                        @if($loop->iteration == 10 && $regularReviewsCount>10)
                            <a href="javascript:;" class="show-more-reviews">
                                SHOW 10 more reviews
                            </a>
                        @endif
                    @endif
                @endforeach
            </div>
        @endif

        @if($videoReviewsCount)
            <div class="video-reviews video-review-tab {{ $videoReviewsCount > 2 ? 'video-reviews-flickity' : 'video-reviews-flex' }}" {!! $regularReviewsCount ? 'style="display:none;"' : '' !!}>
                @foreach($item->reviews_in_video() as $review)
                    @if($review->user)
                        @include('trp.parts.reviews', [
                            'review' => $review,
                            'video' => true,
                            'hidden' => $loop->iteration > 10,
                            'is_dentist' => true,
                            'for_profile' => false,
                            'current_dentist' => $review->getDentist($item),
                        ])
                    @endif
                @endforeach
            </div>
        @endif

        <div class="alert alert-info" id="no-reviews">Sorry, we couldn't find any reviews containing your search query.</div>
    </div>
</div>