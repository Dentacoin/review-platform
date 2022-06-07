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

{{-- @if($loggedUserAllowEdit )
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

	<div class="profile-wrapper" link="{{ getLangUrl('profile/info/'.($editing_branch_clinic )) }}">
		<div class="profile-info-container">
			<div class="profile-info flex">
				<div class="avatar-wrapper">
					@if($loggedUserAllowEdit)
						<div class="upload-image-wrapper">
							{{ Form::open([
								'class' => 'edit-wrapper edit-name', 
								'method' => 'post', 
								'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
							]) }}
								{!! csrf_field() !!}
								<label for="add-avatar" class="image-label tooltip-text" style="background-image: url({{ $user->getImageUrl(true)}})" text="Best resolution: <br/>
									150x150px <br/>
									(max. 200x200 px) <br/>
									Max. size: 2 MB
									">
									<div class="centered-hack">
										<p class="mont">
											@if( !$user->hasimage )
												Add image
												{{-- {!! nl2br(trans('trp.page.user.add-photo')) !!} --}}
											@else
												Edit image
												{{-- {!! nl2br(trans('trp.page.user.change-photo')) !!} --}}
											@endif
										</p>
									</div>
									<div class="loader">
										<i></i>
									</div>
									<input 
									type="file" 
									name="image" 
									id="add-avatar" 
									class="input-croppie" 
									upload-url="{{ getLangUrl('register/upload') }}" 
									accept="image/png,image/jpeg,image/jpg">
									<input type="hidden" name="avatar" class="avatar">
								</label>
			
								<div class="cropper-container"></div>
								<div class="avatar-name-wrapper">
									<span class="avatar-name"></span>
									<button class="destroy-croppie" type="button">×</button>
								</div>
								
								<button type="submit" class="blue-button save-avatar">
									Save image
								</button>
								
								<input type="hidden" name="json" value="1" />

								<div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
							{!! Form::close() !!}
						</div>
					@else
						<img class="avatar" src="{{ $item->getImageUrl(true) }}" alt="{{ trans('trp.alt-tags.reviews-for', [
							'name' => $item->getNames(), 
							'location' => $item->getLocation()
						]) }}" width="150" height="150">
					@endif
					@if($item->is_clinic && $item->branches->isNotEmpty() && $item->id == $item->mainBranchClinic->id)
						<div class="main-clinic mont">{!! nl2br(trans('trp.common.primary-account')) !!}</div>
					@endif

					
					{{-- @if($loggedUserAllowEdit && !empty($writes_review))
						<a href="javascript:;" class="recommend-button" data-popup="recommend-dentist">
							<img src="{{ url('img-trp/thumb-up.svg') }}">
							{{ trans('trp.page.user.recommend') }}
						</a>
					@endif --}}
					
					@php
						$is_branch = !empty($real_user) && $real_user->is_clinic && $item->is_clinic && $real_user->branches->isNotEmpty() && in_array($item->id, $real_user->branches->pluck('branch_clinic_id')->toArray());
						$is_main_clinic_branch = !empty($real_user) && $real_user->is_clinic && $item->is_clinic && $real_user->branches->isNotEmpty() && $real_user->id == $item->mainBranchClinic->id;
					@endphp

					@if( $is_branch || $is_main_clinic_branch)
						
						@if($is_main_clinic_branch && $is_branch)
							<a href="javascript:;" delete-url="{{ getLangUrl('delete-branch') }}" branch-id="{{ $item->id }}" class="delete-branch white-button">
								X Delete branch
							</a>
						@else
							<a class="clinic-branches mont" href="{{ getLangUrl($item->slug.'/branches') }}">
								<img src="{{ url('img-trp/branches.svg') }}" width="24"/>
								Manage branches
							</a>
						@endif
					@else
						@if($item->branches->isNotEmpty())
							<a class="clinic-branches mont" href="{{ getLangUrl($item->slug.'/branches') }}">
								<img src="{{ url('img-trp/branches.svg') }}" width="24"/>
								Check branches
							</a>
						@endif
					@endif					

					@if(empty($user) && in_array($item->status, config('dentist-statuses.unclaimed')))
						<a class="claim-button" href="javascript:;" data-popup="claim-popup">
							{{ trans('trp.common.claim-practice') }}
						</a>
					@endif

					@if($loggedUserAllowEdit)
						<a href="javascript:;" class="turn-on-edit-mode white-button" to-edit="Edit Profile" to-not-edit="Edit Mode">
							<img src="{{ url('img-trp/edit-profile-pencil.svg') }}" width="20" height="20"/>
							<span>Edit Profile</span>
						</a>
					@endif

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
					@if($loggedUserAllowEdit)
						<div class="edit-field">
							<h1 class="mont edited-field" id="value-name" style="display: inline-block;">
								{{ $item->getNames() }}
							</h1>

							<a class="edit-field-button tooltip-text" text="{{ $item->is_clinic ? 'Edit clinic name' : 'Edit title and dentist name' }}">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							@if($loggedUserAllowEdit)
								{{ Form::open([
									'class' => 'edit-wrapper edit-name', 
									'method' => 'post', 
									'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : ''))
								]) }}
									{!! csrf_field() !!}
									<div class="flex flex-mobile">

										@if(!$user->is_clinic)
											<div class="flex flex-column col">
												{{ Form::select( 'title' , config('titles') , $user->title , array('class' => 'input') ) }}
												<input type="hidden" name="field" value="title" />

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
										
										<button type="submit" class="save-field">
											<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
										</button>
									</div>
									<div class="alert alert-warning mobile" style="display: none;"></div>
									<input type="hidden" name="field" value="name" />
									<input type="hidden" name="json" value="1" />
								{!! Form::close() !!}
							@endif
						</div>
					@else
						<h1 class="mont">{{ $item->getNames() }}</h1>
					@endif

					{{-- edit phone --}}
					@if($loggedUserAllowEdit)
						<div class="edit-field {{ $item->name_alternative ? '' : 'show-on-edit-mode' }}">
							<h3 class="edited-field alternative-name" id="value-name_alternative" style="display: inline-block;">
								{{ $item->name_alternative ?? 'Add alternative name' }}
							</h3>

							<a class="edit-field-button {{ $item->name_alternative ? 'tooltip-text' : '' }}" text="Edit alternative name">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							@if($loggedUserAllowEdit)
								{{ Form::open([
									'class' => 'edit-wrapper', 
									'method' => 'post', 
									'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
								]) }}
									{!! csrf_field() !!}
									<div class="flex flex-center">

										<div class="flex flex-mobile flex-center" style="width: 100%;">
											<input 
											type="text" 
											name="name_alternative" 
											class="input input-alternative" 
											{{-- placeholder="{!! nl2br(trans('trp.page.user.name_alterantive')) !!}"  --}}
											value="{{ $user->name_alternative }}"
											>
											<button type="submit" class="save-field">
												<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
											</button>
										</div>
										
									</div>
									<div class="alert alert-warning mobile" style="display: none;"></div>
									<input type="hidden" name="field" value="name_alternative" />
									<input type="hidden" name="json" value="1" />
								{!! Form::close() !!}
							@endif
						</div>
					@else
						@if( $item->name_alternative )
							<h3 class="alternative-name">({{ $item->name_alternative }})</h3>
						@endif
					@endif

					@if($workplace || $workingTime || $loggedUserAllowEdit)
						<div class="flex flex-center workplace-wrapper {{ $workplace || $workingTime ? '' : 'show-on-edit-mode' }}">
							<div class="col">
								@if($workplace || ($loggedUserAllowEdit && !$item->is_clinic))
									<div class="workplace">
										{!! $workplace ?? 'Add workplace' !!}
									</div>
								@endif

								@if($loggedUserAllowEdit && !$item->is_clinic)
									<div class="edit-workplace-wrapper">
										<div class="search-dentist clinic-suggester-wrapper suggester-wrapper">
											<img class="search-icon" src="{{ url('img/search-gray.svg') }}"/>										
											<input 
											type="text" 
											class="input clinic-suggester suggester-input" 
											name="search-clinic" 
											autocomplete="off" 
											{{-- placeholder="{!! nl2br(trans('trp.popup.popup-wokrplace.search')) !!}" --}}
											placeholder="Start typing your workplace name"
											/>
											<div class="suggest-results"></div>
											<input type="hidden" class="suggester-hidden" name="clinic_id" value=""/>
										</div>
								
										<div id="workplaces-list" class="invite-content">
											@if($item->my_workplace->isNotEmpty())
												@foreach( $item->my_workplace as $workplace )
													<span class="workplace-clinic {{ !$workplace->approved ? 'grayed tooltip-text' : '' }}" text="{!! trans('trp.popup.popup-wokrplace.pending') !!}">
														<a href="{{ $workplace->clinic->getLink() }}">
															{{ $workplace->clinic->getNames() }}
														</a>
														<a class="remove-dentist" href="{{ getLangUrl('profile/clinics/delete/'.$workplace->clinic->id) }}">
															<img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
														</a>
													</span>
												@endforeach
											@else
												<span class="show-on-edit-mode-inline">
													Add workplace
												</span>
											@endif
											<a class="edit-field-button edit-workplace {{ $workplace ? 'tooltip-text' : ''}}" text="Edit workplace">
												<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
											</a>
										</div>
									</div>
								@endif
							</div>
							<div>
								@if($workingTime)
									@if(str_contains($workingTime, 'Open now'))
										<div class="working-time open {{ $loggedUserAllowEdit ? 'wider' : '' }}">
											<img src="{{ url('img-trp/clock-blue.svg') }}">
											Open now
											<div class="work-hours">
												@foreach($week_days as $w => $week_day)
													<div class="flex {{ date('w') == $w ? 'active' : '' }}">
														<p class="month">
															{{ $week_day }}
														</p>
														@if($dentistWorkHours && array_key_exists($w, $dentistWorkHours))
															<p>
																@foreach($dentistWorkHours[$w] as $k => $work_hours)
																	{{ $work_hours }} {!! $loop->last ? '' : ' - ' !!}
																@endforeach
															</p>
														@else
															<p>Closed</p>
														@endif
													</div>
												@endforeach
											</div>

											@if($loggedUserAllowEdit)
												<a class="edit-field-button scroll-to" scroll="open-hours-section">
													<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
												</a>
											@endif
										</div>
									@else
										<div class="working-time closed {{ $loggedUserAllowEdit ? 'wider' : '' }}">
											<img src="{{ url('img-trp/clock-red.svg') }}"/>
											Closed now

											@if($loggedUserAllowEdit)
												<a class="edit-field-button scroll-to" scroll="open-hours-section">
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
					@if($loggedUserAllowEdit)
						<div class="edit-field">
							<p class="dentist-address edited-field" id="value-address" style="display: inline-block;">
								{{ $item->address ?? 'Edit your address' }}
							</p>

							<a class="edit-field-button {{ $item->address ? 'tooltip-text' : '' }}" text="Еdit address">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							@if($loggedUserAllowEdit)
								{{ Form::open([
									'class' => 'edit-wrapper address-suggester-wrapper-input', 
									'method' => 'post', 
									'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
								]) }}
									{!! csrf_field() !!}

									<select name="country_id" id="dentist-country1" class="modern-input country-select" style="display: none">
										@if(!$country_id)
											<option>-</option>
										@endif
										@if(!empty($countries))
											@foreach( $countries as $country )
												<option value="{{ $country->id }}" code="{{ $country->code }}" {!! $country_id==$country->id ? 'selected="selected"' : '' !!} >{{ $country->name }}</option>
											@endforeach
										@endif
									</select>

									<div class="flex flex-mobile flex-center">
										<input 
										type="text" 
										name="address" 
										class="input address-suggester-input" 
										autocomplete="off" 
										placeholder="{!! nl2br(trans('trp.page.user.city-street')) !!}" 
										value="{{ $user->address }}"
										>
										<button type="submit" class="save-field">
											<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
										</button>
									</div>
									<div class="suggester-map-div" {!! $user->lat ? 'lat="'.$user->lat.'" lon="'.$user->lon.'"' : '' !!} style="height: 100px; display: none; margin: 10px 0px;">
									</div>
									<div class="alert alert-info geoip-confirmation mobile secondary-info" style="display: none; margin: 10px 0px;">
										{!! nl2br(trans('trp.common.check-address')) !!}
									</div>
									<div class="alert alert-warning geoip-hint mobile secondary-info" style="display: none; margin: 10px 0px;">
										{!! nl2br(trans('trp.common.invalid-address')) !!}
									</div>
									<div class="alert alert-warning different-country-hint mobile secondary-info" style="display: none; margin: -10px 0px 10px;">
										{!! nl2br(trans('trp.page.user.invalid-country')) !!}
									</div>
									
									<div class="alert alert-warning mobile" style="display: none;"></div>
									<input type="hidden" name="field" value="address" />
									<input type="hidden" name="json" value="1" />
								{!! Form::close() !!}
							@endif
						</div>
					@else
						@if($item->address)
							<div class="dentist-address">
								{{ $item->address }}
							</div>
						@endif
					@endif
					
					{{-- edit phone --}}
					@if($loggedUserAllowEdit)
						<div class="edit-field">
							<p class="edited-field" id="value-phone" style="display: inline-block;">
								{{ $item->getFormattedPhone() ?? 'Edit your phone number' }}
							</p>

							<a class="edit-field-button {{ $item->phone ? 'tooltip-text' : '' }}" text="Edit phone number">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							{{ Form::open([
								'class' => 'edit-wrapper', 
								'method' => 'post', 
								'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
							]) }}
								{!! csrf_field() !!}

								<div class="flex flex-mobile flex-center phone-widget">
									<span class="phone-code-holder">{{ $user->country_id ? '+'.$user->country->phone_code : '' }}</span>
									<input 
									type="tel" 
									name="phone" 
									class="input" 
									placeholder="{!! nl2br(trans('trp.page.user.phone')) !!}" 
									value="{{ $user->phone }}"
									>
									<button type="submit" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</button>
								</div>
								<div class="alert alert-warning mobile" style="display: none;"></div>
								<input type="hidden" name="field" value="phone" />
								<input type="hidden" name="json" value="1" />
							{!! Form::close() !!}
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
					@if($loggedUserAllowEdit)
						<div class="edit-field">
							<p class="edited-field" style="display: inline-block;">
								<a class="blue-href" href="{{ $item->getWebsiteUrl() }}" target="_blank" id="value-website">
									{{ $item->website ?? 'Add your website' }}
								</a>
							</p>

							<a class="edit-field-button {{ $item->website ? 'tooltip-text' : '' }}" text="Edit website">
								<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
							</a>

							{{ Form::open([
								'class' => 'edit-wrapper', 
								'method' => 'post', 
								'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
							]) }}
								{!! csrf_field() !!}

								<div class="flex flex-mobile">
									<input 
									type="text" 
									name="website" 
									class="input" 
									placeholder="{!! nl2br(trans('trp.page.user.website')) !!}" 
									value="{{ $user->website }}"
									>
									<button type="submit" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</button>
								</div>

								<div class="alert alert-warning mobile" style="display: none;"></div>
								<input type="hidden" name="field" value="website" />
								<input type="hidden" name="json" value="1" />
							{!! Form::close() !!}
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
									<a class="social email-social" href="mailto:{{ $item->email_public ? $item->email_public : $item->email }}">
										<img src="{{ url('img-trp/social-network/email.svg') }}" height="26"/>
									</a>
								@else
									@if($item->branches->isNotEmpty())
										<a class="social email-social" href="mailto:{{ $item->email_public ? $item->email_public : ($item->mainBranchClinic->email_public ?? $item->mainBranchClinic->email) }}">
											<img src="{{ url('img-trp/social-network/email.svg') }}" height="26"/>
										</a>
									@endif
								@endif
								
								@if( $item->socials)
									@foreach($item->socials as $k => $v)
										<a class="social {{ $k }}" href="{{ $v }}" target="_blank">
											<img src="{{ url('img-trp/social-network/'.$k.'.svg') }}" height="26"/>
										</a>
									@endforeach
								@endif

								@if($loggedUserAllowEdit)
									<a class="edit-field-button tooltip-text" text="{{ $item->socials || $item->email ? 'Edit' : 'Add' }} email and social profiles">
										<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17"/>
									</a>
								@endif
							</div>
						@endif

						@if($loggedUserAllowEdit)
							
							{{ Form::open([
								'class' => 'edit-wrapper', 
								'method' => 'post', 
								'url' => getLangUrl('profile/info/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
							]) }}
								{!! csrf_field() !!}

								<div class="edit-field">
									<div style="flex:1;">
										<div class="email-wrapper">
											<div class="flex flex-mobile email-wrap {{ empty($user->email_public) ? 'disabled-email' : '' }}">
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
													<div class="flex flex-mobile social-wrap flexed-wrap">
														<div class="col social-networks">
															<a href="javascript:;" class="current-social" cur-type="{{ $k }}">
																<img src="{{ url('img-trp/social-network/'.config('trp.social_network')[$k].'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
															</a>
															<div class="social-dropdown"> 
																@foreach(config('trp.social_network') as $key => $sn)
																	<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! isset($user->socials[$key]) ? 'inactive' : ''; !!}">
																		<img class="{{ $sn }}" class-attr="{{ $sn }}" src="{{ url('img-trp/social-network/'.$sn.'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
																	</a>
																@endforeach
															</div>
														</div>
														<div class="col">
															<input type="text" name="socials[{{ $k }}]" class="input social-link-input" value="{{ $v }}" maxlength="300">
														</div>
														<a href="javascript:;" class="remove-social">
															<img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
														</a>
													</div>
												@endforeach
											@else
												<div class="flex social-wrap flexed-wrap">
													<div class="col social-networks">
														<a href="javascript:;" class="current-social" cur-type="{{ array_values(config('trp.social_network'))[0] }}">
															<img src="{{ url('img-trp/social-network/'.array_values(config('trp.social_network'))[0].'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
														</a>
														<div class="social-dropdown"> 
															@foreach(config('trp.social_network') as $key => $sn)
																<a href="javascript:;" social-type="{{ $key }}" social-class="{{ $sn }}" class="social-link {!! $loop->first ? 'inactive' : '' !!}">
																	<img src="{{ url('img-trp/social-network/'.$sn.'.svg') }}" src-attr="{{ url('img-trp/social-network/') }}"/>
																</a>
															@endforeach
														</div>
													</div>
													<div class="col">
														<input type="text" name="socials[{{ key(config('trp.social_network')) }}]" class="input social-link-input" maxlength="300">
													</div>
													<a href="javascript:;" class="remove-social">
														<img class="close-icon" src="{{ url('img-trp/close-icon-blue.png') }}" width="10"/>
													</a>
												</div>
											@endif
											
											@if(!$user->socials || count($user->socials) != count(config('trp.social_network')))
												<a href="javascript:;" class="add-social-profile">
													+ Add another social link
													{{-- {!! nl2br(trans('trp.page.user.add-social-profile')) !!} --}}
												</a>
											@endif
										</div>
									</div>

									<button type="submit" class="save-field">
										<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
									</button>
								</div>

								<div class="alert alert-warning mobile" style="display: none;"></div>
								<input type="hidden" name="json" value="1" />
							{!! Form::close() !!}
							
						@endif
					</div>
					
					{{-- @if(false) --}}
						@if($item->announcement || $loggedUserAllowEdit)
							<div class="announcement-wrapper {{ !$item->announcement && $loggedUserAllowEdit ? 'show-on-edit-mode' : '' }}">

								<div class="announcement-wrap">
									<h4>
										<img src="{{ url('img-trp/announcement.svg') }}" width="16"/>
										<span>{{ $item->announcement ? $item->announcement->title : 'Add office update' }}</span>
										@if($loggedUserAllowEdit)
											<a class="edit-field-button edit-announcement {{ $item->announcement ? 'tooltip-text' : '' }}" text="Edit office update">
												<img src="{{ url('img-trp/pencil.svg') }}" width="20" height="17">
											</a>
										@endif
									</h4>
									<p class="announcement-subtitle" {!! $item->announcement ? '' : 'style="display:none;"' !!}>
										Message from {{ $item->getNames() }}{{ substr($item->getNames(), -1) == 's' ? "'" : "'s" }} office
									</p>
									<p>
										@php
											$announcement_description = $item->announcement ? $item->announcement->description : '';
										@endphp
										<span 
											class="announcement-description" 
											short-text="{{ substr($announcement_description, 0, 150) }}" 
											long-text="{{ $announcement_description }}"
										>
											{{ $announcement_description ? '"' : '' }}{{ substr($announcement_description, 0, 150) }}{!! strlen($announcement_description) < 150 ? '' : '..' !!}{{ $announcement_description ? '"' : '' }}
										</span>
										<a href="javascript:;" class="show-full-announcement" short-text="show more" long-text="show less" {!! strlen($announcement_description) < 150 ? 'style="display:none;"' : '' !!}>show more</a>
									</p>
								</div>
								@if($loggedUserAllowEdit)
									
									{{ Form::open([
										'class' => 'edit-announcement-form', 
										'method' => 'post', 
										'url' => getLangUrl('profile/add-announcement/'.($editing_branch_clinic ? $editing_branch_clinic->id : '')) 
									]) }}
										{!! csrf_field() !!}
	
										<div class="flex flex-mobile">
											<div class="col">
												<input type="text" 
												name="announcement_title" 
												class="input" 
												autocomplete="off" 
												value="{{ $item->announcement ? $item->announcement->title : '' }}" 
												placeholder="What's new at your dental clinic?" 
												maxlength="64"/>
	
												<textarea 
												class="input" 
												name="announcement_description"
												placeholder="Inform your patients about latest promotions, days off, and other office updates"
												maxlength="1500"
												>{{ $item->announcement ? $item->announcement->description : '' }}</textarea>
											</div>
	
											<button type="submit" class="save-field">
												<img src="{{ url('img-trp/white-check.svg') }}" width="20" height="15"/>
											</button>
										</div>
	
									{!! Form::close() !!}
									
								@endif
							</div>

						@endif
					{{-- @endif --}}
				</div>
			</div>
		</div>
		<div class="profile-rating">
			<div class="profile-rating-inner">
				<div class="rating-flex">
					@if($item->avg_rating)
						<div class="rating mont">
							{{ number_format($item->avg_rating, 1) }}
						</div>
					@endif
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

				<div class="buttons-flex">
					@if($loggedUserAllowEdit)
					
						@if( $regularReviewsCount )
							<a href="javascript:;" class="white-button disabled-button tooltip-text" text="Comming soon">
							{{-- <a href="javascript:;" class="white-button disabled-button add-widget-button" data-popup-logged="popup-widget" reviews-guided-action="add"> --}}
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
						{{-- @if(empty($is_trusted) && !$has_asked_dentist) --}}
						@if(false)
							<a href="javascript:;" class="blue-button button-inner-white button-ask" data-popup-logged="popup-ask-dentist">
								{!! nl2br(trans('trp.page.user.request-invite')) !!}
							</a>
						@endif
					@endif
				</div>
			</div>
			@if($item->top_dentist_month || $item->top_dentist_year || $item->golden_partner)
				<div class="awards">
					<h4>Awards</h4>
					
					@if($item->golden_partner)
						<div class="top-dentist">
							<img src="{{ url('img-trp/golden-partner.png') }}">
							<span>
								Golden Partner
							</span>
						</div>
					@endif
					@if($item->top_dentist_month)
						<div class="top-dentist">
							<img src="{{ url('img-trp/top-dentist-month.png') }}">
							<span>
								{!! trans('trp.common.top-dentist') !!}: {{ $item->getLastTopDentistBadge() }}
							</span>
						</div>
					@endif
					@if($item->top_dentist_year)
						<div class="top-dentist">
							<img src="{{ url('img-trp/top-dentist-year.png') }}">
							<span>
								{!! trans('trp.common.top-dentist') !!}: {{ $item->getLastTopDentistYearBadge() }}
							</span>
						</div>
					@endif
				</div>
			@endif
		</div>
	</div>

	{{-- @if(in_array($item->status, config('dentist-statuses.unclaimed')))
		<div class="invited-dentist">{!! nl2br(trans('trp.page.user.added-by-patient')) !!}</div>
	@endif --}}
</div>
{{-- @if($loggedUserAllowEdit)
	<div class="strength-parent fixed">
		@include('trp.parts.strength-scale')
	</div>
@endif --}}

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