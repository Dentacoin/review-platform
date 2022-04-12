<div class="popup fixed-popup verification-popup active removable" id="verification-popup" scss-load="trp-popup-verification">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<img src="{{ url('img/close-icon.png') }}"/>
		</a>
		<div class="verification-content">
			<img src="{{ url('img-trp/verification-icon.png') }}">
			<div id="title-dentist" style="display: none;">
				<h2>
					{!! nl2br(trans('trp.popup.verification-popup.title')) !!}
				</h2>
				<h4>
					{!! nl2br(trans('trp.popup.verification-popup.hint')) !!}
				</h4>
			</div>
			<div id="title-clinic" style="display: none;">
				<h2>
					{!! nl2br(trans('trp.popup.verification-popup.clinic-title')) !!}
				</h2>
				<h4>
					{!! nl2br(trans('trp.popup.verification-popup.clinic-hint')) !!}
				</h4>
			</div>
		</div>

		<div class="verification-info">
			<h2>{!! nl2br(trans('trp.popup.verification-popup.subtitle')) !!}</h2>
			<a href="javascript:;" class="button wh-btn" data-popup="popup-wokring-time-waiting" style="margin-bottom: 20px;">{{ trans('trp.popup.verification-popup.open-hours') }}</a>

			<div id="clinic-add-team">
				<h4 class="popup-title">
					{{ trans('trp.popup.verification-popup.show-team') }}
				</h4>
				{!! Form::open([
					'method' => 'post', 
					'class' => 'invite-dentist-form', 
					'url' => getLangUrl('invite-dentist') 
				]) !!}
					{!! csrf_field() !!}

					<input type="hidden" name="last_user_id" value=""/>
					<input type="hidden" name="last_user_hash" value=""/>

					<div class="dentist-suggester-wrapper suggester-wrapper">
						<div class="modern-field">
							<input type="text" name="invitedentist" class="modern-input dentist-suggester suggester-input" value="" autocomplete="off">
							<label for="invitedentist">
								<span>{!! nl2br(trans('trp.popup.verification-popup.add-dentist')) !!}</span>
							</label>
							<p>{!! nl2br(trans('trp.popup.verification-popup.add-dentist.hint')) !!}</p>

							<div class="suggest-results">
							</div>
							<input type="hidden" class="suggester-hidden" name="dentist_id" value="" url="{{ getLangUrl('invite-dentist') }}">
							<div class="search-wrap">
								<img src="{{ url('img/white-search.png') }}"/>
							</div>
						</div>
					</div>

					<div class="alert alert-success alert-success-d" style="display: none; margin-top: 20px;"></div>
					<div class="alert alert-warning alert-warning-d" style="display: none; margin-top: 20px;"></div>
				{!! Form::close() !!}

				{!! Form::open([
					'method' => 'post', 
					'files'=> true, 
					'class' => 'search-dentist-form add-team-member-form', 
					'url' => getLangUrl('profile/invite-new') 
				]) !!}
					{!! csrf_field() !!}

					<input type="hidden" name="last_user_id" value=""/>
					<input type="hidden" name="last_user_hash" value=""/>

					<p class="info">
						<img src="img/info.png">
						{{ trans('trp.popup.verification-popup.info-add-team') }}
					</p>

					<div class="flex">
						<input type="hidden" name="check-for-same" class="check-for-same">
						<div class="upload-image-wrapper" style="margin: 0px 10px 10px;">
							<label for="add-avatar-member" class="image-label team-label-image">
								<div class="centered-hack">
									<img src="{{ url('img/plus.svg') }}"/>
									<p>
										{{ trans('trp.popup.verification-popup.add-photo') }}
									</p>
								</div>
					    		<div class="loader">
					    			<i></i>
					    		</div>
								<input type="file" name="image" class="add-avatar-member" accept="image/png,image/jpeg,image/jpg" id="add-avatar-member" upload-url="{{ getLangUrl('register/upload') }}"/>
								<input type="hidden" name="avatar" class="avatar"/>
							</label>
							
							<div class="cropper-container add-team-cropper"></div>
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
						<div class="col">
							<div class="modern-field">
								<input type="text" class="modern-input team-member-name" id="team-member-name" name="name"/>
								<label for="team-member-name">
									<span>{{ trans('trp.popup.verification-popup.add-team-name') }}</span>
								</label>
							</div>
						</div>
						<div class="col">
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
						<div class="col mail-col" style="display: none;">
							<div class="modern-field">
								<input type="email" class="modern-input team-member-email" id="team-member-email" name="email" placeholder="{{ trans('trp.common.optional') }}"/>
								<label for="team-member-email">
									<span>{{ trans('trp.popup.verification-popup.add-team-email') }}</span>
								</label>
							</div>
						</div>
					</div>

					<div class="alert member-alert" style="display: none; margin-top: 20px;"></div>
					<div class="tac">
						<input type="submit" class="button" value="{{ trans('trp.popup.verification-popup.add-team-button') }}">
					</div>
				{!! Form::close() !!}
			</div>

			{!! Form::open([
				'method' => 'post', 
				'class' => 'verification-form', 
				'url' => getLangUrl('verification-dentist') 
			]) !!}
				{!! csrf_field() !!}
				
				<input type="hidden" name="last_user_id" value=""/>
				<input type="hidden" name="last_user_hash" value=""/>

				<div class="modern-field tooltip-text fixed-tooltip" text="{!! nl2br(trans('trp.popup.verification-popup.description.tooltip')) !!}">
					<textarea class="modern-input" id="dentist-description" name="description" maxsymb="512"></textarea>
					<label for="dentist-description">
						<span>{!! nl2br(trans('trp.popup.verification-popup.description')) !!}</span>
					</label>
					<p>{!! nl2br(trans('trp.popup.verification-popup.short_description.hint')) !!}</p>
				</div>

				<div class="alert alert-warning descr-error" style="display: none; margin-top: 20px;">
					{{ trans('trp.popup.verification-popup.description-error') }}
				</div>
				<div class="alert alert-success descr-success" style="display: none; margin-top: 20px;"></div>
				<div class="tac">
					<input class="button big-button" type="submit" value="{!! nl2br(trans('trp.popup.verification-popup.save')) !!}"/>
				</div>

			{!! Form::close() !!}
		</div>
	</div>
</div>