<div class="flickity-oval">
    <div class="flex space-between flex-center">
        <a href="javascript:;" class="slider-left"></a>
        <div>
            @if(isset($title))
                <h2 class="mont">{{ $title }}</h2>
            @endif
            @if(isset($subtitle))
                <h3>{{ $subtitle }}</h3>
            @endif
        </div>
        <a href="javascript:;" class="slider-right active"></a>
    </div>

    <div class="flickity">
        <div class="index-slider">
            @foreach( $featured as $dentist )
                <a class="slider-wrapper" href="{{ $dentist->getLink() }}">
                    <div class="slider-inner">
                        @if(!empty($user) && $user->hasReviewTo($dentist->id))
                            <img class="has-review-image" src="{{ url('img-trp/patient-review.png') }}">
                        @endif
                        <div class="slider-image-wrapper">
                            <img 
                                class="slider-real-image" 
                                src="{{ $dentist->getImageUrl(true) }}" 
                                alt="{{ trans('trp.alt-tags.reviews-for', [
                                    'name' => $dentist->getNames(), 
                                    'location' => $dentist->getLocation() 
                                ]) }}" 
                                width="86" 
                                height="86"
                            /> 
                            @if($dentist->is_partner)
                                <div class="partner">
                                    <img src="{{ url('img-trp/mini-logo-white.svg') }}"/>
                                    DCN Accepted
                                </div>
                            @endif
                        </div>
                        <div class="slider-container">
                            <h4>{{ $dentist->getNames() }}</h4>
                            <div class="p flex flex-center">
                                <img src="img-trp/pin-gray.svg" width="20" height="25">
                                {{ $dentist->getLocation() }}
                            </div>
                            <div class="ratings">
                                <div class="stars">
                                    <div class="bar" style="width: {{ $dentist->avg_rating/5*100 }}%;">
                                    </div>
                                </div>
                                <span class="rating">
                                    ({{ trans('trp.common.reviews-count', [ 'count' => intval($dentist->ratings)]) }})
                                </span>
                            </div>
                            @php
                                $review = $dentist->reviews_in_standard()->first();
                            @endphp
                            @if( $review )
                                <p class="review-content">
                                    “{{ $review->answer }}”
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>