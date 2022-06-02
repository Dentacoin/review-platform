<div class="tab-container" id="team">
    <h2 class="mont">
        {!! nl2br(trans('trp.page.user.team')) !!} 
        @if($loggedUserAllowEdit)
            <a class="edit-field-button tooltip-text" text="{{ $hasTeamApproved || $hasNotVerifiedTeamFromInvitation ? 'Edit' : 'Add' }} team members">
                <img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
            </a>
        @endif
    </h2>

    <div class="team-container">
        @foreach( $item->teamApproved as $team)
            @if($team->clinicTeam)
                <a class="team approved-team" href="{{ !$team->clinicTeam || in_array($team->clinicTeam->status, ['dentist_no_email', 'added_new']) ? 'javascript:;' : $team->clinicTeam->getLink() }}" dentist-id="{{ $team->clinicTeam->id }}">
                    <div class="team-image" style="background-image: url('{{ $team->clinicTeam->getImageUrl(true) }}')">
                        @if( ($loggedUserAllowEdit) )
                            <div class="deleter" sure="{!! trans('trp.page.user.delete-sure', ['name' => $team->clinicTeam->getNames() ]) !!}">
                                <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}"/>
                            </div>
                        @endif
                    </div>
                    <div class="team-info">
                        <h4>{{ $team->clinicTeam->getNames() }}</h4>
                        <p>{!! trans('trp.team-jobs.dentist') !!}</p>
                        <div class="ratings">
                            <div class="stars">
                                <div class="bar" style="width: {{ $team->clinicTeam->avg_rating/5*100 }}%;"></div>
                            </div>
                            <span class="rating">
                                ({{ trans('trp.common.reviews-count', [ 'count' => intval($team->clinicTeam->ratings)]) }})
                            </span>
                        </div>
                    </div>
                </a>
            @endif
        @endforeach

        @if($hasNotVerifiedTeamFromInvitation)
            @foreach( $item->notVerifiedTeamFromInvitation as $invite)
                <a class="team" href="javascript:;" invite-id="{{ $invite->id }}">
                    <div class="team-image" style="background-image: url('{{ $invite->getImageUrl(true) }}')">
                        @if( ($loggedUserAllowEdit) )
                            <div class="delete-invite" sure="{!! trans('trp.page.user.delete-sure', ['name' => $invite->invited_name ]) !!}">
                                <img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}"/>
                            </div>
                        @endif
                    </div>
                    <div class="team-info">
                        {{-- ???????????????????????????????? --}}
                        {{-- @if(empty($invite->job))
                            <div class="not-verified">{!! nl2br(trans('trp.page.user.team-not-verified')) !!}</div>
                        @endif --}}
                        <h4>{{ $invite->invited_name }}</h4>
                        @if(empty($invite->job))
                            <p>{!! trans('trp.team-jobs.dentist') !!}</p>
                            <div class="ratings">
                                <div class="stars">
                                    <div class="bar" style="width: 0%;">
                                    </div>
                                </div>
                                <span class="rating">
                                    ({{ trans('trp.common.reviews-count', [ 'count' => '0']) }})
                                </span>
                            </div>
                        @else
                            <p>{!! trans('trp.team-jobs.'.$invite->job) !!}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        @endif

        @if($loggedUserAllowEdit)
            @foreach( $item->teamUnapproved as $team)
                @if($team->clinicTeam)
                    <a class="team pending" href="{{ $team->clinicTeam->getLink() }}" dentist-id="{{ $team->clinicTeam->id }}">
                        <div class="team-image" style="background-image: url('{{ $team->clinicTeam->getImageUrl(true) }}')"></div>
                        <div class="team-info">
                            <h4>{{ $team->clinicTeam->getNames() }}</h4>
                            <p>{!! trans('trp.team-jobs.dentist') !!}</p>
                            <div class="ratings">
                                <div class="stars">
                                    <div class="bar" style="width: {{ $team->clinicTeam->avg_rating/5*100 }}%;">
                                    </div>
                                </div>
                                <span class="rating">
                                    ({{ trans('trp.common.reviews-count', [ 'count' => intval($team->clinicTeam->ratings)]) }})
                                </span>
                            </div>
                            <div class="action-buttons flex">
                                <div class="accept-button" action="{{ getLangUrl('profile/dentists/accept/'.($team->clinicTeam->id)) }}">
                                    {!! nl2br(trans('trp.page.user.accept-dentist')) !!}
                                </div>
                                <div class="reject-button" 
                                action="{{ getLangUrl('profile/dentists/reject/'.($team->clinicTeam->id)) }}" 
                                sure="{!! trans('trp.page.user.delete-sure', ['name' => $team->clinicTeam->getNames() ]) !!}">
                                    {!! nl2br(trans('trp.page.user.reject-dentist')) !!}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        @endif
        
        @if( ($loggedUserAllowEdit) )
            <a href="javascript:;" class="team add-team-member dont-count" guided-action="team">
                @if(false)
                {{-- data-popup="add-team-popup" --}}
                @endif
                <div class="disabled-prop">
                    <div class="team-image" style="background-image: url('{{ url('img-trp/add-icon.png') }}')"></div>
                    <div class="team-info">
                        <span class="add-team-text">Add team member</span>
                    </div>
                </div>
                <span class="comming-soon">
                    Coming soon...
                </span>
            </a>
        @endif
    </div>
</div>