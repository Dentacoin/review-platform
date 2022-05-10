@extends('trp')

@section('content')

@php
	$videoReviewsCount = $item->reviews_in_video()->count();
	$regularReviewsCount = $item->reviews_in_standard()->count();
	$hasPatientInvites = !empty($user) && $user->id==$item->id && $user->patients_invites->isNotEmpty();
	$hasPatientAsks = !empty($user) && $user->id==$item->id && $user->asks->isNotEmpty();

	$hasTeamApproved = $item->teamApproved->isNotEmpty();
	$hasNotVerifiedTeamFromInvitation = $item->notVerifiedTeamFromInvitation->isNotEmpty();

	$workplace = $item->getWorkplaceText( !empty($user) && $user->id==$item->id );
	$workingTime = $item->getWorkHoursText();

	$showAboutSection = $item->description || $item->categories->isNotEmpty() || (!empty($user) && $item->id==$user->id);
	$showTeamSection = $item->is_clinic && ( (!empty($user) && $item->id==$user->id) || $hasTeamApproved || $hasNotVerifiedTeamFromInvitation );
	$showLocationsSection = ($item->lat && $item->lon) || $item->photos->isNotEmpty() || ( !empty($user) && $user->id==$item->id);

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

{{-- @if(!empty($user) && $user->id==$item->id )
	<div class="guided-overflow-wrapper">
		<div class="guided-tour-part guided-overflow-top"></div>
		<div class="guided-tour-part guided-overflow-right">
			<img class="top" src="{{ url('img-trp/border-tooltips.png') }}">
			<img class="bottom" src="{{ url('img-trp/border-tooltips.png') }}">
		</div>
		<div class="guided-tour-part guided-overflow-left">
			<img class="top" src="{{ url('img-trp/border-tooltips.png') }}">
			<img class="bottom" src="{{ url('img-trp/border-tooltips.png') }}">
		</div>
		<div class="guided-tour-part guided-overflow-bottom"></div>

		<div class="bubble-guided-tour">
			<div class="cap"></div>
			<h4>{{ trans('trp.guided-tour.complete-profile') }}</h4>
			<div class="flex guided-info-wrap">
				<div class="guided-icon">
					<img src="{{ url('img-trp/edit-profile.svg') }}"/>
				</div>
				<p>{{ trans('trp.guided-tour.complete-profile-info') }}</p>
			</div>

			<div class="flex guided-buttons">
				<div class="steps">{{ trans('trp.guided-tour.steps') }}: <span id="cur-step">1</span><span>/</span><span id="all-steps">7</span></div>
				<a href="javascript:;" class="skip-step">{{ trans('trp.guided-tour.skip-steps') }}</a>
				<a href="javascript:;" class="skip-reviews-step with-layout button" style="display: none;">{{ trans('trp.guided-tour.ok-button') }}</a>
			</div>
		</div>
	</div>
@endif --}}

<div class="black-overflow" style="display: none;"></div>

<div class="gray-line"></div>

