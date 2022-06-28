<div 
    class="result-container dentist {{ !$forMap ? 'flex' : '' }}"
    {!! $dentist->address ? 'lat="'.$dentist->lat.'" lon="'.$dentist->lon.'"' : '' !!} 
    dentist-id="{{ $dentist->id }}"
    dentist-link="{{ $dentist->getLink() }}"
>
    @if($forMap)
        <div class="flex flex-mobile flex-center">
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
                    <img src="{{ url('img-trp/clock-blue.svg') }}" width="19" height="17"/>
                    Open now
                </div>
            @else
                <div class="working-time closed">
                    <img src="{{ url('img-trp/clock-red.svg') }}" width="19" height="17"/>
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
        @if($for_branch && $dentist->id == $dentist->mainBranchClinic->id)
            <div class="main-clinic mont">{!! nl2br(trans('trp.common.primary-account')) !!}</div>
        @endif
        <div class="dentist-title">
            <h4>
                {{ $dentist->getNames() }}
            </h4>
            @if(!$forMap)
                @if( $time = $dentist->getWorkHoursText() )
                    @if(str_contains($time, 'Open now'))
                        <div class="working-time open">
                            <img src="{{ url('img-trp/clock-blue.svg') }}" width="19" height="17"/>
                            Open now
                        </div>
                    @else
                        <div class="working-time closed">
                            <img src="{{ url('img-trp/clock-red.svg') }}" width="19" height="17"/>
                            Closed now
                        </div>
                    @endif
                @endif
            @endif
        </div>
        @if($forMap)
            <p>{{ $dentist->address }}, {{ $dentist->city_name }}{{ $dentist->country_id ? ', '.$dentist->country->name : '' }}</p>

            <a href="{{ $dentist->getLink() }}" class="button-submit">
                {{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
                Check full profile
                <span>></span>
            </a>
        @else
            <p class="d-address">{{ $dentist->city_name }}{{ $dentist->country_id ? ', '.$dentist->country->name : '' }}</p>
            <p class="d-address">{{ $dentist->address }}</p>
            <p>{{ $dentist->getFormattedPhone() }}</p>
            <a href="{{ $dentist->getWebsiteUrl() }}?popup-loged=submit-review-popup" target="_blank" class="text-link">{{ $dentist->website }}</a>

            @if( $dentist->socials )
                <div class="socials">
                    @foreach($dentist->socials as $k => $v)
                        <a class="social" href="{{ $v }}" target="_blank">
                            <img src="{{ url('img-trp/social-network/'.$k.'.svg') }}" height="24" width="25"/>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($for_branch && !empty($user) && $user->is_clinic && $dentist->is_clinic && $user->branches->isNotEmpty() && in_array($dentist->id, $user->branches->pluck('branch_clinic_id')->toArray()))
                <a href="{{ $dentist->getLink() }}" class="button-submit">
                    {{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
                    Edit branch
                </a>
            @elseif(!empty($user) && $user->is_dentist)
                <a href="{{ $dentist->getLink() }}" class="button-submit">
                    {{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
                    Check Profile
                </a>
            @else
                <a href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup" class="button-submit">
                    {{-- {!! nl2br(trans('trp.common.submit-review')) !!} --}}
                    Write a review
                </a>
            @endif
        @endif
    </div>

    <div class="hidden-info-window" style="display: none">
        <div class="info-window">
            <div class="flex">
                <div class="info-avatar">
                    <img class="avatar" src="{{ $dentist->getImageUrl(true) }}"/>
                </div>
                <div class="info-dentist">
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
                    @if( $time = $dentist->getWorkHoursText() )
                        @if(str_contains($time, 'Open now'))
                            <div class="working-time open">
                                <img src="{{ url('img-trp/clock-blue.svg') }}" width="19" height="17"/>
                                Open now
                            </div>
                        @else
                            <div class="working-time closed">
                                <img src="{{ url('img-trp/clock-red.svg') }}" width="19" height="17"/>
                                Closed now
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <h4>
                {{ $dentist->getNames() }}
            </h4>
            
            <p>{{ $dentist->address }}, {{ $dentist->city_name }}{{ $dentist->country_id ? ', '.$dentist->country->name : '' }}</p>

            <a href="{{ $dentist->getLink() }}" class="button-submit">
                {{-- {!! nl2br(trans('trp.common.see-profile')) !!} --}}
                Check full profile
                <span>></span>
            </a>
        </div>
    </div>
</div>