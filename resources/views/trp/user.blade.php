@extends('trp')

@section('content')

@php
	$loggedUserAllowEdit = !empty($user) && ($user->id==$item->id || $editing_branch_clinic) ? true : false;
	$real_user = !empty($user) ? $user : null;
	$user = !empty($editing_branch_clinic) ? $editing_branch_clinic : (!empty($user) ? $user : null);
	
	$videoReviewsCount = $item->reviews_in_video()->count();
	$regularReviewsCount = $item->reviews_in_standard()->count();
	$hasPatientInvites = $loggedUserAllowEdit && $user->patients_invites->isNotEmpty();
	$hasPatientAsks = $loggedUserAllowEdit && $user->asks->isNotEmpty();

	$hasTeamApproved = $item->teamApproved->isNotEmpty();
	$hasNotVerifiedTeamFromInvitation = $item->notVerifiedTeamFromInvitation->isNotEmpty();

	$workplace = $item->getWorkplaceText( $loggedUserAllowEdit );
	$workingTime = $item->getWorkHoursText();

	$showAboutSection = $item->description || $item->categories->isNotEmpty() || $item->accepted_payment || ($loggedUserAllowEdit);
	$showTeamSection = $item->is_clinic && ( $loggedUserAllowEdit || $hasTeamApproved || $hasNotVerifiedTeamFromInvitation );
	$showLocationsSection = ($item->lat && $item->lon) || $item->photos->isNotEmpty() || ( $loggedUserAllowEdit);
	$showMoreInfoSection = $item->education_info || $item->experience || $item->languages || $item->founded_at || $loggedUserAllowEdit;
	$dentistWorkHours = $item->work_hours ? (is_array($item->work_hours) ? $item->work_hours : json_decode($item->work_hours, true)) : null;


	$week_days = [
		1 => 'Mon',
		'Tue',
		'Wed',
		'Thu',
		'Fri',
		'Sat',
		'Sun',
	];
@endphp

@if(!empty($user))

	@if( $loggedUserAllowEdit )
		@include('trp.popups.add-branch')
		{{-- @include('trp.popups.widget') --}}
		@include('trp.popups.invite')
		{{-- @if(!empty(session('first_guided_tour')) || !empty(session('reviews_guided_tour')))
			@include('trp.popups.first-guided-tour')
		@endif --}}
		@if( $user->is_clinic )
			{{-- @include('trp.popups.add-member') --}}
		@endif
		
		@if($user->wallet_addresses->isEmpty() && $user->is_partner && !$editing_branch_clinic)
			@include('trp.popups.add-wallet-address')
		@endif
	@else
		{{-- @if(!empty($writes_review))
			@include('trp.popups.recommend-dentist')
		@endif --}}
		{{-- @if(empty($is_trusted) && !$has_asked_dentist)
			@include('trp.popups.ask-dentist')
		@endif --}}
		@if(!$user->is_dentist)
			@include('trp.popups.submit-review')
		@endif
	@endif
@elseif(empty($user) && in_array($item->status, config('dentist-statuses.unclaimed')))
	@include('trp/popups/claim-profile')
@endif
@include('trp.popups.detailed-review')

{{-- <div class="popup fixed-popup first-guided-tour-done-popup tour-popup" id="first-guided-tour-done">
	<div class="popup-inner-tour tac">

		<h2>{{ trans('trp.guided-tour.well-done') }}</h2>

		<div class="tour-buttons">
			<a href="javascript:;" class="button-white tour-button done-tour">
				{{ trans('trp.guided-tour.ok') }}
			</a>
		</div>
	</div>
</div> --}}

<script type="application/ld+json">
	{!! json_encode($schema, JSON_UNESCAPED_SLASHES) !!}
</script>

<script type="text/javascript">
	var load_lightbox = {!! $load_lightbox !!};
	var showPartnerWalletPopup = {!! $item->partner_wallet_popup && $item->partner_wallet_popup < Carbon::now() ? 'true' : 'false' !!};
</script>

@endsection