<div class="container">

	<div class="profile-wrapper">
		<div class="profile-info-container">
			<div class="profile-info flex">
				<div class="avatar-wrapper">
					<img class="avatar" src="{{ $item->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [
						'name' => $item->getNames(), 
						'location' => $item->getLocation()
					]) }}" width="150" height="150">
					{{-- @if($item->is_clinic && $item->branches->isNotEmpty() && $item->id == $item->mainBranchClinic->id) --}}
						<div class="main-clinic mont">{!! nl2br(trans('trp.common.primary-account')) !!}</div>
					{{-- @endif  --}}

					{{-- @if(!empty($user) && $user->is_clinic && $item->is_clinic && $user->branches->isNotEmpty() && in_array($item->id, $user->branches->pluck('branch_clinic_id')->toArray()))
						<a href="javascript:;" class="p clinic-branches login-as" login-url="{{ getLangUrl('loginas') }}" branch-id="{{ $item->id }}">
							<div class="img">
								<img src="{{ url('img-trp/swith-account-blue.svg') }}"/>
							</div>
							{!! nl2br(trans('trp.page.user.branch.switch-account')) !!}
							{!! csrf_field() !!}
						</a>
					@else
						@if($item->branches->isNotEmpty()) --}}
							<a class="clinic-branches mont" href="{{ getLangUrl('branches/'.$item->slug) }}">
								<img src="{{ url('img-trp/branches.svg') }}" width="24"/>
								{{-- {!! nl2br(trans('trp.page.user.branch.see-branches')) !!} --}}
								Check branches
							</a>
						{{-- @endif
					@endif --}}
					
					@if(false)
						<div class="visits-wrapper">
							<span class="mont">
								<img class="visit-icon" src="{{ url('img-trp/visits/on_site.svg') }}" width="25"/>
								On-site visits
							</span>
							<span class="mont">
								<img class="visit-icon" src="{{ url('img-trp/visits/virtual.svg') }}" width="25"/>
								Virtual visits
								<img class="visit-info tooltip-text" src="{{ url('img-trp/info-dark-gray.png') }}" text="Virtual visits are a great way to talk with your dentist using your phone or PC. This dental provider uses a third-party video service. Click on the button to schedule a video appointment.">
							</span>
						</div>
					@endif
				</div>
				<div class="profile-details">
					<div class="partner-wrapper flex flex-center {{ $item->is_partner ? 'space-between' : 'flex-text-end' }}">
						@if($item->is_partner)
							<div class="partner">
								<img src="{{ url('img-trp/mini-logo-white.svg') }}">
								Dentacoin Partner
							</div>
						@endif

						<a href="javascript:;" class="share-button" data-popup="popup-share">
							<img src="{{ url('img-trp/share-arrow-gray.svg') }}">
							{!! nl2br(trans('trp.common.share')) !!}
						</a>
					</div>

					{{-- edit title & name --}}
					@if(!empty($user) && $item->id==$user->id)
						<div class="edit-field">
							<h1 class="mont edited-field" style="display: inline-block;">
								{{ $item->getNames() }}
							</h1>

							<a class="edit-field-button">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							<div class="edit-wrapper edit-name">
								<div class="flex flex-center">

									@if(!$user->is_clinic)
										<div class="flex flex-column col">
											{{ Form::select( 'title' , config('titles') , $user->title , array('class' => 'input') ) }}

									@endif
									
									<input 
									type="text" 
									name="name" 
									class="input dentist-name mont" 
									placeholder="{!! nl2br(trans('trp.page.user.name')) !!}" 
									value="{{ $user->name }}"
									>

									@if(!$user->is_clinic)
										</div>
									@endif
									<a href="javascript:;" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</a>
								</div>
							</div>
						</div>
					@else
						<h1 class="mont">{{ $item->getNames() }}</h1>
					@endif

					{{-- edit phone --}}
					@if(!empty($user) && $item->id==$user->id)
						<div class="edit-field">
							<h3 class="edited-field alternative-name" style="display: inline-block;">
								{{ $item->name_alternative ?? 'edit your alternative name' }}
							</h3>

							<a class="edit-field-button">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							<div class="edit-wrapper">
								<div class="flex flex-center">
									<input 
									type="text" 
									name="name_alternative" 
									class="input input-alternative" 
									placeholder="{!! nl2br(trans('trp.page.user.name_alterantive')) !!}" 
									value="{{ $user->name_alternative }}"
									>
									<a href="javascript:;" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</a>
								</div>
							</div>
						</div>
					@else
						@if( $item->name_alternative )
							<h3 class="alternative-name">({{ $item->name_alternative }})</h3>
						@endif
					@endif

					@if($workplace || $workingTime)
						<div class="flex flex-center workplace-wrapper">
							<div class="col">
								@if($workplace)
									<div class="workplace">
										{!! $workplace !!}
									</div>
								@endif
							</div>
							<div>
								@if($workingTime)
									@if(str_contains($workingTime, 'Open now'))
										<div class="working-time open">
											<img src="{{ url('img-trp/clock-blue.svg') }}">
											Open now

											<div class="work-hours">
												@foreach($week_days as $w => $week_day)
													<div class="flex {{ date('w') == $w ? 'active' : '' }}">
														<p class="month">
															{{ $week_day }}
														</p>
														@if(array_key_exists($w, $item->work_hours))
															<p>
																@foreach($item->work_hours[$w] as $k => $work_hours)
																	{{ $work_hours }}h {!! $loop->last ? '' : ' - ' !!}
																@endforeach
															</p>
														@else
															<p>Closed</p>
														@endif
													</div>
												@endforeach
											</div>

											@if(!empty($user) && $item->id==$user->id)
												<a class="edit-field-button">
													<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
												</a>
											@endif
										</div>
									@else
										<div class="working-time closed">
											<img src="{{ url('img-trp/clock-red.svg') }}"/>
											Closed now

											@if(!empty($user) && $item->id==$user->id)
												<a class="edit-field-button">
													<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
												</a>
											@endif
										</div>
									@endif
								@endif
							</div>
						</div>
					@endif

					<p class="dentist-address">{{ $item->getLocation() }}</p>
					
					{{-- edit address --}}
					@if(!empty($user) && $item->id==$user->id)
						<div class="edit-field">
							<p class="dentist-address edited-field" style="display: inline-block;">
								{{ $item->address ?? 'edit your address' }}
							</p>

							<a class="edit-field-button">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							<div class="edit-wrapper address-suggester-wrapper-input">
								<div class="flex flex-center">
									<input 
									type="text" 
									name="address" 
									class="input address-suggester-input" 
									autocomplete="off" 
									placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}" 
									value="{{ $user->address }}"
									>
									<a href="javascript:;" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</a>
								</div>
								<div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 100px; display: none; margin: 10px 0px;">
								</div>
								<div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px;">
									{!! nl2br(trans('trp.common.check-address')) !!}
								</div>
								<div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
									{!! nl2br(trans('trp.common.invalid-address')) !!}
								</div>
								<div class="alert alert-warning different-country-hint mobile" style="display: none; margin: -10px 0px 10px;">
									{!! nl2br(trans('trp.page.user.invalid-country')) !!}
								</div>
							</div>
						</div>
					@else
						@if($item->address)
							<div class="dentist-address">
								{{ $item->address }}
							</div>
						@endif
					@endif
					
					{{-- edit phone --}}
					@if(!empty($user) && $item->id==$user->id)
						<div class="edit-field">
							<p class="edited-field" style="display: inline-block;">
								{{ $item->getFormattedPhone() ?? 'edit your phone number' }}
							</p>

							<a class="edit-field-button">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							<div class="edit-wrapper">
								<div class="flex flex-center phone-widget">
									<span class="phone-code-holder">{{ $user->country_id ? '+'.$user->country->phone_code : '' }}</span>
									<input 
									type="tel" 
									name="phone" 
									class="input" 
									placeholder="{!! nl2br(trans('trp.page.user.phone')) !!}" 
									value="{{ $user->phone }}"
									>
									<a href="javascript:;" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</a>
								</div>
							</div>
						</div>
					@else
						@if( $item->phone )
							<p>
								<a href="tel:{{ $item->getFormattedPhone(true) }}">
									{{ $item->getFormattedPhone() }}
								</a>
							</p>
						@endif
					@endif

					{{-- edit website --}}
					@if(!empty($user) && $item->id==$user->id)
						<div class="edit-field">
							<p class="edited-field" style="display: inline-block;">
								<a class="blue-href" href="{{ $item->getWebsiteUrl() }}" target="_blank">
									{{ $item->website }}
								</a>
							</p>

							<a class="edit-field-button">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							<div class="edit-wrapper">
								<div class="flex">
									<input 
									type="text" 
									name="website" 
									class="input" 
									placeholder="{!! nl2br(trans('trp.page.user.website')) !!}" 
									value="{{ $user->website }}"
									>
									<a href="javascript:;" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</a>
								</div>
							</div>
						</div>
					@else
						@if( $item->website )
							<p>
								<a class="blue-href" href="{{ $item->getWebsiteUrl() }}" target="_blank">
									{{ $item->website }}
								</a>
							</p>
						@endif
					@endif

					{{-- edit socials --}}
					
					<div class="socials-wrapper">
						@if( $item->socials || !empty($item->email))
							<div class="socials">

								@if(!empty($item->email))
									<a class="social" href="mailto:{{ $item->email_public ? $item->email_public : $item->email }}">
										<img src="{{ url('img-trp/social-network/email.svg') }}" height="26"/>
									</a>
								@else
									@if($item->branches->isNotEmpty())
										<a class="social" href="mailto:{{ $item->email_public ? $item->email_public : ($item->mainBranchClinic->email_public ?? $item->mainBranchClinic->email) }}">
											<img src="{{ url('img-trp/social-network/email.svg') }}" height="26"/>
										</a>
									@endif
								@endif
								
								@if( $item->socials)
									@foreach($item->socials as $k => $v)
										<a class="social" href="{{ $v }}" target="_blank">
											<img src="{{ url('img-trp/social-network/'.$k.'.svg') }}" height="26"/>
										</a>
									@endforeach
								@endif

								@if(!empty($user) && $item->id==$user->id)
									<a class="edit-field-button">
										<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
									</a>
								@endif
							</div>
						@endif

						@if(!empty($user) && $item->id==$user->id)
							<div class="edit-field">
								<div style="flex:1;">
									<div class="email-wrapper">
										<div class="flex email-wrap">
											<div class="col social-networks">
												<a href="javascript:;">
													<img src="{{ url('img-trp/social-network/email.svg') }}"/>
												</a>
											</div>
											<div class="col">
												<input type="text" 
												name="email_public" 
												class="input" 
												value="{{ !empty($user->email_public) ? $user->email_public : $user->email }}" 
												placeholder="{!! nl2br(trans('trp.page.user.user-public-email')) !!}" 
												maxlength="100" 
												{!! !empty($user->email_public) ? '' : 'disabled' !!}>
											</div>
										</div>
										<label class="checkbox-label label-public-email {!! !empty($user->email_public) ? '' : 'active' !!}" for="current-email">
											<input 
											type="checkbox" 
											class="special-checkbox" 
											id="current-email" 
											cur-email="{{ $user->email }}" 
											name="current-email" 
											value="{!! !empty($user->email_public) ? '0' : '1' !!}" 
											{!! !empty($user->email_public) ? '' : 'checked' !!}
											
											>
											<div class="checkbox-square">✓</div>
											{{-- {!! nl2br(trans('trp.page.user.user-registration-email')) !!} --}}
											Use my registration email as a public email
										</label>			    	
									</div>
									<div class="social-wrapper dont-count" guided-action="socials" style="padding: 5px; margin: -5px;">
										@if(!empty($user->socials))
											@foreach($user->socials as $k => $v)
												<div class="flex social-wrap">
													<div class="col social-networks">
														<a href="javascript:;" class="current-social" cur-type="{{ $k }}">
															<img src="{{ url('img-trp/social-network/'.config('trp.social_network')[$k].'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
														</a>
													</div>
													<div class="col">
														<input type="text" name="socials[{{ $k }}]" class="input social-link-input" value="{{ $v }}" maxlength="300">
													</div>
												</div>
											@endforeach
										@else
											<div class="flex social-wrap">
												<div class="col social-networks">
													<a href="javascript:;" class="current-social" cur-type="{{ array_values(config('trp.social_network'))[0] }}">
														<img src="{{ url('img-trp/social-network/'.array_values(config('trp.social_network'))[0].'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
													</a>
													<div class="social-dropdown"> 
														@foreach(config('trp.social_network') as $key => $sn)
															<a href="javascript:;">
																<img src="{{ url('img-trp/social-network/'.$sn.'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
															</a>
														@endforeach
													</div>
												</div>
												<div class="col">
													<input type="text" name="socials[{{ key(config('trp.social_network')) }}]" class="input social-link-input" maxlength="300">
												</div>
											</div>
										@endif
										
										@if(count($user->socials) != count(config('trp.social_network')))
											<a href="javascript:;" class="add-social-profile">
												+ Add another social link
												{{-- {!! nl2br(trans('trp.page.user.add-social-profile')) !!} --}}
											</a>
										@endif
									</div>
								</div>
								
								<a href="javascript:;" class="save-field">
									<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
								</a>
							</div>
						@endif
					</div>
					
					@if(false)
						<div class="announcement-wrapper">
							<h4>
								<img src="{{ url('img-trp/announcement.svg') }}" width="16"/>New office safety precautions 
								@if(!empty($user) && $item->id==$user->id)
									<a class="edit-field-button">
										<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
									</a>
								@endif
							</h4>
							<p class="announcement-title">
								Message from the office of {{ $item->getNames() }}
							</p>
							<p>
								<span 
									class="announcement-description" 
									short-text='"{{ substr('We are taking all precautions to protect our patients and staff at this time. We require patients and staff to wear masks in the office. We will be checking temperatures when patients come in. Family members accompanying the patient are advised to wait outside or in their car. We are also screening patients prior to their visit. Patients with respiratory symptoms are advised to schedule a video visit with Dr K.', 0, 150) }}.."' 
									long-text='"{{ 'We are taking all precautions to protect our patients and staff at this time. We require patients and staff to wear masks in the office. We will be checking temperatures when patients come in. Family members accompanying the patient are advised to wait outside or in their car. We are also screening patients prior to their visit. Patients with respiratory symptoms are advised to schedule a video visit with Dr K.' }}"'
								>
									"{{ substr('We are taking all precautions to protect our patients and staff at this time. We require patients and staff to wear masks in the office. We will be checking temperatures when patients come in. Family members accompanying the patient are advised to wait outside or in their car. We are also screening patients prior to their visit. Patients with respiratory symptoms are advised to schedule a video visit with Dr K.', 0, 150) }}.."
								</span>
								<a href="javascript:;" class="show-full-announcement" short-text="show more" long-text="show less">show more</a>
							</p>
						</div>
					@endif
				</div>
			</div>
		</div>
		<div class="profile-rating">

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

			@if(!empty($user) && $user->id==$item->id)
			
				@if( $regularReviewsCount )
					<a href="javascript:;" class="white-button add-widget-button" data-popup-logged="popup-widget" reviews-guided-action="add">
						{{-- {!! nl2br(trans('trp.page.user.widget')) !!} --}}
						Add to website
					</a>
				@endif
				<div style="padding: 5px;" guided-action="invite" class="dont-count">
					<a href="javascript:;" class="blue-button" data-popup-logged="popup-invite">
						{!! nl2br(trans('trp.page.user.invite')) !!}
					</a>
				</div>
			@elseif( empty($user) || !$user->is_dentist )
				<a href="javascript:;" class="blue-button" data-popup-logged="submit-review-popup">
					{{-- {!! nl2br(trans('trp.page.user.submit-review')) !!} --}}
					Write a review
				</a>
				@if(empty($is_trusted) && !$has_asked_dentist)
					<a href="javascript:;" class="blue-button button-inner-white button-ask" data-popup-logged="popup-ask-dentist">
						{!! nl2br(trans('trp.page.user.request-invite')) !!}
					</a>
				@endif
			@endif		
		</div>
	</div>

	{{-- <div class="information flex">
		<a href="javascript:;" class="share-button" data-popup="popup-share">
			<img src="{{ url('img-trp/share.svg') }}" width="16" height="18">
			{!! nl2br(trans('trp.common.share')) !!}
		</a>
		@if(!empty($user) && $user->id!=$item->id && !empty($writes_review))
			<a href="javascript:;" class="recommend-button" data-popup="recommend-dentist">
				<img src="{{ url('img-trp/thumb-up.svg') }}">
				{{ trans('trp.page.user.recommend') }}
			</a>
		@endif
		
    	<div class="profile-info col">

			@if(!empty($user) && $user->id==$item->id)
				{!! Form::open([
					'method' => 'post', 
					'class' => 'edit-profile clearfix', 
					'style' => 'display: none;', 
					'url' => getLangUrl('profile/info') 
				]) !!}
					{!! csrf_field() !!}
					
					<div class="upload-image-wrapper">
						<label for="add-avatar" class="image-label" {!! $user->hasimage ? 'style="background-image: url('.$user->getImageUrl(true).')"' : '' !!}>
							<div class="centered-hack">
								<img src="{{ url('img/camera.svg') }}"/>
								<p>
									@if( !$user->hasimage )
										{!! nl2br(trans('trp.page.user.add-photo')) !!}
									@else
										{!! nl2br(trans('trp.page.user.change-photo')) !!}
									@endif
								</p>
							</div>
							<div class="loader">
								<i></i>
							</div>
							<input type="file" name="image" id="add-avatar" class="input-croppie" upload-url="{{ getLangUrl('register/upload') }}" accept="image/png,image/jpeg,image/jpg">
							<input type="hidden" name="avatar" class="avatar">
						</label>
	
						<div class="cropper-container"></div>
						<div class="avatar-name-wrapper">
							<span class="avatar-name"></span>
							<button class="destroy-croppie" type="button">×</button>
						</div>

						<div class="max-size-label">
							<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="width-100">
								<path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z" class=""></path>
							</svg>
							{{ trans('trp.popup.add-branch.image-max-size') }}
						</div>
						<div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
					</div>

					<div class="media-right address-suggester-wrapper-input">
						@if(!$user->is_clinic)
							<div class="flex">
								{{ Form::select( 'title' , config('titles') , $user->title , array('class' => 'input') ) }}
								<input type="text" name="name" class="input dentist-name" placeholder="{!! nl2br(trans('trp.page.user.name')) !!}" value="{{ $user->name }}">
							</div>
						@else
							<input type="text" name="name" class="input dentist-name" placeholder="{!! nl2br(trans('trp.page.user.name')) !!}" value="{{ $user->name }}">
						@endif
						<input type="text" name="name_alternative" class="input" placeholder="{!! nl2br(trans('trp.page.user.name_alterantive')) !!}" value="{{ $user->name_alternative }}">

						<div class="alert alert-warning mobile ip-country" style="display: none;">
	                    	{!! nl2br(trans('trp.common.different-ip')) !!}
	                    </div>	

	                    @if(!empty($user->country_id))
		                    <div class="user-country" style="position: relative;">
		                    	<div class="covering" style="position: absolute;top: 0px;bottom: 0px;left: 0px;right: 0px; z-index: 1000;background: transparent;"></div>
		                    	<select class="input country-select country-dropdown" name="country_id" disabled="disabled">
			                		<option value="{{ $countriesArray[$user->country_id] }}" code="{{ \App\Models\Country::find($user->country_id)->code }}" selected="selected" >{{ $countriesArray[$user->country_id] }}</option>
			                	</select>
			                </div>		                	
		                	<div class="alert alert-warning mobile" style="margin: 10px 0px; display: none;">
	                        	{!! nl2br(trans('trp.page.user.uneditable-country')) !!}
	                        </div>
	                    @else
		                	<select class="input country-select country-dropdown" name="country_id" {!! !empty($country_id) ? 'disabled="disabled"' : '' !!} real-country="{{ !empty($country_id) ? $country_id : '' }}">
		                		@foreach($countries as $country)
		                			<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $user->country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
		                		@endforeach
		                	</select>
		                @endif
	                	<div>
					    	<input type="text" name="address" class="input address-suggester-input" autocomplete="off" placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}" value="{{ $user->address }}">
	                        <div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 100px; display: none; margin: 10px 0px;">
	                        </div>
	                        <div class="alert alert-info geoip-confirmation mobile" style="display: none; margin: 10px 0px;">
	                        	{!! nl2br(trans('trp.common.check-address')) !!}
	                        </div>
	                        <div class="alert alert-warning geoip-hint mobile" style="display: none; margin: 10px 0px;">
	                        	{!! nl2br(trans('trp.common.invalid-address')) !!}
	                        </div>
					        <div class="alert alert-warning different-country-hint mobile" style="display: none; margin: -10px 0px 10px;">
					        	{!! nl2br(trans('trp.page.user.invalid-country')) !!}
					        </div>
	                    </div>
				    	<input type="text" name="open" class="input dont-count" placeholder="{!! nl2br(trans('trp.page.user.open-hours')) !!}" value="{{ strip_tags($user->getWorkHoursText()) }}" autocomplete="off" data-popup-logged="popup-wokring-time" guided-action="work_hours">
				    	<div class="flex phone-widget">
					    	<span class="phone-code-holder">{{ $user->country_id ? '+'.$user->country->phone_code : '' }}</span>
							<input type="tel" name="phone" class="input" placeholder="{!! nl2br(trans('trp.page.user.phone')) !!}" value="{{ $user->phone }}">
						</div>
				    	<input type="text" name="website" class="input" placeholder="{!! nl2br(trans('trp.page.user.website')) !!}" value="{{ $user->website }}">
				    	<input type="hidden" name="email" value="{{ $user->email }}">
				    	@if(!$user->is_clinic)
					    	<input type="text" name="open" class="input wokrplace-input" placeholder="{!! nl2br(trans('trp.page.user.my-workplace')) !!}" value="{{ strip_tags($user->getWorkplaceText(true)) }}" autocomplete="off" data-popup-logged="popup-wokrplace">
				    	@endif
				    	<div class="email-wrapper">
					    	<div class="flex flexed-wrap email-wrap">
					    		<div class="col social-networks">
					    			<a href="javascript:;" class="current-social">
				    					<img src="{{ url('img/envelope.svg') }}"/>
				    				</a>
					    		</div>
					    		<div class="col">
					    			<input type="text" name="email_public" class="input social-link-input" value="{{ !empty($user->email_public) ? $user->email_public : $user->email }}" placeholder="{!! nl2br(trans('trp.page.user.user-public-email')) !!}" maxlength="100" {!! !empty($user->email_public) ? '' : 'disabled' !!}>
					    		</div>
					    	</div>
					    	<label class="checkbox-label label-public-email {!! !empty($user->email_public) ? '' : 'active' !!}" for="current-email"">
								<input type="checkbox" class="special-checkbox" id="current-email" cur-email="{{ $user->email }}" name="current-email" value="{!! !empty($user->email_public) ? '0' : '1' !!}" {!! !empty($user->email_public) ? '' : 'checked' !!} >
								<div class="checkbox-square">✓</div>
								{!! nl2br(trans('trp.page.user.user-registration-email')) !!}
							</label>			    	
					    </div>
					    <div class="social-wrapper dont-count" guided-action="socials" style="padding: 5px; margin: -5px;">
						    <div class="s-wrap">
						    	@if(!empty($user->socials))
						    		@foreach($user->socials as $k => $v)
								    	<div class="flex social-wrap flexed-wrap">
								    		<div class="col social-networks">
								    			<a href="javascript:;" class="current-social" cur-type="{{ $k }}">
							    					<img src="{{ url('img/social-network/'.config('trp.social_network')[$k].'.svg') }}" src-attr="{{ url('img/social-network/') }}"/>
							    				</a>
								    			<div class="social-dropdown"> 
									    			@foreach(config('trp.social_network') as $key => $sn)
									    				<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! isset($user->socials[$key]) ? 'inactive' : ''; !!}">
															<img class="{{ $sn }}" class-attr="{{ $sn }}" src="{{ url('img/social-network/'.$sn.'.svg') }}" src-attr="{{ url('img/social-network/') }}"/>
									    				</a>
									    			@endforeach
									    		</div>
								    		</div>
								    		<div class="col">
								    			<input type="text" name="socials[{{ $k }}]" class="input social-link-input" value="{{ $v }}" maxlength="300">
								    		</div>
								    	</div>
								    @endforeach
							    @else
							    	<div class="flex social-wrap flexed-wrap">
							    		<div class="col social-networks">
							    			<a href="javascript:;" class="current-social" cur-type="{{ array_values(config('trp.social_network'))[0] }}">
												<img src="{{ url('img/social-network/'.array_values(config('trp.social_network'))[0].'.svg') }}" src-attr="{{ url('img/social-network/') }}"/>
						    				</a>
							    			<div class="social-dropdown"> 
								    			@foreach(config('trp.social_network') as $key => $sn)
								    				<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! $loop->first ? 'inactive' : '' !!}">
								    					<img src="{{ url('img/social-network/'.$sn.'.svg') }}" src-attr="{{ url('img/social-network/') }}"/>
								    				</a>
								    			@endforeach
								    		</div>
							    		</div>
							    		<div class="col">
							    			<input type="text" name="socials[{{ key(config('trp.social_network')) }}]" class="input social-link-input" maxlength="300">
							    		</div>
							    	</div>
							    @endif
							</div>
						    
						    @if(empty($user->socials) || (!empty($user->socials) && (count($user->socials) != count(config('trp.social_network')))))
					    		<a href="javascript:;" class="add-social-profile">{!! nl2br(trans('trp.page.user.add-social-profile')) !!}</a>
					    	@endif
					    </div>
					</div>
					<div class="clearfix">
						<div class="clear flex flex-bottom" style="justify-content: flex-end;">
							<div class="edit-buttons">
								<div style="padding: 5px;" guided-action="save">
									<button class="button" type="submit">
										{!! nl2br(trans('trp.page.user.save')) !!}
									</button>
								</div>
								<a href="javascript:;" class="cancel-edit open-edit">
									{!! nl2br(trans('trp.page.user.cancel')) !!}
								</a>
							</div>
						</div>
					</div>
					<div class="edit-error alert alert-warning" style="display: none;">
					</div>
					<input type="hidden" name="json" value="1">
				{!! Form::close() !!}
			@endif

			@if(in_array($item->status, config('dentist-statuses.unclaimed')))
				<div class="invited-dentist">{!! nl2br(trans('trp.page.user.added-by-patient')) !!}</div>
			@endif

    		<div class="view-profile clearfix">
				<div class="avatar">
					<img src="{{ $item->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [ 'name' => $item->getNames(), 'location' => $item->getLocation()  ]) }}" width="130" height="130">
				</div>
				<div class="media-right">
					<div class="phone-styles">
						<h3>
							{{ $item->getNames() }}
						</h3>
						@if( $item->name_alternative )
							<p class="alternative-name">({{ $item->name_alternative }})</p>
						@endif

						<span class="type">
							@if($item->is_partner)
								<span> {!! nl2br(trans('trp.page.user.partner')) !!}</span> 
							@endif
							{{ $item->is_clinic ? trans('trp.page.user.clinic') : trans('trp.page.user.dentist') }}
						</span>

						@if(!empty($user) && $user->id==$item->id)
							<a class="edit-button open-edit" guided-action="edit" href="javascript:;">
								<img src="{{ url('img-trp/penci-bluel.png') }}">
								{!! nl2br(trans('trp.page.user.edit-profile')) !!}
							</a>
						@endif
						@if(empty($user) && in_array($item->status, config('dentist-statuses.unclaimed')))
							<a class="claim-button" href="javascript:;" data-popup="claim-popup">
								{{ trans('trp.common.claim-practice') }}
							</a>
						@endif

						<div class="ratings average">
							<div class="stars">
								<div class="bar" style="width: {{ $item->avg_rating/5*100 }}%;">
								</div>
							</div>
						</div>
			
						<div class="rating">
							({{ trans('trp.common.reviews-count', [ 'count' => intval($item->ratings)]) }})
						</div>

						@if(!empty($user) && $user->id==$item->id)
							<div style="padding: 5px;" guided-action="invite" class="dont-count">
								<a href="javascript:;" class="button" data-popup-logged="popup-invite">
									{!! nl2br(trans('trp.page.user.invite')) !!}
								</a>
							</div>
							@if( $regularReviewsCount )
								<a href="javascript:;" class="button button-inner-white add-widget-button" data-popup-logged="popup-widget" reviews-guided-action="add" style="text-transform: initial;">
									{!! nl2br(trans('trp.page.user.widget')) !!}
								</a>
							@endif
						@elseif( empty($user) || !$user->is_dentist )
							<a href="javascript:;" class="button" data-popup-logged="submit-review-popup">
								{!! nl2br(trans('trp.page.user.submit-review')) !!}
							</a>
							@if(empty($is_trusted) && !$has_asked_dentist)
								<a href="javascript:;" class="button button-inner-white button-ask" data-popup-logged="popup-ask-dentist">
									{!! nl2br(trans('trp.page.user.request-invite')) !!}
								</a>
							@endif
						@endif		
					</div>
					<div class="profile-details">
						<a href="javascript:;" class="p scroll-to-map" map-tooltip="{{ $item->address ? $item->address.', ' : '' }} {{ $item->country->name }} ">
							<div class="img">
								<img class="black-filter" src="{{ url('img-trp/map-pin.png') }}" width="11" height="14">
							</div>
							{{ $item->getLocation() }}
							<!-- <span class="gray-text">(2 km away)</span> -->
						</a>
						@if( $time = $item->getWorkHoursText() )
							<div class="p">
								<div class="img">
									<img class="black-filter" src="{{ url('img-trp/open.png') }}" width="13" height="14">
								</div>
								{!! $time !!}
							</div>
						@endif
						@if( $item->phone )
							<a class="p" href="tel:{{ $item->getFormattedPhone(true) }}">
								<div class="img">
									<img class="black-filter" src="{{ url('img-trp/phone.png') }}" width="14" height="14">
								</div>
								{{ $item->getFormattedPhone() }}
							</a>
						@endif
						@if( $item->website )
							<a class="p website-p" href="{{ $item->getWebsiteUrl() }}" target="_blank">
								<div class="img">
									<img class="black-filter" src="{{ url('img-trp/website-icon.svg') }}" width="14" height="14">
								</div>
								<span>
									{{ $item->website }}
								</span>
							</a>
						@endif
						@if($item->top_dentist_month)
							<div class="top-dentist">
								<img src="{{ url('img-trp/top-dentist.png') }}" width="16" height="16">
								<span>
									{!! trans('trp.common.top-dentist') !!}
								</span>
							</div>
						@endif
						@if( $workplace = $item->getWorkplaceText( !empty($user) && $user->id==$item->id ) )
							<div class="p workplace-p">
								<div class="img" style="min-width: 25px;">
									<img class="black-filter" src="{{ url('img-trp/clinic.png') }}">
								</div>
								<div>
									{!! $workplace !!}
								</div>
							</div>
						@endif
						<div class="p profile-socials">
							@if(!empty($item->email))
								<a class="social" href="mailto:{{ $item->email_public ? $item->email_public : $item->email }}">
									<img src="{{ url('img/envelope.svg') }}" width="15" height="15"/>
								</a>
							@else
								@if($item->branches->isNotEmpty())
									<a class="social" href="mailto:{{ $item->email_public ? $item->email_public : ($item->mainBranchClinic->email_public ?? $item->mainBranchClinic->email) }}">
										<img src="{{ url('img/envelope.svg') }}" width="15" height="15"/>
									</a>
								@endif
							@endif
							@if( $item->socials )
								@foreach($item->socials as $k => $v)
									<a class="social" href="{{ $v }}" target="_blank">
										<img src="{{ url('img/social-network/'.$k.'.svg') }}" width="15" height="15"/>
									</a>
								@endforeach
							@endif
						</div>
					</div>
				</div>
			</div>
			@if(!empty($user) && $user->id==$item->id)
				<a class="edit-button open-edit" guided-action="edit" href="javascript:;">
					<img src="{{ url('img-trp/penci-bluel.png') }}">
					{!! nl2br(trans('trp.page.user.edit-profile')) !!}
				</a>
			@endif
			@if(empty($user) && in_array($item->status, config('dentist-statuses.unclaimed')))
				<a class="claim-button" href="javascript:;" data-popup="claim-popup">
					{{ trans('trp.common.claim-practice') }}
				</a>
			@endif
		</div>

		<div class="profile-rating col tac">

			<div class="ratings big">
				<div class="stars">
					<div class="bar" style="width: {{ $item->avg_rating/5*100 }}%;">
					</div>
				</div>
			</div>

			<div class="rating">
				({{ trans('trp.page.user.based-reviews-count', [ 'count' => intval($item->ratings)]) }})
			</div>

			@if(!empty($user) && $user->id==$item->id)
				<div style="padding: 5px;display: inline-block;" guided-action="invite" class="dont-count">
					<a href="javascript:;" class="button" data-popup-logged="popup-invite">
						{!! nl2br(trans('trp.page.user.invite')) !!}
					</a>
				</div>
				@if( $regularReviewsCount )
					<div style="padding: 5px;display: inline-block;" reviews-guided-action="add">
						<a href="javascript:;" class="button button-inner-white add-widget-button" data-popup-logged="popup-widget" style="text-transform: initial;">
							{!! nl2br(trans('trp.page.user.widget')) !!}
						</a>
					</div>
				@endif
			@elseif( empty($user) || !$user->is_dentist )
				<a href="javascript:;" class="button" data-popup-logged="submit-review-popup">
					{!! nl2br(trans('trp.page.user.submit-review')) !!}
				</a>
				@if(empty($is_trusted) && !$has_asked_dentist)
					<a href="javascript:;" class="button button-inner-white button-ask" data-popup-logged="popup-ask-dentist">
						{!! nl2br(trans('trp.page.user.request-invite')) !!}
					</a>
				@endif
			@endif							

		</div>
    </div> --}}
