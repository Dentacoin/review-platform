<div class="asks-section">
    
    <div class="container">
        <div class="tab-inner-section">
            
            <div class="tab-container" id="asks">

                <h2 class="mont">
                    My patients
                    {{-- {!! nl2br(trans('trp.page.user.patient-requests')) !!} ({{ $user->asks->count() }}) --}}
                </h2>

                @if($hasPatientAsks)

                    <h3>Patient requests received {!! $patient_asks ? '<span class="patientAsksPendingCount">'.$patient_asks.'</span>' : '' !!}</h3>

                    <div class="asks-container">
                        <table class="table paging" num-paging="10">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('trp.page.profile.asks.list-date') }}
                                    </th>
                                    <th>
                                        {{ trans('trp.page.profile.asks.list-name') }}
                                    </th>
                                    <th>
                                        Email
                                        {{-- {{ trans('trp.page.profile.asks.list-email') }} --}}
                                    </th>
                                    <th>
                                        Type
                                        {{-- {{ trans('trp.page.profile.asks.list-note') }} --}}
                                    </th>
                                    <th>
                                        Rewards
                                        <img class="tooltip-text" src="{{ url('img-trp/info-dark-gray.png') }}" width="15" height="15" text="Rewards received by you for Trusted Reviews submitted by verified patients."/>
                                    </th>
                                    <th>
                                        Action/ Status
                                        {{-- {{ trans('trp.page.profile.asks.list-status') }} --}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $user->asksWithoutHidden->sortBy(function ($elm, $key) {
                                    return $elm['status']=='waiting' ? -1 : 1;
                                }) as $ask )
                                    @php
                                        $askReview = \App\Models\Review::where('user_id', $ask->user->id)->where('dentist_id', $item->id)->orderBy('id', 'desc')->first();	
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ date('d.m.Y', $ask->created_at->timestamp) }}
                                        </td>
                                        <td>
                                            {{ $ask->user ? $ask->user->name : "deleted user" }}
                                        </td>
                                        <td>
                                            {{ $ask->user? $ask->user->email : 'deleted user' }}
                                        </td>
                                        <td>
                                            @if(!empty($ask->review_id) || ($ask->on_review && !empty($ask->user) && !empty($askReview)))
                                                Verification request
                                            @else
                                                Invite Request
                                            @endif
                                        </td>
                                        <td>
                                            @if($ask->status=='yes' && (!empty($ask->review_id) || ($ask->on_review && !empty($ask->user) && !empty($askReview))))
                                                {{ $ask->review ? $ask->review->rewardForReview() : $askReview->rewardForReview() }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($ask->status=='waiting')
                                                <div class="action-buttons flex">
                                                    <a href="javascript:;" class="accept-button handle-asks" link-form="{{ getLangUrl('profile/asks/accept/'.$ask->id) }}">
                                                        {{ trans('trp.page.profile.asks.accept') }}
                                                    </a>
                                                    <a href="javascript:;" class="reject-button handle-asks" link-form="{{ getLangUrl('profile/asks/deny/'.$ask->id) }}">
                                                        Decline
                                                        {{-- {{ trans('trp.page.profile.asks.deny') }} --}}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="{{ $ask->status=='yes' ? 'accepted-text' : 'declined-text' }}">
                                                    {{ $ask->status=='yes' ? 'Accepted' : 'Declined' }}
                                                    {{-- {{ trans('trp.page.profile.asks.status-'.$ask->status) }} --}}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($hasPatientInvites)
                    <h3>Invites sent by you</h3>

                    <div class="asks-container">

                        <table class="table paging" num-paging="10">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('trp.page.profile.invite.list-date') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('trp.page.profile.invite.list-name') }}
                                    </th>
                                    <th>
                                        Email
                                        {{-- {{ trans('trp.page.profile.invite.list-email') }} --}}
                                    </th>
                                    <th>
                                        Rewards
                                        <img class="tooltip-text" src="{{ url('img-trp/info-dark-gray.png') }}" width="15" height="15" text="You’ll receive your invite reward as soon as your patient submits their Trusted Review."/>
                                    </th>
                                    <th>
                                        {{ trans('trp.page.profile.invite.list-status') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $user->patients_invites as $inv )
                                    @if(!$inv->hidden)
                                        <tr>
                                            <td>
                                                {{ date('d.m.Y', $inv->created_at->timestamp) }}
                                            </td>
                                            <td>
                                                {{ $inv->invited_name }}
                                            </td>
                                            <td>
                                                {{ $inv->invited_email }}
                                            </td>
                                            <td>
                                                {{ $inv->rewarded ? $inv->inviteForReview() : '' }}
                                            </td>
                                            <td>
                                                @if($inv->invited_id)

                                                    @if(!empty($inv->hasReview($user->id)))
                                                        @if(!empty($inv->dentistInviteAgain($user->id)))
                                                            <a href="javascript:;" class="blue-button invite-again" data-href="{{ getLangUrl('invite-patient-again') }}" inv-id="{{ $inv->id }}">
                                                                {{ trans('trp.page.profile.invite.invite-again') }}
                                                            </a><br>
                                                        @endif
                                                        <a review-id="{{ $inv->hasReview($user->id)->id }}" href="javascript:;" class="ask-review check-review">
                                                            {{-- {{ trans('trp.page.profile.invite.status-review') }} --}}
                                                            Check review
                                                        </a>
                                                    @else
                                                        <span class="gray-text">
                                                            Pending
                                                            {{-- {{ trans('trp.page.profile.invite.status-no-review') }} --}}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="gray-text">
                                                        Pending
                                                        {{-- {{ trans('trp.page.profile.invite.status-no-review') }} --}}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>