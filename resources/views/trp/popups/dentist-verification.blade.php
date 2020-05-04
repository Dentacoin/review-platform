<div class="popup fixed-popup verification-popup" id="verification-popup">
	<div class="popup-inner inner-white">
		<a href="javascript:;" class="close-popup">
			<i class="fas fa-times"></i>
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

			<a href="javascript:;" class="button wh-btn" style="margin-bottom: 20px;">{{ trans('trp.popup.verification-popup.open-hours') }}</a>

			<div id="clinic-add-team">

				<h4 class="popup-title">
					{{ trans('trp.popup.verification-popup.show-team') }}
				</h4>
				{!! Form::open(array('method' => 'post', 'class' => 'invite-dentist-form', 'url' => getLangUrl('invite-dentist') )) !!}
					{!! csrf_field() !!}

					<input type="hidden" name="last_user_id" value="">
					<input type="hidden" name="last_user_hash" value="">

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
							<i class="search-icon fas fa-search"></i>
						</div>
					</div>

					<div class="alert alert-success alert-success-d" style="display: none; margin-top: 20px;">
					</div>
					<div class="alert alert-warning alert-warning-d" style="display: none; margin-top: 20px;">
					</div>

				{!! Form::close() !!}

				{!! Form::open(array('method' => 'post', 'files'=> true, 'class' => 'search-dentist-form add-team-member-form', 'url' => getLangUrl('profile/invite-new') )) !!}
					{!! csrf_field() !!}

					<input type="hidden" name="last_user_id" value="">
					<input type="hidden" name="last_user_hash" value="">

					<p class="info">
						<img src="img/info.png">
						{{ trans('trp.popup.verification-popup.info-add-team') }}
					</p>

					<div class="flex">
						<input type="hidden" name="check-for-same" class="check-for-same">
						<div style="margin: 0px 10px 10px;">
							<label for="add-avatar-member" class="image-label">
								<div class="centered-hack">
									<i class="fas fa-plus"></i>
									<p>
										{{ trans('trp.popup.verification-popup.add-photo') }}
									</p>
								</div>
					    		<div class="loader">
					    			<i class="fas fa-circle-notch fa-spin"></i>
					    		</div>
								<input type="file" name="image" class="add-avatar-member" id="add-avatar-member" upload-url="{{ getLangUrl('register/upload') }}">
							</label>
							<input type="hidden" class="photo-name-team" name="photo" >
							<input type="hidden" class="photo-thumb-team" name="photo-thumb" >
						</div>
						<div class="col">
							<div class="modern-field">
								<input type="text" class="modern-input team-member-name" id="team-member-name" name="name"></textarea>
								<label for="team-member-name">
									<span>{{ trans('trp.popup.verification-popup.add-team-name') }}</span>
								</label>
							</div>
						</div>
						<div class="col">
							<div class="modern-field alert-after">
					  			<select name="team-job" id="team-member-job" class="modern-input team-member-job">
					  				@foreach(config('trp.team_jobs') as $k => $v)
					  					<option value="{{ $k }}">{{ $v }}</option>
					  				@endforeach
					  			</select>
								<label for="team-member-job">
									<span>{{ trans('trp.popup.verification-popup.add-team-position') }}:</span>
								</label>
							</div>
						</div>
						<div class="col mail-col" style="display: none;">
							<div class="modern-field">
								<input type="email" class="modern-input team-member-email" id="team-member-email" name="email" placeholder="{{ trans('trp.common.optional') }}"></textarea>
								<label for="team-member-email">
									<span>{{ trans('trp.popup.verification-popup.add-team-email') }}</span>
								</label>
							</div>
						</div>
					</div>

					<div class="alert member-alert" style="display: none; margin-top: 20px;">
					</div>
					<div class="tac">
						<input type="submit" class="button" value="{{ trans('trp.popup.verification-popup.add-team-button') }}">
					</div>
				{!! Form::close() !!}
			</div>

			{!! Form::open(array('method' => 'post', 'class' => 'verification-form', 'url' => getLangUrl('verification-dentist') )) !!}
				{!! csrf_field() !!}
				
				<input type="hidden" name="last_user_id" value="">
				<input type="hidden" name="last_user_hash" value="">

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

				<div class="tac">
					<input class="button big-button" type="submit" value="{!! nl2br(trans('trp.popup.verification-popup.save')) !!}">
				</div>

			{!! Form::close() !!}
		</div>
	</div>
</div>

<div class="popup fixed-popup popup-wokring-time" id="popup-wokring-time-waiting" empty-hours>
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
            {!! nl2br(trans('trp.popup.popup-wokring-time.title')) !!}
        </h2>

		{!! Form::open(array('method' => 'post', 'url' => getLangUrl('add-working-hours') )) !!}
			{!! csrf_field() !!}

			<input type="hidden" name="last_user_id" value="">
			<input type="hidden" name="last_user_hash" value="">
			
			@for($day=1;$day<=7;$day++)
				<h4 class="popup-title tac">{{ date('l', strtotime("Sunday +{$day} days")) }}</h4>

                <div class="day-wrapper">
                    <div class="popup-desc" >
                        <label for="day-{{ $day }}"> 
                            {{ Form::checkbox( 'day-'.$day, 1, '', array( 'id' => 'day-'.$day, 'class' => 'work-hour-cb' ) ) }}
                        </label>
                        {{ Form::select( 
                            'work_hours['.$day.'][0][0]', 
                            $hours,
                            '' , 
                            array(
                                'class' => 'input', 
                                'placeholder' => 'HH',
                                'disabled' => 'disabled'
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$day.'][0][1]', 
                            $minutes,
                            '' , 
                            array(
                                'class' => 'input', 
                                'placeholder' => 'MM',
                                'disabled' => 'disabled'
                            ) 
                        ) }}
                        <div class="separator"></div> 
                        {{ Form::select( 
                            'work_hours['.$day.'][1][0]', 
                            $hours,
                            '' , 
                            array(
                                'class' => 'input', 
                                'placeholder' => 'HH',
                                'disabled' => 'disabled'
                            ) 
                        ) }}
                        {{ Form::select( 
                            'work_hours['.$day.'][1][1]', 
                            $minutes,
                            '' , 
                            array(
                                'class' => 'input', 
                                'placeholder' => 'MM',
                                'disabled' => 'disabled'
                            ) 
                        ) }}
                    </div>

                    @if($day == 1)
                        <a href="javascript:;" class="all-days-equal">{!! nl2br(trans('trp.popup.popup-wokring-time.user-same-hours')) !!}</a>
                    @endif
                </div>

			@endfor

			<div class="alert" style="display: none; margin-top: 20px;">
			</div>
			
			
			<input type="hidden" name="json" value="1" />
			<input type="hidden" name="field" value="work_hours" />
			<div class="tac">
				<input type="submit" class="button" value="{!! nl2br(trans('trp.popup.popup-wokring-time.save')) !!}">
			</div>

		{!! Form::close() !!}
	</div>
</div>

<div class="popup fixed-popup popup-existing-dentist" id="popup-existing-dentist">
	<div class="popup-inner inner-white">
		<div class="popup-pc-buttons">
			<a href="javascript:;" class="close-popup"><i class="fas fa-times"></i></a>
		</div>

		<div class="popup-mobile-buttons">
			<a href="javascript:;" class="close-popup">< {!! nl2br(trans('trp.common.back')) !!}</a>
		</div>
		<h2>
            {{ trans('trp.popup.verification-popup.existing-team-title') }}
        </h2>

        <div class="existing-dentists">
			
		</div>

	</div>
</div>