</div>

<div class="tab-titles">
	<div class="container">
		@if($showAboutSection)
			<a class="tab-title active" data-tab="about" href="javascript:;">
				{{-- {!! nl2br(trans('trp.page.user.about')) !!} --}}
				About
			</a>
		@endif
		@if($showTeamSection)
			<a class="tab-title" data-tab="team" href="javascript:;">
				Team
			</a>
		@endif
		@if( $regularReviewsCount || $videoReviewsCount )
			<a class="tab-title" data-tab="reviews" href="javascript:;">
				{!! nl2br(trans('trp.page.user.reviews')) !!}
			</a>
		@endif
		@if( $showLocationsSection )
			<a class="tab-title" data-tab="locations" href="javascript:;">
				Locations
			</a>
		@endif

		{{-- @if(!empty($user) && $user->id==$item->id && ($hasPatientInvites || $hasPatientAsks))
			<a class="tab-title {!! $patient_asks ? 'force-active' : '' !!}" data-tab="asks" href="javascript:;">
				{!! nl2br(trans('trp.page.user.my-patients')) !!}

				<span class="{!! $patient_asks ? 'active' : ''  !!}"></span>
			</a>
		@endif --}}

		<a class="tab-title" data-tab="more-info" href="javascript:;">
			More info
		</a>
	</div>
