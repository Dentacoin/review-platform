<div 
    class="written-review {{ $hidden ? 'hidden-review' : '' }} {{ isset($video) ? 'video-review' : 'regular-review' }}" 
    review-id="{{ $review->id }}"
    trusted="{{ $review->verified ? 1 : 0 }}"
    time="{{ $review->created_at->timestamp }}"
    rating="{{ !empty($review->team_doctor_rating) && ($review->review_to_id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating }}"
    find-in="{{ strtolower(trim($review->title)) }} {{ strtolower(trim($review->answer)) }} {{ strtolower(trim($review->user->name)) }}">
    @if(isset($video))
        <div class="video-wrapper">
            <iframe width="480" height="270" src="https://www.youtube.com/embed/{{ $review->youtube_id }}" frameborder="0" allow="encrypted-media" allowfullscreen></iframe>
        </div>
        {{-- <div class="video-image cover" style="background-image: url('https://img.youtube.com/vi/{{ $review->youtube_id }}/hqdefault.jpg');"></div> --}}
    @endif

    <div class="review-header">
        <img class="review-avatar" src="{{ $review->user->getImageUrl(true) }}"/>
        <div>
            @if($is_dentist)
                <span class="review-name">
                    {{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}: 
                </span>
            @else
                <span class="review-name">to {{ $current_dentist->getNames() }}: </span>
            @endif

            @if($review->title)
                <span class="review-title">
                    “{{ $review->title }}”
                </span>
            @endif
        </div>

        <a href="javascript:;" class="share-button" data-popup="popup-share" share-href="{{ $current_dentist->getLink() }}?review_id={{ $review->id }}">
            <img src="{{ url('img-trp/share-arrow-gray.svg') }}">
            {!! nl2br(trans('trp.common.share')) !!}
        </a>
    </div>
    <div class="review-rating">
        <div 
        class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', [
            'name' => $current_dentist->getNames() 
        ])) !!}"
        {!! $review->verified ? '' : 'style="display:none;"' !!}
        >
            {!! nl2br(trans('trp.common.trusted')) !!}
            <img src="{{ url('img/info-white.svg') }}" width="15" height="15"/>
        </div>
        @if(!$for_profile && !empty($user) && $user->id==$current_dentist->id && !$review->verified && !empty($user->trusted))
            <a class="green-button verify-review" href="javascript:;">
                Verify patient
            </a>
        @endif
        <div class="ratings average">
            <div class="stars">
                <div class="bar" style="width: {{ !empty($review->team_doctor_rating) && ($review->review_to_id == $review->dentist_id) ? $review->team_doctor_rating/5*100 : $review->rating/5*100 }}%;">
                </div>
            </div>
            <span class="rating">
                ({{ !empty($review->team_doctor_rating) && ($review->review_to_id == $review->dentist_id) ? $review->team_doctor_rating : $review->rating }})
            </span>
        </div>
        <span class="review-date">
            {{ $review->created_at ? $review->created_at->toFormattedDateString() : '-' }}
        </span>
    </div>
    @if(isset($video))
    @else
        @if($for_profile)
            <div class="review-content">
                {!! nl2br($review->answer) !!}
                <a href="{{ $current_dentist->getLink() }}?review_id={{ $review->id }}" class="more">
                    {!! nl2br(trans('trp.page.profile.trp.show-entire')) !!}
                </a>
            </div>
            {{-- @if(!$is_dentist)
                <a href="{{ $current_dentist->getLink() }}?popup=recommend-dentist" class="recommend-button">=
                    <img src="https://reviews.dentacoin.com/img-trp/thumb-up.svg">
                    {!! nl2br(trans('trp.page.profile.trp.recommend-dentist')) !!}
                </a>
            @endif --}}
        @else
            <div class="review-content">
                {!! nl2br($review->answer) !!}...
                <a href="javascript:;" class="more">
                    show full review
                    {{-- {!! nl2br(trans('trp.page.user.show-entire')) !!} --}}
                </a>
            </div>

            <div class="review-footer flex flex-mobile break-mobile">
                <div class="col">

                    @if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
                        <a class="blue-button reply-review" href="javascript:;">
                            {!! nl2br(trans('trp.page.user.reply')) !!}
                        </a>
                    @endif
                </div>
            </div>

            @if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
                <div class="review-replied-wrapper reply-form" style="display: none;">
                    <div class="review">
                        <div class="review-header">
                            <img class="review-avatar" src="{{ $current_dentist->getImageUrl(true) }}"/>
                            <span class="review-name">{{ $current_dentist->getNames() }}</span>
                        </div>
                        <div class="review-content">
                            <form method="post" action="{{ $current_dentist->getLink() }}reply/{{ $review->id }}" class="reply-form-element">
                                {!! csrf_field() !!}
                                <textarea class="input" name="reply" placeholder="{!! nl2br(trans('trp.page.user.reply-enter')) !!}"></textarea>
                                <button class="blue-button" type="submit" name="">{!! nl2br(trans('trp.page.user.reply-submit')) !!}</button>
                                <div class="alert alert-warning" style="display: none;">
                                    {!! nl2br(trans('trp.page.user.reply-error')) !!}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($review->reply)
                <div class="review-replied-wrapper">
                    <img class="review-avatar" src="{{ $current_dentist->getImageUrl(true) }}"/>
                    <div>
                        <p class="replied-info">
                            <img src="{{ url('img-trp/reply-icon.svg') }}" />Replied by {{ $current_dentist->getNames() }} {{ $review->replied_at ? 'on '.$review->replied_at->toFormattedDateString() : '' }}
                        </p>
                        <p class="review-content">{!! nl2br($review->reply) !!}</p>
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>