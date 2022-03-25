@if($forMap)
    <div 
        class="result-container dentist" 
        href="{{ $dentist->getLink() }}" 
        {!! $dentist->address ? 'lat="'.$dentist->lat.'" lon="'.$dentist->lon.'"' : '' !!} 
        dentist-id="{{ $dentist->id }}"
    >
        <div class="flex flex-mobile flex-center">
@else
    <a 
        class="result-container dentist flex" 
        href="{{ $dentist->getLink() }}" 
        {!! $dentist->address ? 'lat="'.$dentist->lat.'" lon="'.$dentist->lon.'"' : '' !!} 
        dentist-id="{{ $dentist->id }}"
    >
@endif


    <div class="dentist-image-wrapper">
        <img class="avatar" src="{{ $dentist->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 
            'name' => $dentist->getNames(), 
            'location' => $dentist->getLocation() 
        ]) }}"/>

    @if($forMap)
    </div>
    <div>
    @endif

    @if($dentist->is_partner)
        <div class="partner">
            <img class="tooltip-text" width="18" src="{{ url('img-trp/mini-logo-white.svg') }}" text="{!! nl2br(trans('trp.common.partner')) !!} {{ $dentist->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}"/>
            DCN Accepted
        </div>
    @endif
    <div class="ratings">
        <div class="stars">
            <div class="bar" style="width: {{ $dentist->avg_rating/5*100 }}%;">
            </div>
        </div>
        <span class="rating">
            ({{ trans('trp.common.reviews-count', [ 'count' => intval($dentist->ratings)]) }})
        </span>
    </div>

    @if($forMap)

        @if( $time = $dentist->getWorkHoursText() )
            @if(str_contains($time, 'Open now'))
                <div class="working-time open">
                    <img src="{{ url('img-trp/clock-blue.svg') }}" width="19"/>
                    Open now
                </div>
            @else
                <div class="working-time closed">
                    <img src="{{ url('img-trp/clock-red.svg') }}" width="19"/>
                    Closed now
                </div>
            @endif
        @endif
        </div>
    </div>
    @else
        </div>
    @endif

        <div class="dentist-info">
            <div class="dentist-title">
                <h4>
                    {{ $dentist->getNames() }}
                </h4>
                @if(!$forMap)
                    @if( $time = $dentist->getWorkHoursText() )
                        @if(str_contains($time, 'Open now'))
                            <div class="working-time open">
                                <img src="{{ url('img-trp/clock-blue.svg') }}">
                                Open now
                            </div>
                        @else
                            <div class="working-time closed">
                                <img src="{{ url('img-trp/clock-red.svg') }}">
                                Closed now
                            </div>
                        @endif
                    @endif
                @endif
            </div>
            @if($forMap)
                <p>{{ $dentist->address }}, {{ $dentist->city_name }}, {{ $dentist->country->name }}</p>

                <a href="{{ $dentist->getLink() }}" class="button-submit">
                    {{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
                    Check full profile
                    <span>></span>
                </a>
            @else
                <p>{{ $dentist->city_name }}, {{ $dentist->country->name }}</p>
                <p>{{ $dentist->address }}</p>
                <p>{{ $dentist->phone }}</p>
                <p href="{{ $dentist->website }}?popup-loged=submit-review-popup" target="_blank" class="text-link">{{ $dentist->website }}</p>

                @if( $dentist->socials )
                    <div class="socials">
                        @foreach($dentist->socials as $k => $v)
                            <span class="social" href="{{ $v }}" target="_blank">
                                <img src="{{ url('img-trp/social-network/'.$k.'.svg') }}" height="24"/>
                            </span>
                        @endforeach
                    </div>
                @endif

                @if(!empty($user) && $user->is_dentist)
                    <div href="{{ $dentist->getLink() }}" class="button-submit">
                        {{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
                        Check Profile
                    </div>
                @else
                    <div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup" class="button-submit">
                        {{-- {!! nl2br(trans('trp.common.submit-review')) !!} --}}
                        Write a review
                    </div>
                @endif
            @endif
        </div>
@if($forMap)
    </div>
@else
    </a>
@endif