</div>

<div class="tab-sections">
	<div class="container">

		@if($showAboutSection)
			<div class="tab-container" id="about">
				<h2 class="mont">
					About
					{{-- {!! nl2br(trans('trp.page.user.about-who',[
						'name' => $item->getNames()
					])) !!} --}}
				</h2>

				<div class="tab-inner-section">
					{{-- @if($item->top_dentist_month)
						<div class="top-dentist">
							<img src="{{ url('img-trp/top-dentist.png') }}">
							<span>
								{!! trans('trp.common.top-dentist') !!}: {{ $item->getLastTopDentistBadge() }}
							</span>
						</div>
					@endif --}}
					@if($item->categories->isNotEmpty() || (!empty($user) && $item->id==$user->id))
						<h3>
							Specialities
						</h3>
						<div class="specializations" role="presenter">
							@foreach($item->categories as $specialization)
								<span class="specialization">
									{{ trans('trp.categories.'.config('categories.'.$specialization->category_id)) }}
								</span>
							@endforeach
							{{-- <span class="value-here" empty-value="{{ nl2br(trans('trp.page.user.specialty-empty')) }}">
								{{ $item->categories->isNotEmpty() ? implode(', ', $item->parseCategories($categories)) : nl2br(trans('trp.page.user.specialty-empty')) }}
							</span> --}}
							@if(!empty($user) && $item->id==$user->id)
								<a>
									<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
								</a>
							@endif
						</div>
						@if(!empty($user) && $item->id==$user->id)
							<div class="specialization" role="editor" style="display: none;">
								{{ Form::open([
									'class' => 'edit-description', 
									'method' => 'post', 
									'url' => getLangUrl('profile/info') 
								]) }}
									{!! csrf_field() !!}
									@foreach($categories as $k => $v)
										<label class="checkbox-label {!! in_array($loop->index, $user->categories->pluck('category_id')->toArray()) ? 'active' : '' !!}" for="checkbox-{{ $k }}" >
											<input type="checkbox" class="special-checkbox" id="checkbox-{{ $k }}" name="specialization[]" value="{{ $loop->index }}" {!! in_array($loop->index, $user->categories->pluck('category_id')->toArray()) ? 'checked="checked"' : '' !!}>
											<div class="checkbox-square">✓</div>
											{{ $v }}
										</label>
									@endforeach
									<br/>
									<input type="hidden" name="field" value="specialization" />
									<input type="hidden" name="json" value="1" />
									<button type="submit" class="button">
										{!! nl2br(trans('trp.page.user.save')) !!}
									</button>
									<div class="alert alert-warning" style="display: none;">
									</div>
								{!! Form::close() !!}
							</div>
						@endif
					@endif
				</div>
				
				<div class="tab-inner-section">
					@if($item->description || (!empty($user) && $item->id==$user->id) )
						<h3>
							Introduction
						</h3>
						<div class="about-content" role="presenter">
							<span class="value-here description" empty-value="{{ nl2br(trans('trp.page.user.description-empty')) }}">
								{!! $item->description ? nl2br($item->description) : nl2br(trans('trp.page.user.description-empty')) !!}
							</span>
							@if(!empty($user) && $item->id==$user->id)
								<a class="dont-count guided-description" guided-action="description" style="padding: 10px; margin: -10px; margin-left: 0px;">
									<img src="{{ url('img-trp/pencil.png') }}">
								</a>
							@endif
						</div>
						@if(!empty($user) && $item->id==$user->id)
							<div class="about-content" role="editor" id="edit-descr-container" style="display: none; padding: 5px;">
								{{ Form::open([
									'class' => 'edit-description', 
									'method' => 'post', 
									'url' => getLangUrl('profile/info') 
								]) }}
									{!! csrf_field() !!}
									<textarea class="input" name="description" id="dentist-description" placeholder="{!! nl2br(trans('trp.page.user.description-placeholder')) !!}">
										{{ $item->description }}
									</textarea>
									<p class="symbols-wrapper"><span id="symbols-count">0</span> / max length 512</p>
									<input type="hidden" name="field" value="description" />
									<input type="hidden" name="json" value="1" />
									<button type="submit" class="button skip-step">{!! nl2br(trans('trp.page.user.save')) !!}</button>
									<div class="alert alert-warning" style="display: none;">
									</div>
								{!! Form::close() !!}
							</div>
						@endif
					@endif
					
				</div>
			</div>
		@endif

		@if($showTeamSection)
			<div class="tab-container" id="team">
				<h2 class="mont">
					{!! nl2br(trans('trp.page.user.team')) !!} 
					@if(!empty($user) && $item->id==$user->id)
						<a class="edit-field-button">
							<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
						</a>
					@endif
				</h2>

				<div class="team-container">
					@foreach( $item->teamApproved as $team)
						@if($team->clinicTeam)
							<a class="team approved-team" href="{{ !$team->clinicTeam || in_array($team->clinicTeam->status, ['dentist_no_email', 'added_new']) ? 'javascript:;' : $team->clinicTeam->getLink() }}" dentist-id="{{ $team->clinicTeam->id }}">
								<div class="team-image" style="background-image: url('{{ $team->clinicTeam->getImageUrl(true) }}')">
									@if( (!empty($user) && $item->id==$user->id) )
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
									@if( (!empty($user) && $item->id==$user->id) )
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

					@if(!empty($user) && $item->id==$user->id)
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
					
					@if( (!empty($user) && $item->id==$user->id) )
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
		@endif
		
		@if($regularReviewsCount || $videoReviewsCount )
			<div class="tab-container" id="reviews">

				<h2 class="mont">
					{!! nl2br(trans('trp.page.user.reviews')) !!}
				</h2>

				<div class="reviews-type-buttons">
					@if($regularReviewsCount)
						<a href="javascript:;" class="show-written-reviews active">
							Written reviews
						</a>
					@endif
					@if($videoReviewsCount)
						<a href="javascript:;" class="show-video-reviews {{ !$regularReviewsCount ? 'active' : '' }}">
							Video reviews
						</a>
					@endif
				</div>

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
						<div class="flex">
							@foreach($aggregatedRating as $ratingLabel => $agg_rating)
								<div class="overview-column">
									<p>
										{{-- @if(!$answer)
											<img src="{{ url('img-trp/info-dark-gray.png') }}" class="tooltip-text" text="new"/>
										@endif --}}
										{{ $ratingLabel }}
									</p>
									<div class="ratings average">
										{{-- <div class="stars {{ $answer ? '' : 'new' }}"> --}}
										<div class="stars">
											{{-- @if($answer) --}}
												<div class="bar" style="width: {{ $agg_rating / 5 * 100 }}%;"></div>
											{{-- @else
												<div class="bar new" style="width: 0%;"></div>
											@endif --}}
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
							@foreach($item->reviews_in_standard() as $review)
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
		@endif
		
		@if( $showLocationsSection )
			<div class="tab-container" id="locations">
				<h2 class="mont">
					Locations
					{{-- {!! nl2br(trans('trp.page.user.about-who',[
						'name' => $item->getNames()
					])) !!} --}}

					@if(!empty($user) && $item->id==$user->id)
						<a class="edit-field-button" data-popup-logged="popup-wokring-time">
							<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
						</a>
					@endif
				</h2>
								

				<div class="tab-inner-section location-section">
					<div class="col">
						@if( ($item->lat && $item->lon) )
							<p class="map-address">{{ $item->address ? $item->address.', ' : '' }} {{ $item->country->name }}</p>
							<div class="map-container" id="profile-map" lat="{{ $item->lat }}" lon="{{ $item->lon }}"></div>
						@elseif(!empty($user) && $user->id==$item->id)
							<div class="alert alert-info">
								{!! nl2br(trans('trp.page.user.map-missing', [
									'link' => '<a href="javascript:;" class="open-edit alert-edit">',
									'endlink' => '</a>',
								])) !!}
							</div>
						@endif
					</div>
					@if($item->photos->isNotEmpty() || (!empty($user) && $item->id==$user->id) )
						<div class="gallery-slider col {!! count($item->photos) > 1 ? 'with-arrows' : '' !!}">
							<div class="gallery-flickity">
								{{-- @if( (!empty($user) && $item->id==$user->id && $item->photos->count() < 10 ) )
									<div class="slider-wrapper">
										{{ Form::open([
											'class' => 'gallery-add', 
											'method' => 'post', 
											'files' => true
										]) }}
											<label for="add-gallery-photo" class="add-gallery-image slider-image cover image-label dont-count" guided-action="photos">
												<div class="plus-gallery-image">
													<img src="{{ url('img/plus.svg') }}"/>
													<span>{!! nl2br(trans('trp.page.user.reviews-image')) !!}</span>
												</div>
												<div class="loader">
													<i></i>
												</div>
												<input type="file" name="image" id="add-gallery-photo" upload-url="{{ getLangUrl('profile/gallery') }}" sure-trans="{!! trans('trp.page.user.gallery-sure') !!}" accept="image/png,image/jpeg,image/jpg">
											</label>
										{!! Form::close() !!}
									</div>			    				
								@endif --}}
								@foreach($item->photos as $photo)
									<a href="{{ $photo->getImageUrl() }}" data-lightbox="user-gallery" class="slider-wrapper" photo-id="{{ $photo->id }}">
										<div class="slider-image cover" style="background-image: url('{{ $photo->getImageUrl(true) }}')">
											@if( (!empty($user) && $item->id==$user->id) )
												<div class="delete-gallery delete-button" sure="{!! trans('trp.page.user.gallery-sure') !!}">
													<img class="close-icon" src="{{ url('img/close-icon-white.png') }}"/>
												</div>
											@endif
										</div>
									</a>
								@endforeach
							</div>
						</div>
					@endif
				</div>

				@if($workingTime)
					<div class="tab-inner-section">
						<h3>Open hours</h3>

						<div class="flex work-hours">
							@foreach($week_days as $w => $week_day)
								<div class="col {{ date('w') == $w ? 'active' : '' }}">
									<p class="month">
										{{ $week_day }}
									</p>

									@if( (!empty($user) && $item->id==$user->id) )
										<div class="edit-working-hours-wrap">
											{{ Form::select( 
												'work_hours['.$w.'][0][0]', 
												$hours,
												!empty($user->work_hours[$w][0]) ? explode(':', $user->work_hours[$w][0])[0] : '' , 
												array(
													'class' => !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
													'placeholder' => 'HH',
												) 
											) }}
											{{ Form::select( 
												'work_hours['.$w.'][0][1]', 
												$minutes,
												!empty($user->work_hours[$w][0]) ? explode(':', $user->work_hours[$w][0])[1] : '' , 
												array(
													'class' => !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
													'placeholder' => 'MM',
												) 
											) }}
											{{ Form::select( 
												'work_hours['.$w.'][1][0]', 
												$hours,
												!empty($user->work_hours[$w][1]) ? explode(':', $user->work_hours[$w][1])[0] : '' , 
												array(
													'class' => !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
													'placeholder' => 'HH',
												) 
											) }}
											{{ Form::select( 
												'work_hours['.$w.'][1][1]', 
												$minutes,
												!empty($user->work_hours[$w][1]) ? explode(':', $user->work_hours[$w][1])[1] : '' , 
												array(
													'class' => !empty($user->work_hours[$w]) ? 'input' : 'input grayed', 
													'placeholder' => 'MM',
												) 
											) }}

										</div>

										<label class="checkbox-label" for="day-{{ $w }}"> 
											{{ Form::checkbox( 'day-'.$w, 1, '', array( 'id' => 'day-'.$w, 'class' => 'special-checkbox work-hour-cb', !empty($user->work_hours[$w]) ? 'checked' : 'something' => 'checked' ) ) }}
											<div class="checkbox-square">✓</div>
											Closed
										</label>

										@if($w == 1)
											<label class="checkbox-label" for="all-days-equal" style="display: none;"> 
												{{ Form::checkbox( 'all-days-equal', 1, '', array( 'id' => 'all-days-equal', 'class' => 'special-checkbox all-days-equal') ) }}
												<div class="checkbox-square">✓</div>
												{{-- {!! nl2br(trans('trp.popup.popup-wokring-time.user-same-hours')) !!} --}}
												Apply to all
											</label>
										@endif
									@else
										@if(array_key_exists($w, $item->work_hours))
											<p>
												@foreach($item->work_hours[$w] as $k => $work_hours)
													{{ $work_hours }}h {!! $loop->last ? '' : ' - ' !!}
												@endforeach
											</p>
										@else
											<p>Closed</p>
										@endif
									@endif
								</div>
							@endforeach
						</div>

						@if( (!empty($user) && $item->id==$user->id) )
							<input type="hidden" name="json" value="1" />
							<input type="hidden" name="field" value="work_hours" />
						@endif
					</div>
				@endif
				
				@if(!empty($item->accepted_payment) || (!empty($user) && $item->id==$user->id))
					<div class="tab-inner-section">
						<h3>Payment methods</h3>

						<div class="dentist-payments" role="presenter">
							
							<div class="flex">
								@foreach($item->accepted_payment as $acceptedPayment)
									<span class="value-here" empty-value="{{ nl2br(trans('trp.page.user.accepted-payment-empty')) }}">
										<img src="{{ url('img-trp/payment-methods/'.$acceptedPayment.'.svg') }}"/>
										{{ trans('trp.accepted-payments.'.$acceptedPayment) }}
									</span>
								@endforeach
								{{-- {{ $item->accepted_payment ? $item->parseAcceptedPayment( $item->accepted_payment ) : nl2br(trans('trp.page.user.accepted-payment-empty')) }} --}}
							</div>
							@if(!empty($user) && $item->id==$user->id)
								<a>
									<img src="{{ url('img-trp/pencil.png') }}">
								</a>
							@endif
						</div>
						{{-- @if(!empty($user) && $item->id==$user->id)
							<div class="dentist-payments" role="editor" style="display: none;">
								{{ Form::open([
									'class' => 'edit-description', 
									'method' => 'post', 
									'url' => getLangUrl('profile/info') 
								]) }}
									{!! csrf_field() !!}
									@foreach(config('trp.accepted_payment') as $ap)
										<label class="checkbox-label {!! in_array($ap, $user->accepted_payment) ? 'active' : '' !!}" for="checkbox-{{ $ap }}" >
											<input type="checkbox" class="special-checkbox" id="checkbox-{{ $ap }}" name="accepted_payment[]" value="{{ $ap }}" {!! in_array($ap, $user->accepted_payment) ? 'checked="checked"' : '' !!}>
											<div class="checkbox-square">✓</div>
											{!! trans('trp.accepted-payments.'.$ap) !!}
										</label>
									@endforeach
									<br/>
									<input type="hidden" name="field" value="accepted_payment" />
									<input type="hidden" name="json" value="1" />
									<button type="submit" class="button">
										{!! nl2br(trans('trp.page.user.save')) !!}
									</button>
									<div class="alert alert-warning" style="display: none;">
									</div>
								{!! Form::close() !!}
							</div>
						@endif --}}
					</div>
				@endif
			</div>
		@endif

		<div class="tab-container" id="more-info">
			<h2 class="mont">
				More info
				{{-- {!! nl2br(trans('trp.page.user.about-who',[
					'name' => $item->getNames()
				])) !!} --}}
			</h2>

			<div class="tab-inner-section">
				<h3>
					Education and background
				</h3>

				<div class="education-wrapper">
					•&nbsp;&nbsp;&nbsp;Medical School - Universidad Autónoma de Guadalajara Facultad de Medicina Guadalajara <br/>
					•&nbsp;&nbsp;&nbsp;New York Medical College, Doctor of Medicine <br/>
					•&nbsp;&nbsp;&nbsp;Icahn School of Medicine at Mount Sinai (Residency)
				</div>
			</div>

			<div class="tab-inner-section flex">
				<div>
					<h3>
						Experience
					</h3>

					<span class="bubble">
						Less than 5 years
					</span>
				</div>
				<div class="laguages-wrapper">
					<h3>
						Languages spoken <img src="{{ url('img-trp/info-dark-gray.png') }}" class="tooltip-text" text="Languages spoken in the dental practice."/>
					</h3>

					<span class="bubble">
						English
					</span>
					<span class="bubble">
						Spanish
					</span>
				</div>
			</div>
		</div>

		{{-- <div class="tab-inner-section">
			@if(!empty($user) && $user->id==$item->id && ($hasPatientInvites || $hasPatientAsks))
				<div class="tab-container" id="asks">

					@if($hasPatientAsks)
						<h2 class="mont">
							{!! nl2br(trans('trp.page.user.patient-requests')) !!} ({{ $user->asks->count() }})
						</h2>

						<div class="asks-container">

							<table class="table paging" num-paging="5">
								<thead>
									<tr>
										<th style="width: 20%;">
											{{ trans('trp.page.profile.asks.list-date') }}
										</th>
										<th style="width: 20%;">
											{{ trans('trp.page.profile.asks.list-name') }}
										</th>
										<th style="width: 30%;">
											{{ trans('trp.page.profile.asks.list-email') }}
										</th>
										<th style="width: 20%;">
											{{ trans('trp.page.profile.asks.list-note') }}
										</th>
										<th style="width: 10%;">
											{{ trans('trp.page.profile.asks.list-status') }}
										</th>
									</tr>
								</thead>
								<tbody>
									@foreach( $user->asks->sortBy(function ($elm, $key) {
										return $elm['status']=='waiting' ? -1 : 1;
									}) as $ask )

										@if(!$ask->hidden && $ask->user)
											<tr>
												<td>
													{{ $ask->created_at->toDateString() }}
												</td>
												<td>
													{{ $ask->user ? $ask->user->name : "deleted user" }}
												</td>
												<td>
													{{ $ask->user? $ask->user->email : 'deleted user' }}
												</td>
												<td>
													@php
														$askReview = \App\Models\Review::where('user_id', $ask->user->id)->where('dentist_id', $item->id)->orderBy('id', 'desc')->first();	
													@endphp
													@if(!empty($ask->review_id) || ($ask->on_review && !empty($ask->user) && !empty($askReview)))
														<a review-id="{{ !empty($ask->review_id) ? $ask->review_id : $askReview->id }}" href="javascript:;" class="show-review">
															See review
														</a>
													@else
														Invite Request
													@endif
												</td>
												<td>
													@if($ask->status=='waiting')
														<a class="btn btn-primary compact" href="{{ getLangUrl('profile/asks/accept/'.$ask->id) }}">
															<img src="{{ url('img/thumbs-up.svg') }}"/>
															{{ trans('trp.page.profile.asks.accept') }}
														</a>
														<a class="btn btn-inactive compact" href="{{ getLangUrl('profile/asks/deny/'.$ask->id) }}">
															<img src="{{ url('img/thumbs-down.svg') }}"/>
															{{ trans('trp.page.profile.asks.deny') }}
														</a>
													@else
														<span class="label label-{{ $ask->status=='yes' ? 'success' : 'warning' }}">
															{{ trans('trp.page.profile.asks.status-'.$ask->status) }}
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

					@if($hasPatientInvites)
						<h2 class="black-left-line section-title">
							{!! nl2br(trans('trp.page.user.review-invitation')) !!} ({{ $user->patients_invites->count() }})
						</h2>

						<div class="asks-container">

							<table class="table paging" num-paging="10">
								<thead>
									<tr>
										<th style="width: 20%;">
											{{ trans('trp.page.profile.invite.list-date') }}
										</th>
										<th style="width: 20%;">
											{{ trans('trp.page.profile.invite.list-name') }}
										</th>
										<th style="width: 50%;">
											{{ trans('trp.page.profile.invite.list-email') }}
										</th>
										<th style="width: 10%;">
											{{ trans('trp.page.profile.invite.list-status') }}
										</th>
									</tr>
								</thead>
								<tbody>
									@foreach( $user->patients_invites as $inv )
										@if(!$inv->hidden)
											<tr>
												<td>
													{{ $inv->created_at->toDateString() }}
												</td>
												<td>
													{{ $inv->invited_name }}
												</td>
												<td>
													{{ $inv->invited_email }}
												</td>
												<td>
													@if($inv->invited_id)

														@if(!empty($inv->hasReview($user->id)))
															@if(!empty($inv->dentistInviteAgain($user->id)))
																<a href="javascript:;" class="button invite-again" data-href="{{ getLangUrl('invite-patient-again') }}" inv-id="{{ $inv->id }}">{{ trans('trp.page.profile.invite.invite-again') }}</a><br>
															@endif
															<a review-id="{{ $inv->hasReview($user->id)->id }}" href="javascript:;" class="ask-review button">
																{{ trans('trp.page.profile.invite.status-review') }}
															</a>
														@else
															<span class="label label-warning">
																{{ trans('trp.page.profile.invite.status-no-review') }}
															</span>
														@endif
													@else
														<span class="label label-warning">
															{{ trans('trp.page.profile.invite.status-no-review') }}
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
			@endif
		</div> --}}

		<div class="tab-container">
			<h2 class="mont">
				Highlights
			</h2>

			<div class="tab-inner-section flex hightlights-wrapper">

				<a href="javascript:;" class="hightlight">
					<div class="hightlight-image">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-dentist-front-page.png') }}"/>
					</div>
					<p>Top Dentists of The Year 2021</p>
				</a>

				<a href="javascript:;" class="hightlight">
					<div class="hightlight-image">
						<img src="{{ url('img-trp/index-dentist-mobile.jpg') }}"/>
					</div>
					<p>Advance Dental Care & Implant Centre Joins Dentacoin Network!</p>
				</a>

				<a href="javascript:;" class="hightlight">
					<div class="hightlight-image">
						<img src="{{ url('img-trp/dentacoin-trusted-reviews-dentist-front-page.png') }}"/>
					</div>
					<p>Top Dentist with a Mission: Dr. Saif Siddiqui</p>
				</a>
			</div>
		</div>

		<input type="hidden" name="cur_dent_id" id="cur_dent_id" value="{{ $item->id }}">
	</div>
</div>

{{-- @if(!empty($user) && $item->id==$user->id)
	<div class="strength-parent fixed">
		@include('trp.parts.strength-scale')
	</div>
@endif --}}

@if(!empty($user))

	@if( $user->id==$item->id )
		@include('trp.popups.add-branch')
		@include('trp.popups.widget')
		@include('trp.popups.invite')
		@include('trp.popups.working-time')
		{{-- @if(!empty(session('first_guided_tour')) || !empty(session('reviews_guided_tour')))
			@include('trp.popups.first-guided-tour')
		@endif --}}
		@if( $user->is_clinic )
			{{-- @include('trp.popups.add-member') --}}
		@else
			{{-- @include('trp.popups.workplace') --}}
		@endif
	@else
		@if(!empty($writes_review))
			@include('trp.popups.recommend-dentist')
		@endif
		@if(empty($is_trusted) && !$has_asked_dentist)
			@include('trp.popups.ask-dentist')
		@endif
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
</script>

@endsection