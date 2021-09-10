@if($for_branch)
    <a href="{{ !empty($user) && $user->id == $clinic->id ? 'javascript:;' : $dentist->getLink() }}" class="result-container branch dentist clearfix {{ !empty($user) && $user->id == $clinic->id ? 'login-as' : '' }}" {!! !empty($user) && $user->id == $clinic->id ? 'login-url="'.getLangUrl('loginas').'" branch-id="'.$dentist->id.'" redirect-url="'.getLangUrl('branches/'.$dentist->slug).'"' : '' !!} full-dentist-id="{{ $dentist->id }}">
@else
    <a href="{{ $dentist->getLink() }}" class="result-container dentist clearfix {{ $main_clinic ? 'current-branch' : '' }}" full-dentist-id="{{ $dentist->id }}">
@endif
    @if($main_clinic)
    	<img class="angle-check" src="{{ url('img-trp/angle-check.png') }}">
    @endif
    <div class="avatar{!! $dentist->hasimage ? '' : ' default-avatar' !!}"  style="background-image: url('{{ $dentist->getImageUrl(true) }}')">
        @if($dentist->hasimage)
            <img src="{{ $dentist->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $dentist->getNames(), 'location' => ($dentist->city_name ? $dentist->city_name.', ' : '').($dentist->state_name ? $dentist->state_name.', ' : '').($dentist->country->name) ]) }}" style="display: none !important;"> 
        @endif
        @if(($for_branch || $main_clinic ) && $dentist->id == $dentist->mainBranchClinic->id)
            <div class="main-clinic">{!! nl2br(trans('trp.common.primary-account')) !!}</div>
        @endif
    </div>
    <div class="media-right">
        <h4>
            {{ $dentist->getNames() }}
        </h4>
        @if($dentist->is_partner)
            <span class="type">
                <div class="img">
                    <img src="{{ url('img-trp/mini-logo.png') }}" width="14" height="14">
                </div>
                <span>{!! nl2br(trans('trp.page.search.partner')) !!}</span> 
                {{ $dentist->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}
            </span>
        @endif
        <div class="p">
            <div class="img">
                <img src="{{ url('img-trp/map-pin.png') }}" width="11" height="14">
            </div>
            {{ $dentist->city_name ? $dentist->city_name.', ' : '' }}
            {{ $dentist->state_name ? $dentist->state_name.', ' : '' }} 
            {{ $dentist->country->name }} 
        </div>
        @if( $time = $dentist->getWorkHoursText() )
            <div class="p">
                <div class="img">
                    <img src="{{ url('img-trp/open.png') }}" width="13" height="14">
                </div>
                {!! $time !!}
            </div>
        @endif
        @if( $dentist->website )
            <div class="p dentist-website" href="{{ $dentist->getWebsiteUrl() }}" target="_blank">
                <div class="img">
                    <img class="black-filter" src="{{ url('img-trp/website-icon.svg') }}" width="14" height="14">
                </div>
                <span>
                    {{ $dentist->website }}
                </span>
            </div>
        @endif
        @if($dentist->top_dentist_month)
            <div class="top-dentist">
                <img src="{{ url('img-trp/top-dentist.png') }}" width="15" height="15">
                <span>
                    {!! trans('trp.common.top-dentist') !!}
                </span>
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
        @if($for_branch)
            @if(!empty($user) && $user->id == $clinic->id)
                <div href="javascript:;" login-url="{{ getLangUrl('loginas') }}" branch-id="{{ $dentist->id }}" class="button button-submit login-as {{ $dentist->id != $dentist->mainBranchClinic->id ? 'mt' : '' }}">
                    <i></i>
                    {!! nl2br(trans('trp.page.user.branch.switch-account')) !!}
                </div>

                @if($dentist->id != $dentist->mainBranchClinic->id)
                    <div href="javascript:;" delete-url="{{ getLangUrl('delete-branch') }}" branch-id="{{ $dentist->id }}" class="delete-branch">
                        Delete Branch X
                    </div>
                @endif
            @else
                <div href="{{ $dentist->getLink() }}" class="button button-submit">
                    {!! nl2br(trans('trp.common.see-profile')) !!}
                </div>
            @endif
        @else
            @if(!empty($user) && $user->is_dentist)
                <div href="{{ $dentist->getLink() }}" class="button button-submit">
                    {!! nl2br(trans('trp.common.see-profile')) !!}
                </div>
            @else
                <div href="{{ $dentist->getLink() }}?popup-loged=submit-review-popup" class="button button-submit">
                    {!! nl2br(trans('trp.common.submit-review')) !!}
                </div>
            @endif
        @endif
        <div class="share-button" data-popup="popup-share" share-href="{{ $dentist->getLink() }}">
            <img src="{{ url('img-trp/share.png') }}">
        </div>
    </div>
</a>