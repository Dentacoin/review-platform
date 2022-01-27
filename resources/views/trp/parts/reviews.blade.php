<div class="review review-wrapper" review-id="{{ $review->id }}">
    <div class="review-header">
        <div class="review-avatar" style="background-image: url('{{ $review->user->getImageUrl(true) }}');"></div>
        @if($is_dentist)
            <span class="review-name">{{ !empty($review->user->self_deleted) ? ($review->verified ? trans('trp.common.verified-patient') : trans('trp.common.deleted-user')) : $review->user->name }}: </span>
        @else
            <span class="review-name">to {{ $current_dentist->getNames() }}: </span>
        @endif

        @if($review->verified)
            <div class="trusted-sticker mobile-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $current_dentist->getNames() ])) !!}">
                {!! nl2br(trans('trp.common.trusted')) !!}
                <img src="{{ url('img/info-white.svg') }}" width="15" height="15"/>
            </div>
        @endif

        @if($review->title)
            <span class="review-title">
                “{{ $review->title }}”
            </span>
        @endif

        @if($review->verified)
            <div class="trusted-sticker tooltip-text" text="{!! nl2br(trans('trp.common.trusted-tooltip', ['name' => $current_dentist->getNames() ])) !!}">
                {!! nl2br(trans('trp.common.trusted')) !!}
                <img src="{{ url('img/info-white.svg') }}" width="15" height="15"/>
            </div>
        @endif
    </div>
    <div class="review-rating">
        <div class="ratings">
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
        @if(!empty($review->treatments))
            @foreach($review->treatments as $t)
                <span class="treatment">• {!! App\Models\Review::handleTreatmentTooltips(trans('trp.treatments.'.$t)) !!}</span>
            @endforeach
        @endif
    </div>
    @if($for_profile)
        <div class="review-content">
            {!! nl2br($review->answer) !!}
            <a href="{{ $current_dentist->getLink() }}?review_id={{ $review->id }}" class="more">
                {!! nl2br(trans('trp.page.profile.trp.show-entire')) !!}
            </a>
        </div>
        @if(!$is_dentist)
            <a href="{{ $current_dentist->getLink() }}?popup=recommend-dentist" class="recommend-button">=
                <img src="https://reviews.dentacoin.com/img-trp/thumb-up.svg">
                {!! nl2br(trans('trp.page.profile.trp.recommend-dentist')) !!}
            </a>
        @endif
    @else
        <div class="review-content">
            {!! nl2br($review->answer) !!}
            <a href="javascript:;" class="more">
                {!! nl2br(trans('trp.page.user.show-entire')) !!}
            </a>
        </div>

        <div class="review-footer flex flex-mobile break-mobile">

            @if($review->reply)
                <a class="reply-button show-hide" href="javascript:;" alternative="▾ {{ trans('trp.page.user.show-replies') }}" >
                    ▴ {!! nl2br(trans('trp.page.user.hire-replies')) !!}
                </a>
            @endif
            <div class="col">
                @if(!empty($user) && $user->id==$current_dentist->id && !$review->verified && !empty($user->trusted))
                    <a class="button verify-review" href="javascript:;">
                        Verify
                    </a>
                @endif

                @if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
                    <a class="reply-review" href="javascript:;">
                        <span>
                            {!! nl2br(trans('trp.page.user.reply')) !!}
                        </span>
                    </a>
                @endif
                
                <a class="thumbs-up {!! ($my_upvotes && in_array($review->id, $my_upvotes) ) ? 'voted' : '' !!}" href="javascript:;">
                    <img src="{{ url('img-trp/thumbs-up'.(($my_upvotes && in_array($review->id, $my_upvotes)) ? '-color' : '').'.png') }}" width="24" height="30">
                    <span>
                        {{ intval($review->upvotes) }}
                    </span>
                </a>
                <a class="thumbs-down {!! ($my_downvotes && in_array($review->id, $my_downvotes)) ? 'voted' : '' !!}" href="javascript:;">
                    <img src="{{ url('img-trp/thumbs-down'.(($my_downvotes && in_array($review->id, $my_downvotes)) ? '-color' : '').'.png') }}" width="24" height="30">
                    <span>
                        {{ intval($review->downvotes) }}
                    </span>
                </a>

                <a class="share-review" href="javascript:;" data-popup="popup-share" share-href="{{ $current_dentist->getLink() }}?review_id={{ $review->id }}">
                    <img src="{{ url('img-trp/share-review.png') }}" width="24" height="30">
                    <span>
                        {!! nl2br(trans('trp.common.share')) !!}
                    </span>
                </a>
            </div>
        </div>

        @if(!$review->reply && !empty($user) && ($review->dentist_id==$user->id || $review->clinic_id==$user->id) )
            <div class="review-replied-wrapper reply-form" style="display: none;">
                <div class="review">
                    <div class="review-header">
                        <div class="review-avatar" style="background-image: url('{{ $current_dentist->getImageUrl(true) }}');"></div>
                        <span class="review-name">{{ $current_dentist->getNames() }}</span>
                    </div>
                    <div class="review-content">
                        <form method="post" action="{{ $current_dentist->getLink() }}reply/{{ $review->id }}" class="reply-form-element">
                            {!! csrf_field() !!}
                            <textarea class="input" name="reply" placeholder="{!! nl2br(trans('trp.page.user.reply-enter')) !!}"></textarea>
                            <button class="button" type="submit" name="">{!! nl2br(trans('trp.page.user.reply-submit')) !!}</button>
                            <div class="alert alert-warning" style="display: none;">
                                {!! nl2br(trans('trp.page.user.reply-error')) !!}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($review->reply)
            <div class="review-replied-wrapper">
                <div class="review">
                    <div class="review-header">
                        <div class="review-avatar" style="background-image: url('{{ $current_dentist->getImageUrl(true) }}');"></div>
                        <span class="review-name">{{ $current_dentist->getNames() }}</span>
                        <span class="review-date">
                            {{ $review->replied_at ? $review->replied_at->toFormattedDateString() : '-' }}
                        </span>
                    </div>
                    <div class="review-content">
                        {!! nl2br($review->reply) !!}
                    </div>

                    @if(!empty($user) && $user->id==$current_dentist->id)
                    @else
                        <div class="review-footer">
                            <div class="col">
                                <a class="thumbs-up {!! ($my_upvotes && in_array($review->id, $my_upvotes) ) ? 'voted' : '' !!}" href="javascript:;">
                                    <img src="{{ url('img-trp/thumbs-up'.(($my_upvotes && in_array($review->id, $my_upvotes)) ? '-color' : '').'.png') }}" width="24" height="30">
                                    <span>
                                        {{ intval($review->upvotes_reply) }}
                                    </span>
                                </a>
                                <a class="thumbs-down {!! ($my_downvotes && in_array($review->id, $my_downvotes) ) ? 'voted' : '' !!}" href="javascript:;">
                                    <img src="{{ url('img-trp/thumbs-down'.(($my_downvotes && in_array($review->id, $my_downvotes)) ? '-color' : '').'.png') }}" width="24" height="30">
                                    <span>
                                        {{ intval($review->downvotes_reply) }}
                                    </span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>