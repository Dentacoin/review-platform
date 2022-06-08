<div class="popup verification-popup no-image active removable" id="verification-popup" scss-load="trp-popup-verification" js-load="dentist-verification">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>
		
		<div class="verification-content">
			<h2 class="mont">
				While waiting for verification, start completing your profile
				{{-- {!! nl2br(trans('trp.popup.verification-popup.title')) !!} --}}
			</h2>

			<div class="step" step="1">
				<p class="popup-desc">
					<span>Step 1:</span> Add open hours to welcome new patients
				</p>

				<div class="open-hours-section edit-mode">
					{!! Form::open([
						'class' => 'edit-working-hours-form',
						'method' => 'post', 
						'url' => getLangUrl('verification-dentist-work-hours')
					]) !!}
						{!! csrf_field() !!}
						<input type="hidden" name="last_user_id" value=""/>
						<input type="hidden" name="last_user_hash" value=""/>
						@include('trp.parts.open-hours', [
							'withoutUser' => true,
							'loggedUserAllowEdit' => true,
							'dentistWorkHours' => false,
						])
						<input type="hidden" name="json" value="1" />
						<input type="hidden" name="field" value="work_hours"/>

						<div class="step-buttons tac">
							<a href="javascript:;" class="white-button skip" to-step="2">
								Skip
							</a>
							<button type="submit" class="blue-button">
								Save Open hours
							</button>
						</div>
					{!! Form::close() !!}
				</div>
			</div>

			<div class="step" step="2" style="display: none;">
				<div id="clinic-add-team">
					<p class="popup-desc">
						<span>Step 2:</span> Add team members to your clinic profile
						{{-- {{ trans('trp.popup.verification-popup.show-team') }} --}}
					</p>

					{!! Form::open([
						'method' => 'post', 
						'class' => 'invite-dentist-form', 
						'url' => getLangUrl('invite-dentist') 
					]) !!}
						{!! csrf_field() !!}

						<input type="hidden" name="last_user_id" value=""/>
						<input type="hidden" name="last_user_hash" value=""/>

						<div class="dentist-suggester-wrapper suggester-wrapper">
							<input 
							type="text" 
							name="invitedentist" 
							class="input dentist-suggester suggester-input" 
							value="" 
							autocomplete="off"
							placeholder="Search for registered dental professionals">

							<div class="suggest-results"></div>
							
							<input 
							type="hidden" 
							class="suggester-hidden" 
							name="dentist_id" 
							value="" 
							url="{{ getLangUrl('invite-dentist') }}"/>
						</div>

						<div class="alert alert-success alert-success-d" style="display: none; margin-top: 20px;"></div>
						<div class="alert alert-warning alert-warning-d" style="display: none; margin-top: 20px;"></div>

						<a href="javascript:;" class="invite-manual">
							<img src="{{ url('img-trp/add-icon-in-button.png') }}" width="27"/>
							Invite non-registered dentist via email
						</a>
					{!! Form::close() !!}

					<div class="add-team-manual" style="display: none;">
						{!! Form::open([
							'method' => 'post', 
							'files'=> true, 
							'class' => 'search-dentist-form add-team-member-form', 
							'url' => getLangUrl('profile/invite-new') 
						]) !!}
							{!! csrf_field() !!}

							<input type="hidden" name="last_user_id" value=""/>
							<input type="hidden" name="last_user_hash" value=""/>

							<div class="flex">
								<input type="hidden" name="check-for-same" class="check-for-same"/>
								<div class="upload-image-wrapper">
									<label for="add-avatar-member" class="image-label team-label-image">
										<div class="plus-gallery-image">
											<img class="add-gallery-icon" src="{{ url('img-trp/add-icon.png') }}">
											<span>
												Add image
												
												<img 
												src="{{ url('img-trp/info-dark-gray.png') }}" 
												class="tooltip-text" text="Required resolution: 150x150px<br/> Max. image size: 2 MB"/>
											</span>
										</div>
										<div class="loader">
											<i></i>
										</div>
										<input 
										type="file" 
										name="image" 
										class="add-avatar-member" 
										accept="image/png,image/jpeg,image/jpg" 
										id="add-avatar-member" 
										upload-url="{{ getLangUrl('register/upload') }}"/>
										<input type="hidden" name="avatar" class="avatar"/>
									</label>
									
									<div class="cropper-container add-team-cropper"></div>
									<div class="avatar-name-wrapper">
										<span class="avatar-name"></span>
										<button class="destroy-croppie" type="button">×</button>
									</div>
									<div class="alert alert-warning image-big-error" style="display: none; margin-top: 20px;">The file you selected is large. Max size: 2MB.</div>
								</div>
								<div class="col">
									<div class="modern-field">
										<input 
										type="text" 
										class="modern-input team-member-name" 
										id="team-member-name" 
										name="name"/>
										<label for="team-member-name">
											{{-- <span>{{ trans('trp.popup.verification-popup.add-team-name') }}</span> --}}
											<span>Enter name</span>
										</label>
									</div>
									
									<div class="modern-field alert-after">
										<select name="team-job" id="team-member-job" class="modern-input team-member-job">
											@foreach(config('trp.team_jobs') as $k => $v)
												<option value="{{ $k }}">{{ trans('trp.team-jobs.'.$k) }}</option>
											@endforeach
										</select>
										<label for="team-member-job">
											<span>{{ trans('trp.popup.verification-popup.add-team-position') }}:</span>
										</label>
									</div>
								</div>
							</div>
							<div class="flex">
								<div class="col mail-col dentist-col" style="display: none;">
									<div class="modern-field">
									<input 
									type="email" 
									class="modern-input team-member-email" 
									id="team-member-email" 
									name="email" 
									placeholder="{{ trans('trp.common.optional') }}"/>
									<label for="team-member-email">
										<span>{{ trans('trp.popup.verification-popup.add-team-email') }}</span>
									</label>
								</div>
								</div>
								<div class="col specializations-col dentist-col" style="display: none;">
									<div class="modern-field alert-after">
										<select name="team-speciality" id="team-member-speciality" class="modern-input team-member-speciality">
											@foreach(config('categories') as $k => $v)
												<option value="{{ $k }}">{{ trans('trp.categories.'.$v) }}</option>
											@endforeach
										</select>
										<label for="team-member-speciality">
											{{-- <span>{{ trans('trp.popup.verification-popup.add-team-position') }}:</span> --}}
											<span>Select specialty:</span>
										</label>
									</div>
								</div>
							</div>

							<div class="alert member-alert" style="display: none; margin-top: 20px;"></div>
							<div class="tac">
								<a href="javascript:;" class="invite-existing-dentist">Cancel</a>
								{{-- <input type="submit" class="button" value="{{ trans('trp.popup.verification-popup.add-team-button') }}"> --}}
								<input type="submit" class="green-button" value="Invite dentist">
							</div>
						{!! Form::close() !!}
					</div>

					<div class="step-buttons tac">
						<a href="javascript:;" class="white-button skip" to-step="3">
							Skip
						</a>
					</div>
				</div>
			</div>

			<div class="step" step="3" style="display: none;">
				<p class="popup-desc">
					<span>Step 3:</span> Add a short description about your clinic
					{{-- {{ trans('trp.popup.verification-popup.show-team') }} --}}
				</p>

				{!! Form::open([
					'method' => 'post', 
					'class' => 'verification-form', 
					'url' => getLangUrl('verification-dentist') 
				]) !!}
					{!! csrf_field() !!}
					
					<input type="hidden" name="last_user_id" value=""/>
					<input type="hidden" name="last_user_hash" value=""/>

					<div class="modern-field">
						<textarea class="modern-input" id="dentist-description" name="description" maxlength="512"></textarea>
					</div>

					<div class="alert alert-warning descr-error" style="display: none; margin-top: 20px;">
						{{ trans('trp.popup.verification-popup.description-error') }}
					</div>
					<div class="alert alert-success descr-success" style="display: none; margin-top: 20px;"></div>
					<div class="tac step-buttons">
						<a href="javascript:;" class="white-button close-popup">
							Skip
						</a>
						<button class="blue-button" type="submit">
							Save description
							{{-- {!! nl2br(trans('trp.popup.verification-popup.save')) !!} --}}
						</button>
					</div>

				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>